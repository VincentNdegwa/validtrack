<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\RespectsCompanyContext;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    use RespectsCompanyContext;
    
    
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('users-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $query = $this->scopeToCompany(User::query())
            ->with('roles');
        
        // Handle search if provided
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'email', 'is_active', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $users = $query->paginate($perPage);
        
        $roles = $this->scopeToCompany(Role::query())
            ->orderBy('name')
            ->get();

        return Inertia::render('users/Index', [
            'users' => $users,
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
     * Show the form for creating a new user.
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('users-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $roles = Role::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('users/Create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('users-create')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        //max_users
        $hasAccess = check_if_company_has_feature(Auth::user()->company_id, 'max_users');
        if (!$hasAccess) {
            return redirect()->back()->with('error', 'You have reached the maximum number of users allowed for your plan.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Create user with the current company ID
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'location' => $validated['location'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'company_id' => Auth::user()->company_id,
        ]);

        // Attach roles if provided
        if (!empty($validated['roles'])) {
            // Only attach roles that belong to the user's company
            $roleIds = Role::where('company_id', Auth::user()->company_id)
                ->whereIn('id', $validated['roles'])
                ->pluck('id')
                ->toArray();
            
            $user->roles()->attach($roleIds, ['company_id' => Auth::user()->company_id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        if (!Auth::user()->hasPermission('users-view')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $user = is_numeric($id) ? User::findOrFail($id) : User::findBySlugOrFail($id);
        
        // Make sure the user belongs to the current company
        if ($user->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $user->load('roles');

        return Inertia::render('users/Show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        if (!Auth::user()->hasPermission('users-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $user = is_numeric($id) ? User::findOrFail($id) : User::findBySlugOrFail($id);
        
        // Make sure the user belongs to the current company
        if ($user->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $roles = Role::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        $user->load('roles');

        return Inertia::render('users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $user->roles->pluck('id')->toArray()
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('users-edit')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $user = is_numeric($id) ? User::findOrFail($id) : User::findBySlugOrFail($id);
        
        // Make sure the user belongs to the current company
        if ($user->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Update user details
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->location = $validated['location'] ?? null;
        $user->is_active = $validated['is_active'] ?? $user->is_active;
        $user->save();

        // Update roles if provided
        if (isset($validated['roles'])) {
            // Only sync roles that belong to the user's company
            $roleIds = Role::where('company_id', Auth::user()->company_id)
                ->whereIn('id', $validated['roles'])
                ->pluck('id')
                ->toArray();
            
            // Detach all existing roles for this company
            $user->roles()->detach();
            // Attach new roles with company_id
            $user->roles()->attach($roleIds, ['company_id' => Auth::user()->company_id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('users-delete')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $user = is_numeric($id) ? User::findOrFail($id) : User::findBySlugOrFail($id);
        
        // Make sure the user belongs to the current company
        if ($user->company_id !== Auth::user()->company_id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Don't allow deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Delete the user
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
