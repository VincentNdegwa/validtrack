<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SubjectType;
use Illuminate\Database\Seeder;

class CommonSubjectTypesSeeder extends Seeder
{
    /**
     * Common subject types that are useful across most companies
     */
    private $commonSubjectTypes = [
        // Employee-related subject types
        'Employee',
        'Contractor',
        'Consultant',
        'Intern',
        'Executive',
        'Manager',
        'Director',
        'Staff',
        'Remote Worker',
        'Part-time Employee',

        // Organization-related subject types
        'Department',
        'Team',
        'Branch',
        'Subsidiary',
        'Division',
        'Project Group',
        'Committee',

        // Business partner-related subject types
        'Vendor',
        'Supplier',
        'Client',
        'Customer',
        'Partner',
        'Distributor',
        'Retailer',
        'Manufacturer',
        'Service Provider',

        // Asset-related subject types
        'Vehicle',
        'Equipment',
        'Property',
        'Building',
        'Office',
        'Facility',
        'IT Asset',
        'Machinery',

        // Legal entity-related subject types
        'Company',
        'Subsidiary',
        'Joint Venture',
        'Branch Office',
        'Representative Office',
        'Limited Liability Partnership',
        'Sole Proprietorship',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->info('No companies found. No subject types created.');

            return;
        }

        $totalCreated = 0;

        // For each company, create the common subject types if they don't already exist
        foreach ($companies as $company) {
            $this->command->info("Adding common subject types for company: {$company->name}");

            $created = 0;

            // Process each subject type individually using updateOrCreate
            foreach ($this->commonSubjectTypes as $typeName) {
                $result = SubjectType::updateOrCreate(
                    [
                        'name' => $typeName,
                        'company_id' => $company->id,
                    ],
                    [
                        'updated_at' => now(),
                    ]
                );

                // Check if this was a new record
                if ($result->wasRecentlyCreated) {
                    $created++;
                }
            }

            // Skip the rest if no new subject types were created
            if ($created === 0) {
                $this->command->info('Company already has all common subject types.');

                continue;
            }

            $totalCreated += $created;

            $this->command->info("Created {$created} new subject types for {$company->name}");
        }

        $this->command->info("Seeding completed. Total subject types created: {$totalCreated}");
    }
}
