<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

class UserCompanyHelper
{
    /**
     * Create user, company, and assign permissions
     * @param array $userData
     * @return User
     */
    public static function createUserWithCompanyAndPermissions(array $userData)
    {
        $companyName = $userData['name'] . "'s Workspace";
        $companyEmail = 'company_' . \Illuminate\Support\Str::slug($userData['name']) . '@' . explode('@', $userData['email'])[1];
        $company = Company::create([
            'name' => $companyName,
            'email' => $companyEmail,
        ]);
        $userData['company_id'] = $company->id;
        $user = User::create($userData);
        $company->owner_id = $user->id;
        $company->save();
        self::giveCompanyPermissions($company);
        return $user;
    }

    public static function giveCompanyPermissions(Company $company): void
    {
        $permissions = PermissionSeeder::getDefaultPermissions();
        $adminRole = $company->roles()->firstOrCreate([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrator with full access to all company features',
            'company_id' => $company->id,
        ]);
        $adminUser = $company->users()->where('role', 'admin')->first();
        if ($adminUser) {
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id => ['company_id' => $company->id]]);
        }
        $company->syncPermissions($permissions, $adminRole);
    }
}
