<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
        return $this->hasMany(CompanySetting::class, 'company_id');
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        Log::info("Getting setting for key: {$key} in company: {$this->name}", [
            'setting' => $setting,
        ]);
        
        if (!$setting) {
            return $default ?? CompanySetting::getDefaultValue($key);
        }
        
        $value = $setting->value;
        
        if ($key === CompanySetting::REMINDER_DEFAULT_DAYS && is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            if (is_numeric($value)) {
                return [(int)$value];
            }
        }
        
        return $value;
    }

    public function setSetting($key, $value)
    {
        // If the value is an array, convert it to JSON string for storage
        if (is_array($value)) {
            $value = json_encode($value);
        }
        
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
