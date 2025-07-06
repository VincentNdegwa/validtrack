<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PermissionController extends Controller
{

    public function index()
    {
        $permissions = Permission::where(function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->withCount('roles')
            ->orderBy('name')
            ->get();

        return Inertia::render('permissions/Index', [
            'permissions' => $permissions
        ]);
    }
}
