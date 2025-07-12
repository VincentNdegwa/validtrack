<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectType extends Model
{
    use HasFactory, HasSlug, LogsActivity;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['slug'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
    ];

    /**
     * Get the company that owns the subject type.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the subjects for the subject type.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the required document types for this subject type.
     */
    public function requiredDocumentTypes()
    {
        return $this->hasMany(RequiredDocumentType::class);
    }
    
    /**
     * Get the document types that are required for this subject type.
     */
    public function documentTypes()
    {
        return $this->belongsToMany(DocumentType::class, 'required_document_types')
            ->withPivot(['is_required', 'validity_period', 'notify_on_expiry'])
            ->withTimestamps();
    }

    /**
     * Get the activity logs where this is the target.
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'target');
    }
}
