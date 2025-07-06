<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
  
    public function run(): void
    {
        $this->seedSuperAdminRole();
        $this->seedCompanyRoles();
    }

    /**
     * Seed company-specific roles with their permissions
     */
    private function seedCompanyRoles(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createAdminRole($company);
            $this->createManagerRole($company);
            $this->createUserRole($company);
        }
    }

    /**
     * Create admin role with all company permissions
     */
    private function createAdminRole(Company $company): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'company_id' => $company->id],
            [
                'display_name' => 'Administrator',
                'description' => 'Administrator with full access to all company features',
            ]
        );
        
        $companyPermissions = Permission::where('company_id', $company->id)->get();
        
        $adminRole->permissions()->detach();
        foreach ($companyPermissions as $permission) {
            $adminRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
        }
    }

    /**
     * Create manager role with view permissions
     */
    private function createManagerRole(Company $company): void
    {
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager', 'company_id' => $company->id],
            [
                'display_name' => 'Manager',
                'description' => 'Manager with view access to all company resources',
            ]
        );
        
        $viewPermissions = Permission::where('company_id', $company->id)
            ->where('name', 'like', '%-view')
            ->get();
        
        $managerRole->permissions()->detach();
        foreach ($viewPermissions as $permission) {
            $managerRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
        }
    }

    /**
     * Create user role with basic view permissions
     */
    private function createUserRole(Company $company): void
    {
        $userRole = Role::firstOrCreate(
            ['name' => 'user', 'company_id' => $company->id],
            [
                'display_name' => 'Standard User',
                'description' => 'Standard user with limited access to company resources',
            ]
        );
        
        $basicPermissions = Permission::where('company_id', $company->id)
            ->whereIn('name', [
                'dashboard-view',
                'subjects-view',
                'documents-view',
                'subject-types-view',
                'document-types-view',
            ])
            ->get();
        
        $userRole->permissions()->detach();
        foreach ($basicPermissions as $permission) {
            $userRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
        }
    }

    /**
     * Seed super-admin role with access to all permissions
     */
    private function seedSuperAdminRole(): void
    {
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'company_id' => null],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Super Administrator with access to all companies and features',
            ]
        );
        
        $allPermissions = Permission::all();
        $allCompanies = Company::all();
        
        $superAdminRole->permissions()->detach();
        
        foreach ($allPermissions as $permission) {
            if ($permission->company_id === null) {
                foreach ($allCompanies as $company) {
                    $superAdminRole->permissions()->attach($permission->id, ['company_id' => $company->id]);
                }
            } else {
                $superAdminRole->permissions()->attach($permission->id, ['company_id' => $permission->company_id]);
            }
        }
    }
}
