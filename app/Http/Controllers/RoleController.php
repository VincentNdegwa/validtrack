<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\RespectsCompanyContext;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoleController extends Controller
{
    use RespectsCompanyContext;
    
    // The middleware is applied in the routes file
    
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = $this->scopeToCompany(Role::query())
            ->withCount('users', 'permissions')
            ->orderBy('name')
            ->get();

        return Inertia::render('roles/Index', [
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::where(function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
                })
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Create role with the current company ID
        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'company_id' => Auth::user()->company_id
        ]);

        // Attach permissions if provided
        if (!empty($validated['permissions'])) {
            $permissionIds = Permission::where(function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
                })
                ->whereIn('id', $validated['permissions'])
                ->pluck('id')
                ->toArray();
            
            $role->permissions()->attach($permissionIds, ['company_id' => Auth::user()->company_id]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show($id)
    {
        // Try to find the role by ID or slug
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);
        
        // Make sure the role belongs to the current company
        if ($role->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $role->load('permissions', 'users');

        return Inertia::render('roles/Show', [
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id)
    {
        // Try to find the role by ID or slug
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);
        
        // Make sure the role belongs to the current company
        if ($role->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $permissions = Permission::where(function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        $role->load('permissions');

        return Inertia::render('roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $role->permissions->pluck('id')->toArray()
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, $id)
    {
        // Try to find the role by ID or slug
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);
        
        // Make sure the role belongs to the current company
        if ($role->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('roles')->where(function ($query) use ($role) {
                    return $query->where('company_id', Auth::user()->company_id);
                })->ignore($role->id)
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Update role
        $role->name = $validated['name'];
        $role->display_name = $validated['display_name'] ?? null;
        $role->description = $validated['description'] ?? null;
        $role->save();

        // Update permissions if provided
        if (isset($validated['permissions'])) {
            // Only sync permissions that belong to the user's company or are global
            $permissionIds = Permission::where(function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
                })
                ->whereIn('id', $validated['permissions'])
                ->pluck('id')
                ->toArray();
            
            // Detach all existing permissions for this role and company
            $role->permissions()->detach();
            // Attach new permissions with company_id
            $role->permissions()->attach($permissionIds, ['company_id' => Auth::user()->company_id]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);        
        if ($role->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role that is assigned to users.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
