<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\SubjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubjectTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->hasPermission('subject-types-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = SubjectType::where('company_id', Auth::user()->company_id);

        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortFields = ['name', 'created_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }

        $query->withCount('subjects')->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $subjectTypes = $query->paginate($perPage);

        return Inertia::render('subjects/types/Index', [
            'subjectTypes' => $subjectTypes,
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
        if (! Auth::user()->hasPermission('subject-types-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        return Inertia::render('subjects/types/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->hasPermission('subject-types-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        // max_subject_types
        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'max_subject_types');
        if (! $hasAccess) {
            return redirect()->back()->with('error', 'You have reached the maximum number of subject types allowed for your plan.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check for unique name within company
        $exists = SubjectType::where('company_id', Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A subject type with this name already exists in your company.',
            ]);
        }

        $validated['company_id'] = Auth::user()->company_id;

        SubjectType::create($validated);

        return redirect()->route('subject-types.index')
            ->with('success', 'Subject type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (! Auth::user()->hasPermission('subject-types-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);

        if ($subjectType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        $user = Auth::user();
        $subjects = $subjectType->subjects;
        $documentTypes = DocumentType::where('company_id', $user->company_id)->where('is_active', 1)->get();
        $requiredDocumentTypes = $subjectType->requiredDocumentTypes()
            ->with('documentType', 'subjectType')
            ->where('company_id', $user->company_id)
            ->get();

        return Inertia::render('subjects/types/Show', [
            'subjectType' => $subjectType,
            'subjects' => $subjects,
            'documentTypes' => $documentTypes,
            'requiredDocumentTypes' => $requiredDocumentTypes,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (! Auth::user()->hasPermission('subject-types-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);

        // Make sure the subject type belongs to the user's company
        if ($subjectType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        return Inertia::render('subjects/types/Edit', [
            'subjectType' => $subjectType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('subject-types-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);

        // Make sure the subject type belongs to the user's company
        if ($subjectType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check for unique name within company, excluding the current subject type
        $exists = SubjectType::where('company_id', Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $subjectType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A subject type with this name already exists in your company.',
            ]);
        }

        $subjectType->update($validated);

        return redirect()->route('subject-types.index')
            ->with('success', 'Subject type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (! Auth::user()->hasPermission('subject-types-delete')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);

        // Make sure the subject type belongs to the user's company
        if ($subjectType->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Check if there are any subjects using this type
        if ($subjectType->subjects()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Cannot delete this subject type because it is being used by one or more subjects.',
            ]);
        }

        $subjectType->delete();

        return redirect()->route('subject-types.index')
            ->with('success', 'Subject type deleted successfully.');
    }
}
