<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DocumentTypeController extends Controller
{
    /**
     * Show the bulk import form
     */
    public function showBulkImport()
    {
        if (!Auth::user()->hasPermission('document-types-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        return Inertia::render('documents/types/BulkImport');
    }

    /**
     * Process the bulk import
     */
    public function bulkImport(Request $request)
    {
        if (!Auth::user()->hasPermission('document-types-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
            'records' => 'required|array',
            'records.*.name' => 'required|string|max:255',
            'records.*.description' => 'nullable|string|max:1000',
            'records.*.icon' => 'nullable|string|max:255',
            'records.*.is_active' => 'required|boolean',
        ]);
        
        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'max_document_types');
        if (!$hasAccess) {
            return redirect()->back()->with('error', 'You have reached the maximum number of document types allowed for your plan.');
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $maxRecords = 100; // Maximum records per import
        $records = array_slice($request->records, 0, $maxRecords);

        if (count($request->records) > $maxRecords) {
            $results['errors'][] = "Maximum import limit of {$maxRecords} records reached. Only processing first {$maxRecords} records.";
        }

        foreach ($records as $index => $record) {
            try {

                $exists = DocumentType::where('company_id', Auth::user()->company_id)
                    ->where('name', $record['name'])
                    ->exists();

                if ($exists) {
                    $results['failed']++;
                    $results['errors'][] = "Record " . ($index + 1) . ": A document type with this name already exists.";
                    continue;
                }

                DocumentType::create([
                    'name' => $record['name'],
                    'description' => $record['description'] ?? null,
                    'icon' => $record['icon'] ?? null,
                    'is_active' => $record['is_active'],
                    'company_id' => Auth::user()->company_id,
                ]);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Record " . ($index + 1) . ": Failed to create document type - {$e->getMessage()}";
            }
        }

        return redirect()->back()->with([
            'import_results' => $results,
            'success' => 'Import process completed.',
        ]);
    }

    public function index(Request $request)
    {
        if (! Auth::user()->hasPermission('document-types-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = DocumentType::where('company_id', Auth::user()->company_id);

        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortFields = ['name', 'created_at', 'is_active'];
        if (! in_array($sortField, $allowedSortFields)) {
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
        if (! Auth::user()->hasPermission('document-types-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        return Inertia::render('documents/types/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->hasPermission('document-types-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'max_document_types');
        if (! $hasAccess) {
            return redirect()->back()->with('error', 'You have reached the maximum number of document types allowed for your plan.');
        }
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
                'name' => 'A document type with this name already exists in your company.',
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
        if (! Auth::user()->hasPermission('document-types-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);

        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $documents = $documentType->documents;

        return Inertia::render('documents/types/Show', [
            'documentType' => $documentType,
            'documents' => $documents,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (! Auth::user()->hasPermission('document-types-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);

        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        return Inertia::render('documents/types/Edit', [
            'documentType' => $documentType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('document-types-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);

        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
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
                'name' => 'A document type with this name already exists in your company.',
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
        if (! Auth::user()->hasPermission('document-types-delete')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $documentType = is_numeric($id) ? DocumentType::findOrFail($id) : DocumentType::findBySlugOrFail($id);

        // Make sure the document type belongs to the user's company
        if ($documentType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Check if there are any documents using this type
        if ($documentType->documents()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Cannot delete this document type because it is being used by one or more documents.',
            ]);
        }

        $documentType->delete();

        return redirect()->route('document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
