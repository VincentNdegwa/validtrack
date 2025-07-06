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
    // The middleware is applied in the routes file
    
    /**
     * Display a listing of all companies.
     */
    public function index()
    {
        $companies = Company::withCount('users')->orderBy('name')->get();
        
        return Inertia::render('companies/Index', [
            'companies' => $companies
        ]);
    }
    
    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return Inertia::render('companies/Create');
    }
    
    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $company = Company::create($validated);
        
        // Run the permission and role seeders for this new company
        $permissionSeeder = new \Database\Seeders\PermissionSeeder();
        $permissionSeeder->run();
        
        $roleSeeder = new \Database\Seeders\RoleSeeder();
        $roleSeeder->run();
        
        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully');
    }
    
    /**
     * Display the specified company.
     */
    public function show($id)
    {
        $company = is_numeric($id) ? Company::findOrFail($id) : Company::findBySlugOrFail($id);

        $company->load(['users' => function($query) {
            $query->orderBy('name');
        }]);
        
        return Inertia::render('companies/Show', [
            'company' => $company
        ]);
    }
    
    /**
     * Show the form for editing the specified company.
     */
    public function edit($id)
    {
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
