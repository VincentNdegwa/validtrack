<?php

namespace App\Http\Middleware;

use App\Services\ImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CompanyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        $isSuperAdmin = false;
        foreach ($user->roles as $role) {
            if ($role->name === 'super-admin') {
                $isSuperAdmin = true;
                break;
            }
        }

        // Check if we're impersonating someone
        $impersonationService = app(ImpersonationService::class);
        $isImpersonating = $impersonationService->isImpersonating();

        // If we're impersonating, share that information with the frontend
        if ($isImpersonating) {
            \Inertia\Inertia::share([
                'impersonating' => true,
                'impersonatorId' => $impersonationService->getImpersonatorId(),
                'impersonatedUserId' => $impersonationService->getImpersonatedId(),
            ]);
        }

        // If user is super-admin and there's an active company context in session
        if ($isSuperAdmin && Session::has('active_company_id')) {
            $activeCompanyId = Session::get('active_company_id');

            $request->attributes->add(['active_company_id' => $activeCompanyId]);

            \Inertia\Inertia::share('activeCompanyId', $activeCompanyId);
        }

        return $next($request);
    }
}
