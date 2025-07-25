<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => function() {
                $user = Auth::user();
                $company_user = $user;
                $company = $company_user->company ?? null;
                $features = $this->getFeatures($company);
                if (!$user) {
                    return [
                        'user' => null,
                    ];
                }
                
                $permissionsCollection = $user->getAllPermissions();
                
                $permissionNames = [];
                foreach ($permissionsCollection as $permission) {
                    $permissionNames[] = $permission->name;
                }
                
                return [
                    'user' => array_merge($user->only('id', 'name', 'email', 'company_id', 'slug'), [
                        'roles' => $user->roles,
                        'permissions' => $permissionNames,
                    ]),
                    'features' => $features,
                ];
            },
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    public function getFeatures($company)
    {
        if ($company) {
            $companyOwner = get_company_owner($company);
            // $actualCompany = \App\Models\Company::find($company);
            if (!$companyOwner) {
                return [];
            }

            if (!$companyOwner || !$companyOwner->subscribed() || !$companyOwner->subscribed('default')) {
                return [];
            }

            $paddleSubscription = $companyOwner->subscriptions()->where('status', 'active')->first();
            if (!$paddleSubscription) {
                return [];
            }
            $subItem = $paddleSubscription->items()->first();
            if (!$subItem) {
                return [];
            }
            $billingPlan = \App\Models\BillingPlan::with('features')->where('paddle_product_id', $subItem->product_id)->first();
            if (!$billingPlan) {
                return [];
            }
            $features = $billingPlan->features;
            return $features;
        }
        return [];
    }
}
