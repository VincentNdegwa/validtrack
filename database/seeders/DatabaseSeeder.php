<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company = $this->createCompany();
        $users = $this->createUsers($company);
        
        $this->seedPermissionsAndRoles();
        $this->assignRolesToUsers($users, $company);
    }

    /**
     * Create the default company
     */
    private function createCompany(): Company
    {
        return Company::factory()->create([
            'name' => 'Demo Company',
            'email' => 'info@democompany.com',
        ]);
    }


    private function createUsers(Company $company): array
    {
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'company_id' => $company->id,
        ]);
        
        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'company_id' => $company->id,
        ]);
        
        User::factory(5)->create([
            'company_id' => $company->id,
        ]);

        return [
            'admin' => $adminUser,
            'superAdmin' => $superAdminUser
        ];
    }

    /**
     * Run the permission and role seeders
     */
    private function seedPermissionsAndRoles(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }

    /**
     * Assign roles to users
     */
    private function assignRolesToUsers(array $users, Company $company): void
    {
        $this->assignAdminRole($users['admin'], $company);
        $this->assignSuperAdminRole($users['superAdmin']);
        $this->assignUserRolesToRegularUsers($users['admin']->id, $users['superAdmin']->id, $company);
    }

    /**
     * Assign admin role to the admin user
     */
    private function assignAdminRole(User $adminUser, Company $company): void
    {
        $adminRole = Role::where('name', 'admin')
            ->where('company_id', $company->id)
            ->first();
            
        if ($adminRole) {
            $adminUser->roles()->attach($adminRole->id, ['company_id' => $company->id]);
        }
    }

    /**
     * Assign super-admin role to the super admin user
     */
    private function assignSuperAdminRole(User $superAdminUser): void
    {
        $superAdminRole = Role::where('name', 'super-admin')
            ->whereNull('company_id')
            ->first();
            
        if ($superAdminRole) {
            // Use the user's company_id since role_user.company_id cannot be NULL
            $superAdminUser->roles()->attach($superAdminRole->id, ['company_id' => $superAdminUser->company_id]);
        }
    }

    /**
     * Assign user role to all regular users
     */
    private function assignUserRolesToRegularUsers(int $adminUserId, int $superAdminUserId, Company $company): void
    {
        $userRole = Role::where('name', 'user')
            ->where('company_id', $company->id)
            ->first();
            
        if ($userRole) {
            User::where('id', '!=', $adminUserId)
                ->where('id', '!=', $superAdminUserId)
                ->where('company_id', $company->id)
                ->get()
                ->each(function ($user) use ($userRole, $company) {
                    $user->roles()->attach($userRole->id, ['company_id' => $company->id]);
                });
        }
    }
}
