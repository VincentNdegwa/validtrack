<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default company
        $company = Company::factory()->create([
            'name' => 'Demo Company',
            'email' => 'info@democompany.com',
        ]);
        
        // Create admin user
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'company_id' => $company->id,
        ]);
        
        // Create super admin user (global access)
        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'company_id' => $company->id,
        ]);
        
        // Create additional test users
        User::factory(5)->create([
            'company_id' => $company->id,
        ]);
        
        // Run permission seeder
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
        
        // Assign admin role to the admin user
        $adminRole = \App\Models\Role::where('name', 'admin')
            ->where('company_id', $company->id)
            ->first();
            
        if ($adminUser && $adminRole) {
            $adminUser->roles()->attach($adminRole->id, ['company_id' => $company->id]);
        }
        
        // Assign super-admin role to the super admin user
        $superAdminRole = \App\Models\Role::where('name', 'super-admin')
            ->whereNull('company_id')
            ->first();
            
        if ($superAdminUser && $superAdminRole) {
            $superAdminUser->roles()->attach($superAdminRole->id, ['company_id' => null]);
        }
        
        // Assign user role to the rest of the users
        $userRole = \App\Models\Role::where('name', 'user')
            ->where('company_id', $company->id)
            ->first();
            
        if ($userRole) {
            User::where('id', '!=', $adminUser->id)
                ->where('id', '!=', $superAdminUser->id)
                ->where('company_id', $company->id)
                ->get()
                ->each(function ($user) use ($userRole, $company) {
                    $user->roles()->attach($userRole->id, ['company_id' => $company->id]);
                });
        }
    }
}
