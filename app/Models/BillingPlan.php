<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BillingPlan extends Model
{
    use HasFactory, HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'monthly_price',
        'yearly_price',
        'is_active',
        'is_featured',
        'sort_order',
        'paddle_product_id',
        'paddle_monthly_price_id',
        'paddle_yearly_price_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the features associated with this plan.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(BillingFeature::class, 'billing_plan_features')
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Get the users that have subscribed to this plan.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_billing_plans')
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
}
