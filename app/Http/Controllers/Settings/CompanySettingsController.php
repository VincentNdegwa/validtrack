<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanySettingsController extends Controller
{
    /**
     * Display the company settings page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;
        
        $settings = [
            'name' => $company->name,
            'logo' => $company->logo,
            'timezone' => $company->getSetting(CompanySetting::TIMEZONE),
            'reminder_default_days' => $company->getSetting(CompanySetting::REMINDER_DEFAULT_DAYS),
            'notification_email_enabled' => $company->getSetting(CompanySetting::NOTIFICATION_EMAIL_ENABLED),
        ];

        $timezones = \DateTimeZone::listIdentifiers();

        return Inertia::render('settings/Company', [
            'settings' => $settings,
            'timezones' => $timezones,
        ]);
    }

    /**
     * Update the company settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'in:' . implode(',', \DateTimeZone::listIdentifiers())],
            'reminder_default_days' => ['required', 'numeric', 'min:1', 'max:365'],
            'notification_email_enabled' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ]);
        
        $company = $request->user()->company;        
        $company->name = $validatedData['name'];
        
        if ($request->hasFile('logo')) {
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $path = $request->file('logo')->store('company-logos', 'public');
            $company->logo = $path;
        }
        
        $company->save();
        
        $company->setSetting(CompanySetting::TIMEZONE, $validatedData['timezone']);
        $company->setSetting(CompanySetting::REMINDER_DEFAULT_DAYS, (int)$validatedData['reminder_default_days']);
        
        // Handle checkbox value - ensure it's properly converted to boolean
        // This helps with values like "1", "0", 1, 0, "true", "false"
        $notificationEnabled = filter_var(
            $validatedData['notification_email_enabled'], 
            FILTER_VALIDATE_BOOLEAN
        );
        $company->setSetting(CompanySetting::NOTIFICATION_EMAIL_ENABLED, $notificationEnabled);
        
        return redirect()->route('settings.company')->with('success', 'Company settings updated successfully.');
    }
}