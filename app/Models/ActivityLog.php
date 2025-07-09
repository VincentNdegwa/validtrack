<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory, HasSlug;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['slug', 'message', 'friendly_target_name', 'friendly_date'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'action_type',
        'target_type',
        'target_id',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company associated with the activity.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the target model of the activity.
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a human-readable message for this activity log.
     *
     * @return string
     */
    public function getMessageAttribute(): string
    {
        $userName = $this->user?->name ?? 'System';
        $action = $this->getReadableAction();
        $targetName = $this->getFriendlyTargetName();

        return "{$userName} {$action} {$targetName}";
    }

    /**
     * Get a user-friendly target name.
     * 
     * @return string
     */
    public function getFriendlyTargetNameAttribute(): string
    {
        return $this->getFriendlyTargetName();
    }

    /**
     * Get a formatted date for the activity.
     * 
     * @return string
     */
    public function getFriendlyDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Convert the action type to a human-readable form.
     *
     * @return string
     */
    protected function getReadableAction(): string
    {
        $actionMap = [
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
        ];

        return $actionMap[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get a human-readable name for the target model.
     *
     * @return string
     */
    protected function getFriendlyTargetName(): string
    {
        $className = class_basename($this->target_type);
        $readableClassName = preg_replace('/(?<!^)([A-Z])/', ' $1', $className);
        // Try to get a name/title field from the payload
        $name = $this->getNameFromPayload();
        
        if ($name) {
            return "{$readableClassName} \"{$name}\"";
        }
        
        return "{$readableClassName} #{$this->target_id}";
    }

    /**
     * Extract a meaningful name from the payload.
     *
     * @return string|null
     */
    protected function getNameFromPayload(): ?string
    {
        $payload = $this->payload;
        
        // For created/deleted actions
        if (isset($payload['attributes'])) {
            foreach (['name', 'title', 'email', 'username', 'subject', 'description'] as $field) {
                if (isset($payload['attributes'][$field])) {
                    return $payload['attributes'][$field];
                }
            }
        }
        
        // For updated actions
        if (isset($payload['new'])) {
            foreach (['name', 'title', 'email', 'username', 'subject', 'description'] as $field) {
                if (isset($payload['new'][$field])) {
                    return $payload['new'][$field];
                }
            }
        }
        
        // If we couldn't find a name in the payload
        return null;
    }
    
    /**
     * Get the changes made during an update.
     *
     * @return array|null
     */
    public function getChanges(): ?array
    {
        if ($this->action_type !== 'updated' || !isset($this->payload['new']) || !isset($this->payload['old'])) {
            return null;
        }
        
        $changes = [];
        foreach ($this->payload['new'] as $key => $newValue) {
            $oldValue = $this->payload['old'][$key] ?? null;
            $changes[$key] = [
                'from' => $oldValue,
                'to' => $newValue
            ];
        }
        
        return $changes;
    }
}
