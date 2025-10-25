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
            CommonDocumentTypesSeeder::class,
            CommonSubjectTypesSeeder::class,
            BillingFeatureSeeder::class,
        ]);
    }

    /**
     * Create a company with the given details
     */
    private function createCompany(string $name, string $email, bool $isAdmin = false): Company
    {
        // Use updateOrCreate to handle existing companies
        return Company::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'location' => $isAdmin ? 'System Administration HQ' : 'Client Location',
                'owner_id' => null,
            ]
        );
    }

    private function createUsers(Company $regularCompany, Company $adminCompany): array
    {
        // Check for existing super admin user
        $superAdminUser = User::where('email', 'superadmin@validtrack.com')->first();

        if (! $superAdminUser) {
            $superAdminUser = User::factory()
                ->forCompany($adminCompany)
                ->create([
                    'name' => 'Super Admin',
                    'email' => 'superadmin@validtrack.com',
                    'role' => 'super-admin',
                ]);

            $adminCompany->owner_id = $superAdminUser->id;
            $adminCompany->save();
        }

        // Check for existing admin user
        $adminUser = User::where('email', 'admin@example.com')->first();

        if (! $adminUser) {
            $adminUser = User::factory()
                ->forCompany($regularCompany)
                ->create([
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                ]);
            $regularCompany->owner_id = $adminUser->id;
            $regularCompany->save();
        }

        // Only create regular users if we don't have enough
        $existingRegularUsers = User::where('company_id', $regularCompany->id)
            ->where('role', 'user')
            ->count();

        if ($existingRegularUsers < 2) {
            $neededUsers = 2 - $existingRegularUsers;
            if ($neededUsers > 0) {
                User::factory($neededUsers)
                    ->forCompany($regularCompany)
                    ->create([
                        'role' => 'user',
                    ]);
            }
        }

        return [
            'admin' => $adminUser,
            'superAdmin' => $superAdminUser,
            'regularCompany' => $regularCompany,
            'adminCompany' => $adminCompany,
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
        $this->assignAdminRole($users['admin'], $regularCompany);
        $this->assignUserRolesToRegularUsers(
            $users['admin']->id,
            $users['superAdmin']->id,
            $regularCompany
        );

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
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id => ['company_id' => $company->id]]);
        }
    }

    private function assignSuperAdminRole(User $superAdminUser, Company $adminCompany): void
    {
        $superAdminRole = Role::where('name', 'super-admin')
            ->whereNull('company_id')
            ->first();

        if ($superAdminRole) {
            $superAdminUser->roles()->syncWithoutDetaching([$superAdminRole->id => ['company_id' => $adminCompany->id]]);
        }

        $otherCompanies = Company::where('id', '!=', $adminCompany->id)->get();

        foreach ($otherCompanies as $company) {
            $adminRole = Role::where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();

            if ($adminRole) {
                $superAdminUser->roles()->syncWithoutDetaching([$adminRole->id => ['company_id' => $company->id]]);
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
                    $user->roles()->syncWithoutDetaching([$userRole->id => ['company_id' => $company->id]]);
                });
        }
    }
}
