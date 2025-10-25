<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UserCompanyHelper;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        try {
            return Socialite::driver('google')
                ->stateless()
                ->with([
                    'access_type' => 'offline',
                    'prompt' => 'select_account consent',
                ])
                ->scopes([
                    'email',
                    'profile',
                    'https://www.googleapis.com/auth/userinfo.email',
                    'https://www.googleapis.com/auth/userinfo.profile',
                ])
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google login error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Unable to connect to Google. Please try again.');
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            if (! $googleUser) {
                return redirect()->route('login')->with('error', 'Unable to retrieve user information from Google.');
            }

            $existingUser = User::whereHas('tokens', function ($query) use ($googleUser) {
                $query->where('google_id', $googleUser->id);
            })->first();

            if ($existingUser) {
                Auth::login($existingUser);

                return redirect()->route('dashboard')->with('success', 'Logged in successfully.');
            }

            $user = UserCompanyHelper::createUserWithCompanyAndPermissions([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(Str::random(16)),
                'avatar' => $googleUser->avatar,
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);
            $user->tokens()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'google_id' => $googleUser->id,
                    'google_token' => isset($googleUser->token) ? $googleUser->token : null,
                    'google_refresh_token' => isset($googleUser->refreshToken) ? $googleUser->refreshToken : null,
                ]
            );
            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Logged in successfully.');

        } catch (\Exception $e) {
            Log::error('Google callback error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Google login failed: '.$e->getMessage());
        }
    }

    // ...existing code...
}
