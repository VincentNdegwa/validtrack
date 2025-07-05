<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('permission:roles-view');
    }
    
    /**
     * Display a listing of the permissions.
     * Shows all available permissions that can be assigned to roles.
     */
    public function index()
    {
        $permissions = Permission::where(function ($query) {
                $query->where('company_id', Auth::user()->company_id)
                    ->orWhereNull('company_id'); // Include global permissions
            })
            ->withCount('roles')
            ->orderBy('name')
            ->get();

        return Inertia::render('permissions/Index', [
            'permissions' => $permissions
        ]);
    }
}
