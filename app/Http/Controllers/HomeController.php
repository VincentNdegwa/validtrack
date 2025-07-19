<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $plans = BillingPlan::where('is_featured', 1)
            ->with('features')
            ->get()
            ->map(function ($plan) {
                $plan->friendly_features = $plan->getFriendlyFeatures();
                return $plan;
            });

        return Inertia::render('CompliTrack', [
            'plans' => $plans,
        ]);
    }
}
