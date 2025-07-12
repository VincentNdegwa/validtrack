<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\RequiredDocumentType;
use App\Models\Subject;
use App\Models\SubjectType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('subjects-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = Subject::with('subjectType')
            ->where('company_id', Auth::user()->company_id);
        
        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'email', 'phone', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $subjects = $query->with(['documents', 'documents.documentType'])->paginate($perPage);
        
        $subjectTypes = SubjectType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
        $documentTypes = DocumentType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('subjects/Index', [
            'subjects' => $subjects,
            'subjectTypes' => $subjectTypes,
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
        if (!Auth::user()->hasPermission('subjects-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subjectTypes = SubjectType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('subjects/Create', [
            'subjectTypes' => $subjectTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('subjects-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject_type_id' => 'nullable|exists:subject_types,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['user_id'] = Auth::id();

        $subject = Subject::create($validated);

        return redirect()->route('subjects.show', $subject->id)
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!Auth::user()->hasPermission('subjects-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $subject->load(['subjectType', 'documents', 'documents.documentType']);
        
        $documentTypes = DocumentType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
            
        $requiredDocumentTypes = RequiredDocumentType::where('subject_type_id', $subject->subject_type_id)
            ->where('company_id', Auth::user()->company_id)
            ->with('documentType')
            ->get();

        return Inertia::render('subjects/Show', [
            'subject' => $subject,
            'documents' => $subject->documents,
            'documentTypes' => $documentTypes,
            'requiredDocumentTypes' => $requiredDocumentTypes,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!Auth::user()->hasPermission('subjects-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $subjectTypes = SubjectType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('subjects/Edit', [
            'subject' => $subject,
            'subjectTypes' => $subjectTypes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('subjects-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject_type_id' => 'nullable|exists:subject_types,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.show', Crypt::encrypt($subject->id))
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('subjects-delete')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
