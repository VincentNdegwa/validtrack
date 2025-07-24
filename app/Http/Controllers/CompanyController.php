<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CompanyController extends Controller
{

    public function index(Request $request)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search', '');
        
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;
        
        $query = Company::withCount('users', 'owner');
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $allowedSortFields = ['name', 'email', 'created_at', 'is_active', 'users_count'];
        $allowedDirections = ['asc', 'desc'];
        
        if (in_array($sortField, $allowedSortFields) && in_array(strtolower($sortDirection), $allowedDirections)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $companies = $query->paginate($perPage)->withQueryString();
        
        return Inertia::render('companies/Index', [
            'companies' => $companies,
            'filters' => [
                'sort' => $sortField,
                'direction' => $sortDirection,
                'search' => $search,
                'per_page' => $perPage
            ]
        ]);
    }
    
    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        return Inertia::render('companies/Create');
    }
    
    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $validated = $request->validate([
            // Company fields
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:companies,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:255',
            // User fields
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:users,email',
            'user_password' => 'required|string|min:8',
            'user_phone' => 'nullable|string|max:50',
            'user_address' => 'nullable|string|max:255',
        ]);

        // Create company
        $company = Company::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'website' => $validated['website'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Create user (owner/admin)
        $user = \App\Models\User::create([
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['user_password']),
            'company_id' => $company->id,
            'role' => 'admin',
            'phone' => $validated['user_phone'],
            'address' => $validated['user_address'],
            'is_active' => true,
        ]);

        // Set company owner
        $company->owner_id = $user->id;
        $company->save();

        // Assign admin role and permissions
        $adminRole = $company->roles()->firstOrCreate([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrator with full access to all company features',
            'company_id' => $company->id,
        ]);
        $permissions = \Database\Seeders\PermissionSeeder::getDefaultPermissions();
        $company->syncPermissions($permissions, $adminRole);
        $user->roles()->syncWithoutDetaching([$adminRole->id => ['company_id' => $company->id]]);

        event(new \App\Events\CompanyCreated($company, $user, $request->user_password ));
        return redirect()->route('companies.index')
            ->with('success', 'Company and owner created successfully');
    }
    
    /**
     * Display the specified company.
     */
    public function show($id)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $company = is_numeric($id) ? Company::findOrFail($id) : Company::findBySlugOrFail($id);

        $company->load([
            'users.roles' => function($query) {
            $query->orderBy('name');
            }, 
        'owner'
    ]);
        
        return Inertia::render('companies/Show', [
            'company' => $company
        ]);
    }
    
    /**
     * Show the form for editing the specified company.
     */
    public function edit($id)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $company = is_numeric($id) ? Company::findOrFail($id) : Company::findBySlugOrFail($id);

        return Inertia::render('companies/Edit', [
            'company' => $company
        ]);
    }
    
    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);
        
        $company->update($validated);
        
        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully');
    }
    
    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        // Check if any users are associated with this company
        if ($company->users()->count() > 0) {
            return redirect()->route('companies.index')
                ->with('error', 'Cannot delete company with associated users');
        }
        
        $company->delete();
        
        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully');
    }

    public function switchCompany(Request $request)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $companyId = $request->input('company_id');
        
        if (!$companyId) {
            Session::forget('active_company_id');
            app(\App\Services\ImpersonationService::class)->stopImpersonating();
            return redirect()->back()->with('success', 'Switched to global view');
        }
        
        $company = Company::find($companyId);
        
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found');
        }
        Session::put('active_company_id', $company->id);
        $users = $company->users()->where('is_active', true)->get();
        
        return Inertia::render('companies/UserSelection', [
            'company' => $company,
            'users' => $users,
        ]);
    }
    
    /**
     * Impersonate a user in the selected company.
     */
    public function impersonateUser(Request $request)
    {
        if (!Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $userId = $request->input('user_id');
        
        if (!$userId) {
            return redirect()->back()->with('error', 'No user selected');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }
        
        $currentUser = Auth::user();
        $isSuperAdmin = false;
        foreach ($currentUser->roles as $role) {
            if ($role->name === 'super-admin') {
                $isSuperAdmin = true;
                break;
            }
        }
        
        if (!$isSuperAdmin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized action');
        }
        
        app(\App\Services\ImpersonationService::class)->impersonate($user);
        
        return redirect()->route('dashboard')->with('success', 'You are now viewing as ' . $user->name);
    }
    
    /**
     * Stop impersonating a user and return to the super admin account.
     */
    public function stopImpersonation()
    {
        $impersonationService = app(\App\Services\ImpersonationService::class);
        
        if ($impersonationService->stopImpersonating()) {
            return redirect()->route('dashboard')->with('success', 'Returned to your account');
        }
        
        return redirect()->route('dashboard')->with('error', 'Not currently impersonating');
    }
}
