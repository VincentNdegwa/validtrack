<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['admin', 'user', 'manager']),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($user) {
            try {
            //    Log::info('Creating user role for:', [
            //        'user' => $user
            //     ]); 

                if (!$user->company_id) {
                    \Illuminate\Support\Facades\Log::warning("User #{$user->id} has no company ID. Skipping role assignment.");
                    return;
                }
                
                $company = \App\Models\Company::find($user->company_id);
                if (!$company) {
                    \Illuminate\Support\Facades\Log::warning("Company #{$user->company_id} not found for user #{$user->id}. Skipping role assignment.");
                    return;
                }
                
                // Try to find an existing role that matches the user's role attribute
                $role = \App\Models\Role::where('name', $user->role)
                    ->where('company_id', $user->company_id)
                    ->first();
                
                // If no matching role exists, create one with all permissions
                if (!$role) {
                    \Illuminate\Support\Facades\Log::info("Role '{$user->role}' not found for company #{$user->company_id}. Creating it now.");
                    
                    // Create the role
                    $role = \App\Models\Role::create([
                        'name' => $user->role,
                        'display_name' => ucfirst($user->role),
                        'description' => ucfirst($user->role) . ' role for ' . $company->name,
                        'company_id' => $user->company_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Get all available permissions
                    $permissions = Permission::all()->pluck('id')->toArray();
                    
                    // Attach permissions to the role if we have any
                    if (!empty($permissions)) {
                        foreach ($permissions as $permissionId) {
                            \Illuminate\Support\Facades\DB::table('permission_role')->insert([
                                'permission_id' => $permissionId,
                                'role_id' => $role->id,
                                'company_id' => $user->company_id,
                            ]);
                        }
                    }
                }
                
                // Attach the role to the user if it's not already attached
                if (!$user->roles()->where('role_id', $role->id)->exists()) {
                    // Directly use DB insert to bypass any model events that might cause issues
                    \Illuminate\Support\Facades\DB::table('role_user')->insert([
                        'role_id' => $role->id,
                        'user_id' => $user->id,
                        'user_type' => get_class($user),
                        'company_id' => $user->company_id,
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error creating/attaching role for user: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configure the user as an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
    
    /**
     * Configure the user with a specific company.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }
}
