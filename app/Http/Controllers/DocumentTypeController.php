<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DocumentType::where('company_id', Auth::user()->company_id);
        
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSortFields = ['name', 'created_at', 'is_active'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }
        
        $query->withCount('documents')->orderBy($sortField, $sortDirection);
        
        $perPage = $request->get('per_page', 10);
        $documentTypes = $query->paginate($perPage);

        return Inertia::render('documents/types/Index', [
            'documentTypes' => $documentTypes,
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
    public function create()
    {
        return Inertia::render('documents/types/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
        ]);

        // Check for unique name within company
        $exists = DocumentType::where('company_id', Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A document type with this name already exists in your company.'
            ]);
        }

        $validated['company_id'] = Auth::user()->company_id;
        $validated['is_active'] = true;

        DocumentType::create($validated);

        return redirect()->route('document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Try to find the document type by ID or slug
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);
        
        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $documents = $documentType->documents;

        return Inertia::render('documents/types/Show', [
            'documentType' => $documentType,
            'documents' => $documents
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Try to find the document type by ID or slug
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);
        
        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('documents/types/Edit', [
            'documentType' => $documentType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Try to find the document type by ID or slug
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);
        
        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Check for unique name within company, excluding the current document type
        $exists = DocumentType::where('company_id', Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $documentType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A document type with this name already exists in your company.'
            ]);
        }

        $documentType->update($validated);

        return redirect()->route('document-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Try to find the document type by ID or slug
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);
        
        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if there are any documents using this type
        if ($documentType->documents()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Cannot delete this document type because it is being used by one or more documents.'
            ]);
        }

        $documentType->delete();

        return redirect()->route('document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
