<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::with(['subject', 'documentType'])
            ->where('company_id', Auth::user()->company_id);
            
        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('file_name', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'created_at', 'updated_at', 'expiry_date', 'file_name', 'file_size'];
        if (!in_array($sortField, $allowedSortFields)) {
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
            abort(403, 'Unauthorized action.');
        }

        // Store the file
        $path = $request->file('file')->store('documents/' . $subject->id, 'public');
        
        $document = new Document();
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

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Try to find the document by ID or slug
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);
        
        // Make sure the document belongs to the user's company
        if ($document->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $document->load(['subject', 'documentType', 'uploader']);

        return Inertia::render('documents/Show', [
            'document' => $document,
            'fileUrl' => Storage::url($document->file_url),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Try to find the document by ID or slug
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);
        
        // Make sure the document belongs to the user's company
        if ($document->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
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
            'fileUrl' => Storage::url($document->file_url),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Try to find the document by ID or slug
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);
        
        // Make sure the document belongs to the user's company
        if ($document->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
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
            abort(403, 'Unauthorized action.');
        }

        // Handle file update if provided
        if ($request->hasFile('file')) {
            // Delete the old file
            if (Storage::exists($document->file_url)) {
                Storage::delete($document->file_url);
            }

            // Store the new file
            $path = $request->file('file')->store('documents/' . $subject->id, 'public');
            $document->file_url = $path;
        }

        $document->subject_id = $validated['subject_id'];
        $document->document_type_id = $validated['document_type_id'] ?? null;
        $document->issue_date = $validated['issue_date'];
        $document->expiry_date = $validated['expiry_date'] ?? null;
        $document->status = $validated['status'];
        $document->notes = $validated['notes'] ?? null;
        $document->save();

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Try to find the document by ID or slug
        $document = is_numeric($id) ? Document::findOrFail($id) : Document::findBySlugOrFail($id);
        
        // Make sure the document belongs to the user's company
        if ($document->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the file from storage
        if (Storage::exists($document->file_url)) {
            Storage::delete($document->file_url);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
