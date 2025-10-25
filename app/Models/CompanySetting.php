<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'company_id',
        'key',
        'value',
    ];

    public function setValueAttribute($value)
    {
        // Make sure arrays are properly handled before json casting
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Get the company that owns the setting.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Common setting keys
     */
    const TIMEZONE = 'timezone';

    const REMINDER_DEFAULT_DAYS = 'reminder_default_days';

    const NOTIFICATION_EMAIL_ENABLED = 'notification_email_enabled';

    const COMPANY_LOGO = 'company_logo';

    const COMPANY_NAME = 'company_name';

    /**
     * Default values for settings
     */
    const DEFAULTS = [
        self::TIMEZONE => 'UTC',
        self::REMINDER_DEFAULT_DAYS => [30, 14, 7, 1],
        self::NOTIFICATION_EMAIL_ENABLED => true,
    ];

    /**
     * Get the default value for a setting
     */
    public static function getDefaultValue($key)
    {
        return self::DEFAULTS[$key] ?? null;
    }
}
