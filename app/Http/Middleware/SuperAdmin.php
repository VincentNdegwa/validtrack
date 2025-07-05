<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has the super-admin role
        $isSuperAdmin = false;
        foreach ($user->roles as $role) {
            if ($role->name === 'super-admin') {
                $isSuperAdmin = true;
                break;
            }
        }

        if (!$isSuperAdmin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have super administrator privileges.'
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'You do not have super administrator privileges.');
        }

        return $next($request);
    }
}
