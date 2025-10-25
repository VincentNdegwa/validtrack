<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationService
{
    /**
     * Start impersonating a user
     *
     * @param  User  $user  The user to impersonate
     */
    public function impersonate(User $user): void
    {
        Session::put('impersonator_id', Auth::id());
        Session::put('active_company_id', $user->company_id);
        Session::put('impersonated_user_id', $user->id);

        Auth::login($user);
    }

    /**
     * Stop impersonating and revert back to original user
     *
     * @return bool True if we were impersonating and successfully stopped
     */
    public function stopImpersonating(): bool
    {
        if (! Session::has('impersonator_id')) {
            return false;
        }

        $originalUserId = Session::get('impersonator_id');
        $originalUser = User::find($originalUserId);

        if (! $originalUser) {
            Session::forget(['impersonator_id', 'impersonated_user_id']);

            return false;
        }

        Auth::login($originalUser);

        Session::forget(['impersonator_id', 'impersonated_user_id']);

        return true;
    }

    /**
     * Check if the current session is impersonating another user
     */
    public function isImpersonating(): bool
    {
        return Session::has('impersonator_id');
    }

    /**
     * Get the ID of the user being impersonated
     */
    public function getImpersonatedId(): ?int
    {
        return Session::get('impersonated_user_id');
    }

    /**
     * Get the ID of the impersonator
     */
    public function getImpersonatorId(): ?int
    {
        return Session::get('impersonator_id');
    }
}
