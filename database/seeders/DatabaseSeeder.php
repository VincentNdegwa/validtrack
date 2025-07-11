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
        $superAdminCompany = $this->createCompany('ValidTrack System Administration', 'admin@validtrack.com', true);
        $clientCompany = $this->createCompany('Demo Client Company', 'info@democompany.com');
        
        $users = $this->createUsers($clientCompany, $superAdminCompany);
        
        $this->seedPermissionsAndRoles();
        $this->assignRolesToUsers($users, $clientCompany, $superAdminCompany);
        $this->call([
            CompanySettingsSeeder::class,
        ]);
    }

    /**
     * Create a company with the given details
     */
    private function createCompany(string $name, string $email, bool $isAdmin = false): Company
    {
        return Company::factory()->create([
            'name' => $name,
            'email' => $email,
            'location' => $isAdmin ? 'System Administration HQ' : 'Client Location',
        ]);
    }

    private function createUsers(Company $regularCompany, Company $adminCompany): array
    {
        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@validtrack.com',
            'company_id' => $adminCompany->id,
            'role'=> 'super-admin',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'company_id' => $regularCompany->id,
            'role'=>'admin',
        ]);
        
        
        User::factory(2)->create([
            'company_id' => $regularCompany->id,
            'role' => 'user',
        ]);

        return [
            'admin' => $adminUser,
            'superAdmin' => $superAdminUser,
            'regularCompany' => $regularCompany,
            'adminCompany' => $adminCompany
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
    private function assignRolesToUsers(array $users, Company $regularCompany, Company $adminCompany): void
    {
        // For the regular company: assign admin role to the admin user and user roles to regular users
        $this->assignAdminRole($users['admin'], $regularCompany);
        $this->assignUserRolesToRegularUsers(
            $users['admin']->id, 
            $users['superAdmin']->id, 
            $regularCompany
        );
        
        // For the admin company: assign super-admin role to the super admin user only
        $this->assignSuperAdminRole($users['superAdmin'], $adminCompany);
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

    private function assignSuperAdminRole(User $superAdminUser, Company $adminCompany): void
    {
        // 1. Assign the global super-admin role (in their own company)
        $superAdminRole = Role::where('name', 'super-admin')
            ->whereNull('company_id')
            ->first();
            
        if ($superAdminRole) {
            $superAdminUser->roles()->attach($superAdminRole->id, ['company_id' => $adminCompany->id]);
        }

        // 2. Also give the super-admin user admin access to all other companies
        $otherCompanies = Company::where('id', '!=', $adminCompany->id)->get();
        
        foreach ($otherCompanies as $company) {
            $adminRole = Role::where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();
                
            if ($adminRole) {
                $superAdminUser->roles()->attach($adminRole->id, ['company_id' => $company->id]);
            }
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
