<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->hasPermission('roles-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = Permission::where(function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })
            ->withCount('roles');

        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('display_name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'display_name', 'roles_count', 'created_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }

        $query->orderBy($sortField, $sortDirection);

        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $permissions = $query->paginate($perPage);

        return Inertia::render('permissions/Index', [
            'permissions' => $permissions,
            'filters' => [
                'search' => $request->get('search', ''),
                'sort' => $sortField,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }
}
