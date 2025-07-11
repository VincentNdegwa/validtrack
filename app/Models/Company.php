<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
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
        'email',
        'phone',
        'address',
        'website',
        'logo',
        'location',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users associated with the company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the subject types associated with the company.
     */
    public function subjectTypes()
    {
        return $this->hasMany(SubjectType::class);
    }

    /**
     * Get the subjects associated with the company.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the document types associated with the company.
     */
    public function documentTypes()
    {
        return $this->hasMany(DocumentType::class);
    }

    /**
     * Get the documents associated with the company.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the activity logs associated with the company.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function settings()
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        
        if (!$setting) {
            return $default ?? CompanySetting::getDefaultValue($key);
        }
        
        return $setting->value;
    }

    public function setSetting($key, $value)
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
