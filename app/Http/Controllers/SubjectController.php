<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::with('subjectType')
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('subjects/Index', [
            'subjects' => $subjects
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
        // Try to find the subject by ID or slug
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $subject->load('subjectType');
        
        $documents = $subject->documents()->with('documentType')->get();

        return Inertia::render('subjects/Show', [
            'subject' => $subject,
            'documents' => $documents
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Try to find the subject by ID or slug
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
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
        // Try to find the subject by ID or slug
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
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

        return redirect()->route('subjects.show', $subject->id)
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Try to find the subject by ID or slug
        $subject = is_numeric($id) ? Subject::findOrFail($id) : Subject::findBySlugOrFail($id);
        
        // Make sure the subject belongs to the user's company
        if ($subject->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
