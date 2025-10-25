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

        // First detach existing permissions to avoid constraint violations
        $adminRole->permissions()->detach();

        // Then attach each permission individually with error handling
        foreach ($companyPermissions as $permission) {
            try {
                $adminRole->permissions()->attach($permission->id, [
                    'company_id' => $company->id,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Error attaching permission {$permission->id} to admin role for company {$company->id}: ".$e->getMessage());
            }
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

        // First detach existing permissions to avoid constraint violations
        $managerRole->permissions()->detach();

        // Then attach each permission individually with error handling
        foreach ($viewPermissions as $permission) {
            try {
                $managerRole->permissions()->attach($permission->id, [
                    'company_id' => $company->id,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Error attaching permission {$permission->id} to manager role for company {$company->id}: ".$e->getMessage());
            }
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

        // First detach existing permissions to avoid constraint violations
        $userRole->permissions()->detach();

        // Then attach each permission individually with error handling
        foreach ($basicPermissions as $permission) {
            try {
                $userRole->permissions()->attach($permission->id, [
                    'company_id' => $company->id,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Error attaching permission {$permission->id} to user role for company {$company->id}: ".$e->getMessage());
            }
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

        // First detach all permissions to avoid unique constraint violations
        $superAdminRole->permissions()->detach();

        // Build array of permissions to attach
        foreach ($allPermissions as $permission) {
            if ($permission->company_id === null) {
                // Global permission - attach for all companies
                foreach ($allCompanies as $company) {
                    try {
                        // Use attach with a try/catch to handle any errors
                        $superAdminRole->permissions()->attach($permission->id, [
                            'company_id' => $company->id,
                        ]);
                    } catch (\Exception $e) {
                        // Log the error but continue processing
                        $this->command->warn("Error attaching permission {$permission->id} to role {$superAdminRole->id} for company {$company->id}: ".$e->getMessage());
                    }
                }
            } else {
                // Company-specific permission
                try {
                    // Use attach with a try/catch to handle any errors
                    $superAdminRole->permissions()->attach($permission->id, [
                        'company_id' => $permission->company_id,
                    ]);
                } catch (\Exception $e) {
                    // Log the error but continue processing
                    $this->command->warn("Error attaching permission {$permission->id} to role {$superAdminRole->id} for company {$permission->company_id}: ".$e->getMessage());
                }
            }
        }
    }
}
