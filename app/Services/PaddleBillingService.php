<?php

namespace App\Services;

use App\Models\BillingPlan;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Paddle\Subscription;

class PaddleBillingService
{
    /**
     * Convert our internal billing cycle to Paddle's format
     */
    protected function mapBillingCycle(string $cycle): string
    {
        return $cycle === 'yearly' ? 'year' : 'month';
    }

    /**
     * Create a Paddle subscription for a user based on our internal plan
     */
    public function createSubscription(User $user, BillingPlan $plan, string $billingCycle, ?int $trialDays = null): array
    {
        try {
            // Map our billing cycle to Paddle's format
            $paddleBillingCycle = $this->mapBillingCycle($billingCycle);

            // Get the Paddle price ID for this plan and billing cycle
            $priceId = $billingCycle === 'yearly'
                ? $plan->paddle_yearly_price_id
                : $plan->paddle_monthly_price_id;

            if (! $priceId) {
                throw new \Exception("Paddle price ID not found for plan {$plan->name} with billing cycle {$billingCycle}");
            }

            Log::info('Creating Paddle subscription', [
                'user_id' => $user->id,
                'plan' => $plan->name,
                'billing_cycle' => $billingCycle,
                'price_id' => $priceId,
                'trial_days' => $trialDays,
            ]);

            $checkout = $user->subscribe($priceId, 'default')
                ->returnTo(route('paddle.billing.index', ['checkout_completed' => 1]));

            if ($trialDays && $trialDays > 0) {
                Log::info('Trial days will be handled at the Paddle dashboard level', ['trial_days' => $trialDays]);
            }

            if ($checkout) {
                return [
                    'success' => true,
                    'checkout' => $checkout,
                ];
            }

            // For cases where we couldn't get the checkout ID
            throw new \Exception('Failed to retrieve checkout ID from Paddle response');
        } catch (\Exception $e) {
            Log::error('Paddle subscription creation failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a user's Paddle subscription
     */
    public function cancelSubscription(User $user): bool
    {
        try {
            if ($subscription = $user->subscription('default')) {
                $subscription->cancel();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Paddle subscription cancellation failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Resume a canceled Paddle subscription
     */
    public function resumeSubscription(User $user): bool
    {
        try {
            if ($subscription = $user->subscription('default')) {
                if ($subscription->onGracePeriod()) {
                    $subscription->resume();

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Paddle subscription resumption failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Sync our internal plan data with Paddle subscription data
     */
    public function syncSubscriptionData(User $user): ?array
    {
        try {
            $paddleSubscription = $user->subscription('default');
            if (! $paddleSubscription) {
                return null;
            }

            // Map Paddle subscription status to our internal status
            $status = match ($paddleSubscription->status) {
                'active' => 'active',
                'trialing' => 'trial',
                'paused' => 'paused',
                'past_due' => 'past_due',
                'canceled' => 'canceled',
                default => $paddleSubscription->status
            };

            return [
                'paddle_id' => $paddleSubscription->id,
                'status' => $status,
                'current_period_start' => $paddleSubscription->current_period_start,
                'current_period_end' => $paddleSubscription->current_period_end,
                'trial_ends_at' => $paddleSubscription->trial_ends_at,
            ];
        } catch (\Exception $e) {
            Log::error('Paddle subscription sync failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Handle webhook events from Paddle
     */
    public function handleWebhook($payload): bool
    {
        try {
            // This will be handled by Cashier's webhook controller
            return true;
        } catch (\Exception $e) {
            Log::error('Paddle webhook handling failed: '.$e->getMessage());

            return false;
        }
    }
}
