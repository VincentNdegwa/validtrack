<?php

namespace App\Http\Controllers;

use App\Models\SubjectType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SubjectTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjectTypes = SubjectType::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('subjects/types/Index', [
            'subjectTypes' => $subjectTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('subjects/types/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check for unique name within company
        $exists = SubjectType::where('company_id', Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'A subject type with this name already exists in your company.'
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
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);
        
        if ($subjectType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $subjects = $subjectType->subjects;

        return Inertia::render('subjects/types/Show', [
            'subjectType' => $subjectType,
            'subjects' => $subjects
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Try to find the subject type by ID or slug
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);
        
        // Make sure the subject type belongs to the user's company
        if ($subjectType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('subjects/types/Edit', [
            'subjectType' => $subjectType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Try to find the subject type by ID or slug
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);
        
        // Make sure the subject type belongs to the user's company
        if ($subjectType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
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
                'name' => 'A subject type with this name already exists in your company.'
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
        // Try to find the subject type by ID or slug
        $subjectType = is_numeric($id) ? SubjectType::findOrFail($id) : SubjectType::findBySlugOrFail($id);
        
        // Make sure the subject type belongs to the user's company
        if ($subjectType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if there are any subjects using this type
        if ($subjectType->subjects()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Cannot delete this subject type because it is being used by one or more subjects.'
            ]);
        }

        $subjectType->delete();

        return redirect()->route('subject-types.index')
            ->with('success', 'Subject type deleted successfully.');
    }
}
