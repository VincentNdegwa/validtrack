<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Services\PaddleBillingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaddleBillingController extends Controller
{
    use AuthorizesRequests;
    
    protected $paddleService;
    
    public function __construct(PaddleBillingService $paddleService)
    {
        $this->paddleService = $paddleService;
    }
    
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
        
        // Get the user's active subscription from Paddle
        $currentPlan = null;
        $subscription = $user->subscription('default');
        
        if ($subscription) {
            // Find the corresponding billing plan
            $billingPlan = BillingPlan::with('features')->where('paddle_product_id', $subscription->paddle_id)->first();
            
            if ($billingPlan) {
                // Format features for frontend
                $features = $billingPlan->features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'name' => $feature->name,
                        'description' => $feature->description,
                        'value' => $feature->value,
                        'is_highlighted' => (bool)$feature->is_highlighted,
                        'type' => $feature->type,
                    ];
                });
                
                // Format the billing plan for frontend
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
                
                // Map the billing cycle from Paddle
                $billingCycle = $subscription->recurring ? 
                    ($subscription->interval === 'year' ? 'yearly' : 'monthly') : 
                    'monthly';
                
                $currentPlan = [
                    'id' => $subscription->id,
                    'billing_plan' => $formattedBillingPlan,
                    'billing_cycle' => $billingCycle,
                    'current_period_start' => $subscription->current_period_start->toDateTimeString(),
                    'current_period_end' => $subscription->current_period_end->toDateTimeString(),
                    'status' => $subscription->status,
                ];
                
                // Add trial_ends_at if applicable
                if ($subscription->onTrial()) {
                    $currentPlan['trial_ends_at'] = $subscription->trial_ends_at->toDateTimeString();
                }
            }
        }
            
        return Inertia::render('users/UserBilling', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
        ]);
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
        $result = $this->paddleService->createSubscription(
            $user, 
            $plan, 
            $validated['billing_cycle'], 
            $validated['trial_days'] ?? null
        );
        
        if (!$result['success']) {
            return redirect()->back()->withErrors(['subscription' => 'Failed to create subscription: ' . ($result['error'] ?? 'Unknown error')]);
        }
        
        // Redirect to Paddle checkout page
        if (isset($result['checkout_url'])) {
            return redirect($result['checkout_url']);
        }
        
        // Fallback - should not typically happen
        return redirect()->route('billing.index')->with('success', 'Subscription process started.');
    }
    
    /**
     * Cancel the user's current subscription.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');
        
        if (!$subscription) {
            return redirect()->route('billing.index')
                ->with('error', 'No active subscription found.');
        }
        
        // Cancel at period end
        $subscription->cancelAtPeriodEnd();
        
        return redirect()->route('billing.index')
            ->with('success', 'Your subscription has been canceled. You will have access until the end of your billing period.');
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');
        
        if (!$subscription || !$subscription->onGracePeriod()) {
            return redirect()->route('billing.index')
                ->with('error', 'No canceled subscription found or subscription cannot be resumed.');
        }
        
        // Resume the subscription
        $subscription->resume();
        
        return redirect()->route('billing.index')
            ->with('success', 'Your subscription has been resumed.');
    }
    
    /**
     * Handle webhook from Paddle
     */
    public function handleWebhook(Request $request)
    {
        // This is handled by Laravel Cashier automatically
        return response()->json(['success' => true]);
    }
}
