<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentUploadRequest;
use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DocumentUploadRequestController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        // document_upload_requests
        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'document_upload_requests');
        if (! $hasAccess) {
            return redirect()->back()->with('error', 'Your plan does not allow creating document upload requests.');
        }
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'document_types' => 'required|array|min:1',
            'document_types.*.id' => 'required|exists:document_types,id',
            'document_types.*.required' => 'required|boolean',
            'email' => 'required|email',
            'expiry_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        $hasVerification = check_if_company_has_feature(Auth::user()->company_id, 'verification_codes');
        if (! $hasVerification) {
            $validated['verification_code'] = '0000';
        } else {
            $validated['verification_code'] = DocumentUploadRequest::generateVerificationCode();
        }
        $uploadRequest = DocumentUploadRequest::create([
            'subject_id' => $subject->id,
            'token' => DocumentUploadRequest::generateToken(),
            'verification_code' => $validated['verification_code'], // DocumentUploadRequest::generateVerificationCode(),
            'email' => $validated['email'],
            'expires_at' => now()->addHours($validated['expiry_hours'] ?? 24),
            'status' => 'pending',
        ]);

        foreach ($validated['document_types'] as $documentTypeData) {
            $documentType = DocumentType::findOrFail($documentTypeData['id']);

            $uploadRequest->items()->create([
                'document_type_id' => $documentType->id,
                'is_required' => $documentTypeData['required'],
                'status' => 'pending',
            ]);
        }

        Mail::to($validated['email'])->queue(new \App\Mail\DocumentUploadRequestMail($uploadRequest));

        return back()->with('success', 'Document upload request has been sent successfully.');
    }

    /**
     * Cancel an upload request
     */
    public function cancel(DocumentUploadRequest $uploadRequest)
    {
        $this->authorize('cancel', $uploadRequest);

        $uploadRequest->cancel();

        return back()->with('success', 'Upload request has been cancelled.');
    }

    /**
     * Show public upload form for a token
     */
    public function showUploadForm(string $token)
    {
        $uploadRequest = DocumentUploadRequest::with(['subject', 'items.documentType'])
            ->where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $documentTypes = $uploadRequest->items->map(function ($item) {
            return [
                'id' => $item->id,
                'document_type_id' => $item->document_type_id,
                'name' => $item->documentType->name,
                'required' => $item->is_required,
                'status' => $item->status,
            ];
        });

        return Inertia::render('documents/PublicUpload', [
            'uploadRequest' => [
                'token' => $uploadRequest->token,
                'subject' => $uploadRequest->subject->only(['id', 'name']),
                'documentTypes' => $documentTypes,
                'expiresAt' => $uploadRequest->expires_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function processUpload(Request $request, string $token)
    {
        try {
            $hasVerification = true; // check_if_company_has_feature(Auth::user()->company_id, 'verification_codes');
            $request->validate([
                'verification_code' => ($hasVerification ? 'required' : 'nullable').'|string|size:6',
                'upload_request_item_id' => 'required|exists:document_upload_request_items,id',
                'file' => 'required|file|max:10240', // 10MB max
                'issue_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:issue_date',
                'notes' => 'nullable|string|max:1000',
            ]);

            $tokenExists = DocumentUploadRequest::where('token', $token)->exists();
            if (! $tokenExists) {
                return back()->with('error', 'Invalid upload link. The link may have expired or been removed.');
            }
            $codeCorrect = DocumentUploadRequest::where('token', $token)
                ->where('verification_code', $request->verification_code)
                ->exists();

            if (! $codeCorrect) {
                return back()->with('error', 'Incorrect verification code. Please check and try again.');
            }

            $uploadRequest = DocumentUploadRequest::where('token', $token)
                ->where('verification_code', $request->verification_code)
                ->where('status', 'pending')
                ->first();

            if (! $uploadRequest) {
                return back()->with('error', 'This upload request has already been used or cancelled.');
            }

            if ($uploadRequest->expires_at < now()) {
                return back()->with('error', 'This upload link has expired. Please request a new upload link.');
            }

            $requestItem = $uploadRequest->items()
                ->where('id', $request->upload_request_item_id)
                ->where('status', 'pending')
                ->first();

            if (! $requestItem) {
                return back()->with('error', 'The selected document has already been uploaded or is no longer available.');
            }

            DB::beginTransaction();

            try {
                $document = new Document;
                $document->subject_id = $uploadRequest->subject_id;
                $document->document_type_id = $requestItem->document_type_id;
                $document->upload_request_item_id = $requestItem->id;
                $document->issue_date = $request->issue_date;
                $document->expiry_date = $request->expiry_date;
                $document->status = 1;
                $document->notes = $request->notes;
                $document->company_id = $uploadRequest->subject->company_id;

                if ($request->hasFile('file')) {
                    $file_request = upload_file($request->file('file'), 'documents');
                    if ($file_request['success']) {
                        $path = $file_request['path'];
                        $document->file_url = $path;
                    } else {
                        DB::rollBack();

                        return back()->with('error', 'File upload failed. Please try again.');
                    }
                }

                $document->save();
                $requestItem->markAsCompleted();

                $pendingRequiredItems = $uploadRequest->items()
                    ->where('is_required', true)
                    ->where('status', 'pending')
                    ->count();

                if ($pendingRequiredItems === 0) {
                    $uploadRequest->markAsUsed();
                    DB::commit();

                    return Inertia::render('documents/PublicUploadSuccess', [
                        'allCompleted' => true,
                    ]);
                }

                DB::commit();

                return back()->with('success', 'Document uploaded successfully. Please upload the remaining required documents.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Document upload failed: '.$e->getMessage());

                return back()->with('error', 'An error occurred while processing your upload. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Document upload request error: '.$e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }
}
