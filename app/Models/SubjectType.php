<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectType extends Model
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
     * Get the activity logs where this is the target.
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'target');
    }
}
