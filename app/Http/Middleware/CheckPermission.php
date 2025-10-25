<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
