<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\BillingPlan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Services\PaddleBillingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
                    'current_period_start' => ($paddleSubscription?->lastPayment() && $paddleSubscription?->lastPayment()->date)
                        ? company_datetime($paddleSubscription->lastPayment()->date, $user->company_id)
                        : null,
                    'current_period_end' => ($paddleSubscription?->nextPayment() && $paddleSubscription?->nextPayment()->date)
                        ? company_datetime($paddleSubscription->nextPayment()->date, $user->company_id)
                        : null,
                    'status' => $paddleSubscription->status,
                ];

                if ($paddleSubscription->trial_ends_at) {
                    $currentPlan['trial_ends_at'] = $paddleSubscription->trial_ends_at;
                }
            }
        }

        $transactions = $user->transactions()
            ->orderByDesc('billed_at')
            ->limit(10)
            ->get()
            ->map(function ($txn)use($user) {
                return array_merge(
                    $txn->toArray(),
                    [
                        'billed_at' => company_datetime($txn->billed_at->format('Y-m-d H:i:s'), $user->company_id),
                        'total' => number_format($txn->total / 100, 2),
                    ]
                );
            });


        return Inertia::render('users/UserBilling', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'transactions'=> $transactions,
        ]);
    }
    
    /**
     * Subscribe a user to a billing plan.
     */
    public function subscribe(Request $request)
    {
        $plan_encrypted = $request->route('plan');
        $plan_id = Crypt::decrypt($plan_encrypted);
        $plan = BillingPlan::findOrFail($plan_id);
        if(!isset($plan)){
            return redirect()->back('error', 'Plan not found');
        };
        $billing_cycle = $request->route('billing_cycle');
        $user = $request->user();

        $priceId = $billing_cycle === 'yearly'
            ? $plan->paddle_yearly_price_id
            : $plan->paddle_monthly_price_id;

        if (!$priceId) {
            return redirect()->back()->withErrors([
                'subscription' => 'This plan is not available for ' . $billing_cycle . ' billing in Paddle'
            ]);
        }

        try {
            $result = $this->paddleService->createSubscription(
                $user, 
                $plan, 
                $billing_cycle,
                $validated['trial_days'] ?? null
            );
            
            Log::info('Paddle subscription creation result', $result);
            
            if (!$result['success']) {
                return redirect()->back()->with(
                    'Error creating subscription: ' . ($result['error'] ?? 'Unknown error'));
            }
            if (!empty($result['checkout'])) {
                Log::info('Using checkout object directly with view');
                return view('checkout', [
                    'checkout' => $result['checkout'],
                    'plan' => $plan,
                    'billing_cycle' => $billing_cycle,
                ]);
            }
            return redirect()->route('paddle.billing.index')
                ->with('error', 'Failed to intiate subscription');
        } catch (\Exception $e) {
            Log::error('Error creating Paddle checkout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating subscription: ' . $e->getMessage());
        }
    }


    /**
     * Cancel the user's current subscription.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');
        
        if (!$subscription) {
            return redirect()->route('paddle.billing.index')
                ->with('error', 'No active subscription found.');
        }        
        $subscription->cancel();
        
        return redirect()->route('paddle.billing.index')
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
            return redirect()->route('paddle.billing.index')
                ->with('error', 'No canceled subscription found or subscription cannot be resumed.');
        }
        
        // Resume the subscription
        $subscription->resume();
        
        return redirect()->route('paddle.billing.index')
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
