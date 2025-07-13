<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingFeature extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'key',
        'type',
        'description',
    ];

    /**
     * Get the plans that have this feature
     */
    public function plans()
    {
        return $this->belongsToMany(BillingPlan::class, 'billing_plan_features')
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Determine if a feature value means unlimited
     * 
     * @param mixed $value
     * @return bool
     */
    public static function isUnlimited($value)
    {
        return $value === '-1' || $value === -1;
    }

    /**
     * Format the value for display
     * 
     * @param mixed $value
     * @return string
     */
    public function formatValue($value)
    {
        if (self::isUnlimited($value)) {
            return 'Unlimited';
        }

        if ($this->type === 'boolean') {
            return $value ? 'Yes' : 'No';
        }
        
        return $value;
    }
}
