<?php

namespace App\Services;

use App\Models\BillingFeature;
use App\Models\BillingPlan;
use App\Models\Company;

class BillingService
{
    /**
     * Check if a company has a specific feature
     * 
     * @param Company $company
     * @param string $featureKey
     * @return bool
     */
    public function companyHasFeature(Company $company, string $featureKey): bool
    {
        $value = $this->getCompanyFeatureValue($company, $featureKey);
        
        // If feature is boolean, any positive value means they have it
        $feature = BillingFeature::where('key', $featureKey)->first();
        if (!$feature) {
            return false;
        }

        if ($feature->type === 'boolean') {
            return (bool) $value;
        }

        // For numeric features, -1 means unlimited, any positive value means they have it
        return $value === -1 || $value > 0;
    }

    /**
     * Get the value of a feature for a company
     * 
     * @param Company $company
     * @param string $featureKey
     * @return mixed
     */
    public function getCompanyFeatureValue(Company $company, string $featureKey): mixed
    {
        $feature = BillingFeature::where('key', $featureKey)->first();
        if (!$feature) {
            return null;
        }

        $plan = $this->getCompanyCurrentPlan($company);
        if (!$plan) {
            return null;
        }

        $planFeature = $plan->features()->where('billing_feature_id', $feature->id)->first();
        if (!$planFeature) {
            return null;
        }

        return $planFeature->pivot->value;
    }

    /**
     * Get the current billing plan for a company
     * 
     * @param Company $company
     * @return BillingPlan|null
     */
    public function getCompanyCurrentPlan(Company $company): ?BillingPlan
    {
        $companyPlan = $company->currentBillingPlan;
        if (!$companyPlan) {
            // Return basic plan as default
            return BillingPlan::where('slug', 'basic')->first();
        }

        return $companyPlan->plan;
    }
}
