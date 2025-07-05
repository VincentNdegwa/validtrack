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
            
            // Attach all company permissions to admin role
            $permissionsData = [];
            foreach ($companyPermissions as $permission) {
                $permissionsData[$permission->id] = ['company_id' => $company->id];
            }
            
            $adminRole->permissions()->sync($permissionsData);
            
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
            $viewPermissionsData = [];
            foreach ($viewPermissions as $permission) {
                $viewPermissionsData[$permission->id] = ['company_id' => $company->id];
            }
            
            $managerRole->permissions()->sync($viewPermissionsData);
            
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
            $basicPermissionsData = [];
            foreach ($basicPermissions as $permission) {
                $basicPermissionsData[$permission->id] = ['company_id' => $company->id];
            }
            
            $userRole->permissions()->sync($basicPermissionsData);
        }
        
        // Create a global administrator role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'company_id' => null],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Super Administrator with access to all companies and features',
            ]
        );
        
        // Get global permissions
        $globalPermissions = Permission::whereNull('company_id')->get();
        
        // Attach global permissions to super admin role
        $globalPermissionsData = [];
        foreach ($globalPermissions as $permission) {
            $globalPermissionsData[$permission->id] = ['company_id' => null];
        }
        
        $superAdminRole->permissions()->sync($globalPermissionsData);
    }
}
