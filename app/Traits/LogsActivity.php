<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    protected static function bootLogsActivity()
    {
        // Log activity when a model is created
        static::created(function (Model $model) {
            static::logActivity($model, 'created');
        });

        // Log activity when a model is updated
        static::updated(function (Model $model) {
            if ($model->isDirty()) {
                static::logActivity($model, 'updated');
            }
        });

        // Log activity when a model is deleted
        static::deleted(function (Model $model) {
            static::logActivity($model, 'deleted');
        });
    }

    /**
     * Log activity for the model.
     *
     * @return void
     */
    protected static function logActivity(Model $model, string $action)
    {
        // Skip logging if we're in a non-user context (like seeding or testing)
        if (! Auth::check()) {
            return;
        }

        // Get the authenticated user and their company
        $user = Auth::user();
        $company_id = $user->company_id;

        // activity_logging
        $hasAccess = check_if_company_has_feature($company_id, 'activity_logging');
        if (! $hasAccess) {
            return;
        }
        // Create the payload based on the action
        $payload = static::createPayload($model, $action);

        // Create the activity log entry
        ActivityLog::create([
            'user_id' => $user->id,
            'company_id' => $company_id,
            'action_type' => $action,
            'target_type' => get_class($model),
            'target_id' => $model->id,
            'payload' => $payload,
        ]);
    }

    /**
     * Create the payload for the activity log.
     */
    protected static function createPayload(Model $model, string $action): array
    {
        $payload = [];

        // For 'created', include all attributes
        if ($action === 'created') {
            $payload['attributes'] = $model->getAttributes();
            // Remove any sensitive data
            static::removeSensitiveData($payload['attributes']);
        }

        // For 'updated', include changed attributes
        elseif ($action === 'updated') {
            $payload['old'] = collect($model->getOriginal())->intersectByKeys($model->getDirty())->toArray();
            $payload['new'] = $model->getDirty();
            // Remove any sensitive data
            static::removeSensitiveData($payload['old']);
            static::removeSensitiveData($payload['new']);
        }

        // For 'deleted', include some identifying info
        elseif ($action === 'deleted') {
            // Get key attributes to identify what was deleted
            $identifiers = ['id' => $model->id];

            // Also include name/title/etc. if available
            foreach (['name', 'title', 'email', 'username'] as $field) {
                if (isset($model->{$field})) {
                    $identifiers[$field] = $model->{$field};
                    break;
                }
            }

            $payload['attributes'] = $identifiers;
        }

        return $payload;
    }

    /**
     * Remove sensitive data from the payload.
     */
    protected static function removeSensitiveData(array &$data): void
    {
        // Define sensitive fields that should be redacted
        $sensitiveFields = [
            'password', 'password_hash', 'token', 'remember_token',
            'api_token', 'secret', 'verification_code',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
    }

    /**
     * Define which attributes should be included in the activity log.
     * Override this method in your model to customize.
     */
    public function getActivityLogAttributes(): array
    {
        // By default, include all fillable attributes
        return $this->fillable;
    }
}
