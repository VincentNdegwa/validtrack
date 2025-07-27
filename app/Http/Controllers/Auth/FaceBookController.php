<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\UserCompanyHelper;
use Illuminate\Http\Request;
use App\Events\RegisteredUser;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class FaceBookController extends Controller
{
    public function loginUsingFacebook(Request $request)
    {
        try {
            return Socialite::driver('facebook')
                ->stateless()
                ->with([
                    'display' => 'popup',
                    'auth_type' => 'rerequest'
                ])
                ->scopes(['email', 'public_profile'])
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Facebook login error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to connect to Facebook. Please try again.');
        }
    }

    public function callbackFromFacebook(Request $request)
    {
        try {
            $fbUser = Socialite::driver('facebook')
                ->stateless()
                ->user();
            if(!$fbUser) {
                return redirect()->route('login')->with('error', 'Unable to retrieve user information from Facebook.');
            }
            $existingFbUser = User::whereHas('tokens', function ($query) use ($fbUser) {
                $query->where('facebook_id', $fbUser->id);
            })->first();
            if ($existingFbUser) {
                Auth::login($existingFbUser);
                return redirect()->route('dashboard')->with('success', 'Logged in successfully.');
            }

            $user = UserCompanyHelper::createUserWithCompanyAndPermissions([
                'name' => $fbUser->name,
                'email' => $fbUser->email,
                'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                'avatar' => $fbUser->avatar,
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);
            $user->tokens()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'facebook_id' => $fbUser->id,
                    'facebook_token' => isset($fbUser->token) ? $fbUser->token : null,
                    'facebook_refresh_token' => isset($fbUser->refreshToken) ? $fbUser->refreshToken : null,
                ]
            );
            event(new Registered($user));
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Logged in successfully.');
        } catch (\Exception $e) {
            Log::error('Facebook callback error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Facebook login failed: ' . $e->getMessage());
        }
    }

    // ...existing code...
}
