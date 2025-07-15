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
    
    private function getAvailablePermissions()
    {
        return Permission::where(function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();
    }
    
    private function filterPermissionIds(array $permissionIds)
    {
        return Permission::where(function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->whereIn('id', $permissionIds)
            ->pluck('id')
            ->toArray();
    }
    
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('roles-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = $this->scopeToCompany(Role::query())
            ->withCount('users', 'permissions');
            
        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('display_name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'display_name', 'users_count', 'permissions_count', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $roles = $query->paginate($perPage);

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'filters' => [
                'search' => $request->get('search', ''),
                'sort' => $sortField,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('roles-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $permissions = $this->getAvailablePermissions();

        return Inertia::render('roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('roles-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        // role_based_access
        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'role_based_access');
        if (!$hasAccess) {
            return redirect()->back()->with('error', 'Your plan does not allow role based access.');
        }
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
            $permissionIds = $this->filterPermissionIds($validated['permissions']);
            
            if (!empty($permissionIds)) {
                $attachData = array_fill_keys($permissionIds, ['company_id' => Auth::user()->company_id]);
                $role->permissions()->attach($attachData);
            }
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show($id)
    {
        if (!Auth::user()->hasPermission('roles-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);
        
        // Make sure the role belongs to the current company
        if ($role->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
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
        if (!Auth::user()->hasPermission('roles-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);
        
        if ($role->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $permissions = $this->getAvailablePermissions();

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
        if (!Auth::user()->hasPermission('roles-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);
        
        // Make sure the role belongs to the current company
        if ($role->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
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

        if (isset($validated['permissions'])) {
            $permissionIds = $this->filterPermissionIds($validated['permissions']);
            $role->permissions()->detach();
            if (!empty($permissionIds)) {
                $attachData = array_fill_keys($permissionIds, ['company_id' => Auth::user()->company_id]);
                $role->permissions()->attach($attachData);
            }
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('roles-delete')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $role = is_numeric($id) ? Role::findOrFail($id) : Role::findBySlugOrFail($id);        
        if ($role->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
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
