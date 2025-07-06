<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {

            $adminRole = Role::firstOrCreate(
                ['name' => 'admin', 'company_id' => $company->id],
                [
                    'display_name' => 'Administrator',
                    'description' => 'Administrator with full access to all company features',
                ]
            );
            
            $companyPermissions = Permission::where('company_id', $company->id)->get();
            $permissionIds = $companyPermissions->pluck('id')->toArray();

            $adminRole->permissions()->detach();
            foreach ($companyPermissions as $permission) {
                $adminRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
            }
            
            $managerRole = Role::firstOrCreate(
                ['name' => 'manager', 'company_id' => $company->id],
                [
                    'display_name' => 'Manager',
                    'description' => 'Manager with view access to all company resources',
                ]
            );
            
            // Get all view permissions
            $viewPermissions = Permission::where('company_id', $company->id)
                ->where(function($query) {
                    $query->where('name', 'like', '%-view');
                })
                ->get();
            
            // Attach view permissions to manager role
            $managerRole->permissions()->detach();
            foreach ($viewPermissions as $permission) {
                $managerRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
            }
            
            // Create a User role with limited access
            $userRole = Role::firstOrCreate(
                ['name' => 'user', 'company_id' => $company->id],
                [
                    'display_name' => 'Standard User',
                    'description' => 'Standard user with limited access to company resources',
                ]
            );
            
            // Get basic permissions for standard users
            $basicPermissions = Permission::where('company_id', $company->id)
                ->whereIn('name', [
                    'dashboard-view',
                    'subjects-view',
                    'documents-view',
                    'subject-types-view',
                    'document-types-view',
                ])
                ->get();
            
            // Attach basic permissions to user role
            $userRole->permissions()->detach();
            foreach ($basicPermissions as $permission) {
                $userRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
            }
        }
        
        // Create a global administrator role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'company_id' => null],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Super Administrator with access to all companies and features',
            ]
        );
        
        // Get all permissions (both global and company-specific from all companies)
        $allPermissions = Permission::all();
        
        // Detach any existing permissions first
        $superAdminRole->permissions()->detach();
        
        // Get all companies
        $allCompanies = Company::all();
        
        foreach ($allPermissions as $permission) {
            if ($permission->company_id === null) {
                // Global permission: attach once for each company
                foreach ($allCompanies as $company) {
                    $superAdminRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
                }
            } else {
                // Company-specific permission: attach with its own company_id
                $superAdminRole->permissions()->attach($permission->id, ['company_id' => $permission->company_id]);
            }
        }
    }
}
