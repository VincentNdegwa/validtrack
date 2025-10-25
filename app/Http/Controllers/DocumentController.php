<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->hasPermission('documents-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = Document::with(['subject', 'documentType'])
            ->where('company_id', Auth::user()->company_id);

        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('notes', 'like', "%{$searchTerm}%")
                    ->orWhere('file_url', 'like', "%{$searchTerm}%");

                $q->orWhereHas('subject', function ($sq) use ($searchTerm) {
                    $sq->where('name', 'like', "%{$searchTerm}%");
                });

                $q->orWhereHas('documentType', function ($sq) use ($searchTerm) {
                    $sq->where('name', 'like', "%{$searchTerm}%");
                });
            });
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSortFields = ['issue_date', 'expiry_date', 'created_at', 'updated_at', 'status'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $documents = $query->paginate($perPage);

        return Inertia::render('documents/Index', [
            'documents' => $documents,
            'filters' => [
                'search' => $request->get('search', ''),
                'sort' => $sortField,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (! Auth::user()->hasPermission('documents-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subjects = Subject::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        $documentTypes = DocumentType::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedSubject = null;
        if ($request->has('subject_id')) {
            $selectedSubject = Subject::findOrFail($request->subject_id);
        }

        return Inertia::render('documents/Create', [
            'subjects' => $subjects,
            'documentTypes' => $documentTypes,
            'selectedSubject' => $selectedSubject,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->hasPermission('documents-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'max_documents');
        if (! $hasAccess) {
            return redirect()->back()->with('error', 'You have reached the maximum number of documents allowed for your plan.');
        }

        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'manual_document_upload');
        if (! $hasAccess) {
            return redirect()->back()->with('error', 'You do not have permission to upload documents manually.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'document_type_id' => 'nullable|exists:document_types,id',
            'file' => 'required|file|max:10240', // Max 10MB
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'status' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        // Make sure the subject belongs to the user's company
        $subject = Subject::findOrFail($validated['subject_id']);
        if ($subject->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Store the file
        $path = $request->file('file')->store('documents/'.$subject->id, 'public');

        $document = new Document;
        $document->subject_id = $validated['subject_id'];
        $document->document_type_id = $validated['document_type_id'] ?? null;
        $document->file_url = $path;
        $document->issue_date = $validated['issue_date'];
        $document->expiry_date = $validated['expiry_date'] ?? null;
        $document->status = $validated['status'];
        $document->uploaded_by = Auth::id();
        $document->notes = $validated['notes'] ?? null;
        $document->company_id = Auth::user()->company_id;
        $document->save();

        return redirect()->route('documents.show', Crypt::encrypt($document->id))
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (! Auth::user()->hasPermission('documents-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);
        if ($document->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $document->load(['subject', 'documentType', 'uploader']);

        return Inertia::render('documents/Show', [
            'document' => $document,
            'fileUrl' => get_file_url($document->file_url),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (! Auth::user()->hasPermission('documents-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);

        // Make sure the document belongs to the user's company
        if ($document->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $subjects = Subject::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        $documentTypes = DocumentType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('documents/Edit', [
            'document' => $document,
            'subjects' => $subjects,
            'documentTypes' => $documentTypes,
            'fileUrl' => get_file_url($document->file_url),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('documents-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);

        // Make sure the document belongs to the user's company
        if ($document->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'document_type_id' => 'nullable|exists:document_types,id',
            'file' => 'nullable|file|max:10240', // Max 10MB
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'status' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        // Make sure the subject belongs to the user's company
        $subject = Subject::findOrFail($validated['subject_id']);
        if ($subject->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        if ($request->hasFile('file')) {
            $exists = check_file_exists($document->file_url);
            if ($exists) {
                $deleteResult = delete_file($document->file_url);
                if (! $deleteResult['success']) {
                    Log::warning('Failed to delete old document: '.$deleteResult['message']);
                }
                $uploadResult = upload_file($request->file('file'), 'documents/'.$subject->id, 'public');
                if ($uploadResult['success']) {
                    $document->file_url = $uploadResult['path'];
                } else {
                    return back()->withErrors(['logo' => $uploadResult['message']]);
                }
            }
        }

        $document->subject_id = $validated['subject_id'];
        $document->document_type_id = $validated['document_type_id'] ?? null;
        $document->issue_date = $validated['issue_date'];
        $document->expiry_date = $validated['expiry_date'] ?? null;
        $document->status = $validated['status'];
        $document->notes = $validated['notes'] ?? null;
        $document->save();

        return redirect()->route('documents.show', Crypt::encrypt($document->id))
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (! Auth::user()->hasPermission('documents-delete')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);

        if ($document->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $exists = check_file_exists($document->file_url);
        if ($exists) {
            $deleteResult = delete_file($document->file_url);
            if (! $deleteResult['success']) {
                Log::warning('Failed to delete old document: '.$deleteResult['message']);
            }
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function download($id)
    {
        if (! Auth::user()->hasPermission('documents-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);
        if ($document->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $exists = check_file_exists($document->file_url);
        if (! $exists) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_url);
    }
}
