<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DocumentType;
use App\Models\RequiredDocumentType;
use App\Models\SubjectType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequiredDocumentTypeController extends Controller
{
    // public function index(Request $request)
    // {
    //     $company = Company::find($request->user()->company_id);
    //     $subjectTypes = SubjectType::where('company_id', $company->id)->get();
    //     $documentTypes = DocumentType::where('company_id', $company->id)->where('is_active', true)->get();
        
    //     $requiredDocuments = RequiredDocumentType::where('company_id', $company->id)
    //         ->with(['subjectType', 'documentType'])
    //         ->get()
    //         ->groupBy('subject_type_id');
            
    //     return Inertia::render('RequiredDocuments/Index', [
    //         'subjectTypes' => $subjectTypes,
    //         'documentTypes' => $documentTypes,
    //         'requiredDocuments' => $requiredDocuments
    //     ]);
    // }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_type_id' => 'required|exists:subject_types,id',
            'document_type_id' => 'required|exists:document_types,id',
            'is_required' => 'boolean'
        ]);
        
        $validated['company_id'] = $request->user()->company_id;
        
        RequiredDocumentType::updateOrCreate(
            [
                'subject_type_id' => $validated['subject_type_id'],
                'document_type_id' => $validated['document_type_id'],
                'company_id' => $validated['company_id']
            ],
            $validated
        );
        
        return back()->with('success', 'Document requirement saved successfully');
    }
    
    public function destroy(Request $request, $requiredDocumentType_id)
    {
        $requiredDocumentType = RequiredDocumentType::findOrFail($requiredDocumentType_id);
        if(!$requiredDocumentType) {
            return back()->with('error','Document requirement not found');
        };
        if ($requiredDocumentType->company_id !== $request->user()->company_id) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        $requiredDocumentType->delete();
        
        return back()->with('success', 'Document requirement removed successfully');
    }
}
