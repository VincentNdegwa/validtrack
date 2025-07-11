<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanySettingsController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->can('company-settings-view')){
            $company = $request->user()->company;

            $settings = [
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $company->address,
                'website' => $company->website,
                'location' => $company->location,
                'logo' => get_file_url($company->logo),
                'timezone' => $company->getSetting(CompanySetting::TIMEZONE),
                'reminder_default_days' => $company->getSetting(CompanySetting::REMINDER_DEFAULT_DAYS),
                'notification_email_enabled' => $company->getSetting(CompanySetting::NOTIFICATION_EMAIL_ENABLED),
            ];

            $timezones = \DateTimeZone::listIdentifiers();

            return Inertia::render('settings/Company', [
                'settings' => $settings,
                'timezones' => $timezones,
            ]);
        }else{
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function update(Request $request)
    {
        if(!Auth::user()->hasPermission('company-settings-edit')){
            return redirect()->back()->with('error', 'Permission denied.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'in:' . implode(',', \DateTimeZone::listIdentifiers())],
            'reminder_default_days' => ['required', 'array'],
            'reminder_default_days.*' => ['required', 'numeric', 'min:1', 'max:365'],
            'notification_email_enabled' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ]);
        
        $company = $request->user()->company;        
        
        // Update company details
        $company->name = $validatedData['name'];
        $company->email = $validatedData['email'] ?? null;
        $company->phone = $validatedData['phone'] ?? null;
        $company->address = $validatedData['address'] ?? null;
        $company->website = $validatedData['website'] ?? null;
        $company->location = $validatedData['location'] ?? null;
        
        if ($request->hasFile('logo')) {
            if ($company->logo) {
                $deleteResult = delete_file($company->logo);
                if (!$deleteResult['success']) {
                    Log::warning('Failed to delete old logo: ' . $deleteResult['message']);
                }
            }
            
            $uploadResult = upload_file($request->file('logo'), 'company-logos');
            if ($uploadResult['success']) {
                $company->logo = $uploadResult['path'];
            } else {
                return back()->withErrors(['logo' => $uploadResult['message']]);
            }
        }
        
        $company->save();
        
        $company->setSetting(CompanySetting::TIMEZONE, $validatedData['timezone']);
        
        $reminderDays = array_map('intval', $validatedData['reminder_default_days']);
        sort($reminderDays, SORT_NUMERIC);
        $reminderDays = array_reverse($reminderDays);
        $company->setSetting(CompanySetting::REMINDER_DEFAULT_DAYS, $reminderDays);
        
       
        $notificationEnabled = filter_var(
            $validatedData['notification_email_enabled'], 
            FILTER_VALIDATE_BOOLEAN
        );
        $company->setSetting(CompanySetting::NOTIFICATION_EMAIL_ENABLED, $notificationEnabled);
        
        return redirect()->route('settings.company')->with('success', 'Company settings updated successfully.');
    }
}