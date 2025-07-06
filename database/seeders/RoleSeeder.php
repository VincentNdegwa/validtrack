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
            $permissionIds = $companyPermissions->pluck('id')->toArray();
            $adminRole->permissions()->detach();
            $adminRole->permissions()->attach($permissionIds);
            
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
            $viewPermissionIds = $viewPermissions->pluck('id')->toArray();
            $managerRole->permissions()->detach();
            $managerRole->permissions()->attach($viewPermissionIds);
            
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
            $basicPermissionIds = $basicPermissions->pluck('id')->toArray();
            $userRole->permissions()->detach();
            $userRole->permissions()->attach($basicPermissionIds);
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
        
        // Detach any existing permissions first
        $superAdminRole->permissions()->detach();
        
        // Attach global permissions to super admin role without using company_id in pivot
        foreach ($globalPermissions as $permission) {
            $superAdminRole->permissions()->attach($permission->id);
        }
    }
}
