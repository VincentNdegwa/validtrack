<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory, HasSlug, LogsActivity;

    protected $appends = ['slug'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
        'description',
        'icon',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the document type.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the required document type relationships where this document type is required.
     */
    public function requiredFor()
    {
        return $this->hasMany(RequiredDocumentType::class);
    }

    /**
     * Get the subject types that require this document type.
     */
    public function subjectTypes()
    {
        return $this->belongsToMany(SubjectType::class, 'required_document_types')
            ->withPivot(['is_required', 'validity_period', 'notify_on_expiry'])
            ->withTimestamps();
    }

    /**
     * Get the documents for the document type.
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
}
