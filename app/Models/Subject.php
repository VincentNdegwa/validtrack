<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, HasSlug, LogsActivity;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['slug', 'compliance_status', 'missing_documents'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
        'subject_type_id',
        'user_id',
        'email',
        'phone',
        'address',
        'category',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'integer',
        'compliance_status' => 'boolean',
    ];

    /**
     * Get the company that owns the subject.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the subject type that owns the subject.
     */
    public function subjectType()
    {
        return $this->belongsTo(SubjectType::class);
    }

    /**
     * Get the user that created the subject.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the documents for the subject.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the activity logs where this is the target.
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'target');
    }
    
    public function getComplianceStatusAttribute()
    {
        if (!$this->subjectType) {
            return true; 
        }
        
        $requiredDocTypes = RequiredDocumentType::where('subject_type_id', $this->subject_type_id)
            ->where('company_id', $this->company_id)
            ->pluck('document_type_id')
            ->toArray();
        
        if (empty($requiredDocTypes)) {
            return true; 
        }        
        $activeDocuments = $this->documents()
            ->where('status', 1)
            ->whereIn('document_type_id', $requiredDocTypes)
            ->get();
            
        $coveredDocTypes = $activeDocuments->pluck('document_type_id')->unique()->toArray();
        
        // Check if all required document types are covered
        $allDocTypesCovered = count(array_diff($requiredDocTypes, $coveredDocTypes)) === 0;
        
        // Check if any required documents are expired
        $hasExpiredDocuments = $activeDocuments->contains(function ($doc) {
            return $doc->expiry_date && $doc->expiry_date->isPast();
        });
        
        return $allDocTypesCovered && !$hasExpiredDocuments;
    }
    
    public function getMissingDocumentsAttribute()
    {
        if (!$this->subjectType) {
            return [];
        }
        
        // Get all required document types for this subject's type
        $requiredDocTypes = RequiredDocumentType::where('subject_type_id', $this->subject_type_id)
            ->where('company_id', $this->company_id)
            ->with('documentType')
            ->get();
            
        if ($requiredDocTypes->isEmpty()) {
            return [];
        }
        
        $missingDocs = [];
        
        foreach ($requiredDocTypes as $requiredDoc) {
            // Check if there's an active document (status = 1) for this document type
            $activeDoc = $this->documents()
                ->where('document_type_id', $requiredDoc->document_type_id)
                ->where('status', 1)
                ->first();
                
            // If no active document, or if document is expired
            if (!$activeDoc || ($activeDoc->expiry_date && $activeDoc->expiry_date->isPast())) {
                $missingDocs[] = $requiredDoc->documentType;
            }
        }
        
        return $missingDocs;
    }
}
