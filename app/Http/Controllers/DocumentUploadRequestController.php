<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentUploadRequest;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class DocumentUploadRequestController extends Controller
{
    use AuthorizesRequests;
  
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'document_types' => 'required|array|min:1',
            'document_types.*.id' => 'required|exists:document_types,id',
            'document_types.*.required' => 'required|boolean',
            'email' => 'required|email',
            'expiry_hours' => 'nullable|integer|min:1|max:168', 
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        
        $uploadRequest = DocumentUploadRequest::create([
            'subject_id' => $subject->id,
            'token' => DocumentUploadRequest::generateToken(),
            'verification_code' => DocumentUploadRequest::generateVerificationCode(),
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

        Mail::to($validated['email'])->send(new \App\Mail\DocumentUploadRequestMail($uploadRequest));

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
        $request->validate([
            'verification_code' => 'required|string|size:6',
            'upload_request_item_id' => 'required|exists:document_upload_request_items,id',
            'file' => 'required|file|max:10240', // 10MB max
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $uploadRequest = DocumentUploadRequest::where('token', $token)
            ->where('verification_code', $request->verification_code)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();
            
        $requestItem = $uploadRequest->items()
            ->where('id', $request->upload_request_item_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $document = new Document();
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
            if($file_request['success']){
                $path = $file_request['url'];
                $document->file_url = $path;
            } else {
                return back()->withErrors(['file' => 'File upload failed. Please try again.']);
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
            return Inertia::render('documents/PublicUploadSuccess', [
                'allCompleted' => true
            ]);
        }
        
        return back()->with('success', 'Document uploaded successfully. Please upload the remaining required documents.');
    }
}
