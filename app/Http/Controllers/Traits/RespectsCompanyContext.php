<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait RespectsCompanyContext
{
    /**
     * Get the current company context.
     */
    protected function getCompanyContext(): ?int
    {
        // Check if user is super admin
        $user = Auth::user();
        $isSuperAdmin = false;

        if ($user) {
            foreach ($user->roles as $role) {
                if ($role->name === 'super-admin') {
                    $isSuperAdmin = true;
                    break;
                }
            }
        }

        if ($isSuperAdmin && Session::has('active_company_id')) {
            // If super admin has active context, use that
            return Session::get('active_company_id');
        } elseif ($user) {
            // Otherwise use user's own company
            return $user->company_id;
        }

        return null;
    }

    /**
     * Scope a query to the current company context.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeToCompany($query)
    {
        $companyId = $this->getCompanyContext();

        if ($companyId) {
            return $query->where('company_id', $companyId);
        }

        return $query;
    }
}
