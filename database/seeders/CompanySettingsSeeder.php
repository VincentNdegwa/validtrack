<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            foreach (CompanySetting::DEFAULTS as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value) : $value;

                CompanySetting::updateOrCreate([
                    'company_id' => $company->id,
                    'key' => $key,
                ], [
                    'value' => $formattedValue,
                ]);
            }
        }
    }
}
