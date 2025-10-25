<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredDocumentType extends Model
{
    use HasFactory, HasSlug, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_type_id',
        'document_type_id',
        'company_id',
        'is_required',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the subject type this requirement belongs to.
     */
    public function subjectType()
    {
        return $this->belongsTo(SubjectType::class);
    }

    /**
     * Get the document type this requirement uses.
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the company this requirement belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
