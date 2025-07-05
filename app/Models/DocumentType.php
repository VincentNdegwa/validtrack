<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

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
