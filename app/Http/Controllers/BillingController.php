<?php

namespace App\Http\Controllers;

use App\Models\BillingFeature;
use App\Models\BillingPlan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BillingController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display the user's billing page with available plans.
     */
    public function index()
    {
        // Get all active plans with their features
        $plans = BillingPlan::with('features')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        $user = Auth::user();

        $paddleSubscription = $user->subscriptions()->where('status', 'active')->first();
        $currentPlan = null;
        if ($paddleSubscription) {
            $subItem = $paddleSubscription->items()->first();
            $billingPlan = BillingPlan::with('features')->where('paddle_product_id', $subItem->product_id)->first();

            if ($billingPlan) {
                $features = $billingPlan->features;
                $formattedBillingPlan = [
                    'id' => $billingPlan->id,
                    'name' => $billingPlan->name,
                    'description' => $billingPlan->description,
                    'monthly_price' => $billingPlan->monthly_price,
                    'yearly_price' => $billingPlan->yearly_price,
                    'is_active' => (bool)$billingPlan->is_active,
                    'is_featured' => (bool)$billingPlan->is_featured,
                    'sort_order' => $billingPlan->sort_order,
                    'features' => $features,
                ];
                $billingCycle = $subItem->price_id ===  $billingPlan->paddle_monthly_price_id
                    ? 'monthly'
                    : 'yearly';

                $currentPlan = [
                    'id' => $paddleSubscription->id,
                    'billing_plan' => $formattedBillingPlan,
                    'billing_cycle' => $billingCycle,
                    'current_period_start' => $paddleSubscription->current_period_start ?? null,
                    'current_period_end' => $paddleSubscription->current_period_end ?? null,
                    'status' => $paddleSubscription->status,
                ];

                if ($paddleSubscription->trial_ends_at) {
                    $currentPlan['trial_ends_at'] = $paddleSubscription->trial_ends_at;
                }
            }
        }
            
        return Inertia::render('users/UserBilling', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
        ]);
    }
    
    /**
     * Display the billing plans management page for admins.
     */
    public function indexPlans()
    {
        
        $plans = BillingPlan::with('features')
            ->orderBy('sort_order')
            ->get();
            
        return Inertia::render('Billing/plans/Index', [
            'plans' => $plans,
        ]);
    }
    
    /**
     * Show form to create a new billing plan.
     */
    public function createPlan()
    {
        
        $features = BillingFeature::all();
        
        return Inertia::render('Billing/plans/Create', [
            'features' => $features,
        ]);
    }
    
    /**
     * Show form to edit a billing plan.
     */
    public function editPlan(BillingPlan $plan)
    {
        
        $plan->load('features');
        $features = BillingFeature::all();
        
        return Inertia::render('Billing/plans/Edit', [
            'plan' => $plan,
            'features' => $features,
        ]);
    }
    
    /**
     * Display the billing features management page for admins.
     */
    public function indexFeatures()
    {
        
        $features = BillingFeature::all();
        
        return Inertia::render('Billing/features/Index', [
            'features' => $features,
        ]);
    }
    
    /**
     * Show form to create a new billing feature.
     */
    public function createFeature()
    {
        
        return Inertia::render('Billing/features/Create');
    }
    
    /**
     * Show form to edit a billing feature.
     */
    public function editFeature(BillingFeature $feature)
    {
        
        return Inertia::render('Billing/features/Edit', [
            'feature' => $feature,
        ]);
    }
    
    /**
     * Store a newly created billing plan.
     */
    public function storePlan(Request $request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'paddle_product_id' => 'nullable|string',
            'paddle_monthly_price_id' => 'nullable|string',
            'paddle_yearly_price_id' => 'nullable|string',
            'features' => 'array',
        ]);
        
        // Create the plan
        $plan = BillingPlan::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'monthly_price' => $validated['monthly_price'],
            'yearly_price' => $validated['yearly_price'],
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'paddle_product_id' => $validated['paddle_product_id'] ?? null,
            'paddle_monthly_price_id' => $validated['paddle_monthly_price_id'] ?? null,
            'paddle_yearly_price_id' => $validated['paddle_yearly_price_id'] ?? null,
            'sort_order' => BillingPlan::count() + 1,
        ]);
        
        if (isset($validated['features'])) {
            foreach ($validated['features'] as $key=>$value) {
                if(isset($value)){
                    $plan->features()->attach($key, [
                        'value' => $value,
                    ]);
                }
            }
        }
        
        return redirect()->route('billing.plans.index')
            ->with('success', 'Billing plan created successfully.');
    }
    
    /**
     * Update the specified billing plan.
     */
    public function updatePlan(Request $request, BillingPlan $plan)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'paddle_product_id' => 'nullable|string',
            'paddle_monthly_price_id' => 'nullable|string',
            'paddle_yearly_price_id' => 'nullable|string',
            'features' => 'array',
        ]);
        
        // Update the plan
        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'monthly_price' => $validated['monthly_price'],
            'yearly_price' => $validated['yearly_price'],
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'sort_order' => $validated['sort_order'] ?? $plan->sort_order,
            'paddle_product_id' => $validated['paddle_product_id'] ?? null,
            'paddle_monthly_price_id' => $validated['paddle_monthly_price_id'] ?? null,
            'paddle_yearly_price_id' => $validated['paddle_yearly_price_id'] ?? null,
        ]);
        
        if (isset($validated['features'])) {
            $features = [];
            foreach ($validated['features'] as $featureId => $value) {
                if ($value !== null) {
                    $features[$featureId] = ['value' => $value];
                }
            }
            
            $plan->features()->sync($features);
        }
        
        return redirect()->route('billing.plans.index')
            ->with('success', 'Billing plan updated successfully.');
    }
    
    /**
     * Remove the specified billing plan.
     */
    public function destroyPlan(BillingPlan $plan)
    {
        
        // Check if plan is in use
        if ($plan->users()->count() > 0) {
            return redirect()->route('billing.plans.index')
                ->with('error', 'Cannot delete a billing plan that is in use.');
        }
        
        $plan->delete();
        
        return redirect()->route('billing.plans.index')
            ->with('success', 'Billing plan deleted successfully.');
    }
    
    /**
     * Store a newly created billing feature.
     */
    public function storeFeature(Request $request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:billing_features,key',
            'type' => 'required|in:boolean,number',
            'description' => 'nullable|string',
        ]);
        
        BillingFeature::create($validated);
        
        return redirect()->route('billing.features.index')
            ->with('success', 'Billing feature created successfully.');
    }
    
    /**
     * Update the specified billing feature.
     */
    public function updateFeature(Request $request, BillingFeature $feature)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:billing_features,key,'.$feature->id,
            'type' => 'required|in:boolean,number',
            'description' => 'nullable|string',
        ]);
        
        $feature->update($validated);
        
        return redirect()->route('billing.features.index')
            ->with('success', 'Billing feature updated successfully.');
    }
    
    /**
     * Remove the specified billing feature.
     */
    public function destroyFeature(BillingFeature $feature)
    {
        
        // Check if feature is in use
        if ($feature->plans()->count() > 0) {
            return redirect()->route('billing.features.index')
                ->with('error', 'Cannot delete a billing feature that is in use.');
        }
        
        $feature->delete();
        
        return redirect()->route('billing.features.index')
            ->with('success', 'Billing feature deleted successfully.');
    }
    
    /**
     * Subscribe a user to a billing plan.
     */
    public function subscribe(Request $request, BillingPlan $plan)
    {
        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'trial_days' => 'nullable|integer|min:0',
        ]);
        
        $user = $request->user();
        $now = now();
        
        // Calculate period end date
        $periodEnd = $validated['billing_cycle'] === 'yearly' 
            ? $now->copy()->addYear() 
            : $now->copy()->addMonth();
            
        // Calculate trial end date if trial days is provided
        $trialEndsAt = null;
        if (!empty($validated['trial_days']) && $validated['trial_days'] > 0) {
            $trialEndsAt = $now->copy()->addDays($validated['trial_days']);
        }
        
        // Deactivate any existing active plans
        $user->billingPlans()
            ->wherePivot('is_active', true)
            ->update(['is_active' => false]);
        
        // Subscribe to the new plan
        $user->billingPlans()->attach($plan->id, [
            'billing_cycle' => $validated['billing_cycle'],
            'current_period_start' => $now,
            'current_period_end' => $periodEnd,
            'trial_ends_at' => $trialEndsAt,
            'is_active' => true,
            'status' => $trialEndsAt ? 'trial' : 'active',
        ]);
        
        return redirect()->route('billing.index')
            ->with('success', "You've successfully subscribed to the {$plan->name} plan.");
    }
    
    /**
     * Cancel the user's current subscription.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        
        // Find the active subscription
        $subscription = $user->billingPlans()
            ->wherePivot('is_active', true)
            ->first();
        
        if (!$subscription) {
            return redirect()->route('billing.index')
                ->with('error', 'No active subscription found.');
        }
        
        // Mark as canceled but keep active until the end of the period
        $user->billingPlans()
            ->wherePivot('is_active', true)
            ->update([
                'status' => 'canceled',
            ]);
        
        return redirect()->route('billing.index')
            ->with('success', 'Your subscription has been canceled. You will have access until the end of your billing period.');
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(Request $request)
    {
        $user = $request->user();
        
        // Find the canceled subscription
        $subscription = $user->billingPlans()
            ->wherePivot('status', 'canceled')
            ->wherePivot('is_active', true)
            ->first();
        
        if (!$subscription) {
            return redirect()->route('billing.index')
                ->with('error', 'No canceled subscription found.');
        }
        
        // Resume the subscription
        $user->billingPlans()
            ->wherePivot('id', $subscription->pivot->id)
            ->update([
                'status' => 'active',
            ]);
        
        return redirect()->route('billing.index')
            ->with('success', 'Your subscription has been resumed.');
    }
}
