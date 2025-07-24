<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        // Create a company for the user
        $companyName = $request->name . "'s Workspace";
        $companyEmail = 'company_' . Str::slug($request->name) . '@' . explode('@', $request->email)[1];
        
        $company = Company::create([
            'name' => $companyName,
            'email' => $companyEmail,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $company->id,
            'role' => 'admin',
        ]);
        $company->owner_id = $user->id;
        $company->save();
        $this->giveCompanyPermissions($company);

        event(new Registered($user));
        Auth::login($user);

        return to_route('dashboard');
    }

    public function giveCompanyPermissions(Company $company): void
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
