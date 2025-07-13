<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Paddle\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasSlug, HasRolesAndPermissions, LogsActivity, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
        'phone',
        'address',
        'location',
        'logo',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['slug'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function routeNotificationForSlack()
    {
        return config('notification.SLACK_BOT_USER_DEFAULT_CHANNEL');
    }

    /**
     * Get the company that owns the user.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the documents that were uploaded by the user.
     */
    public function uploadedDocuments()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Get the subjects created by this user.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the activity logs associated with the user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
    
    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->roles) {
            return false;
        }
        
        // Check each role's permissions
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $perm) {
                if ($perm->name === $permission) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get all permissions for the user through their roles.
     * 
     * @return array
     */
    public function getAllPermissions(): array
    {
        $this->load('roles.permissions');
        
        if (!$this->roles) {
            return [];
        }
        
        $permissions = [];
        
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission;
            }
        }
        
        return array_unique($permissions);
    }
    
    /**
     * Get the billing plans that belong to the user.
     */
    public function billingPlans()
    {
        return $this->belongsToMany(BillingPlan::class, 'user_billing_plans')
            ->withPivot([
                'id',
                'billing_cycle',
                'current_period_start',
                'current_period_end',
                'trial_ends_at',
                'is_active',
                'status',
                'created_at',
                'updated_at'
            ])
            ->withTimestamps();
    }

    /**
     * Get the user's active billing plan.
     */
    public function activeBillingPlan()
    {
        return $this->belongsToMany(BillingPlan::class, 'user_billing_plans')
            ->wherePivot('is_active', true)
            ->withPivot([
                'id',
                'billing_cycle',
                'current_period_start',
                'current_period_end',
                'trial_ends_at',
                'is_active',
                'status',
                'created_at',
                'updated_at'
            ])
            ->withTimestamps()
            ->first();
    }
}
