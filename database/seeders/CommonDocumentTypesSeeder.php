<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class CommonDocumentTypesSeeder extends Seeder
{
    /**
     * Common document types that are useful across most companies
     */
    private $commonDocumentTypes = [
        // Identity Documents
        'Passport',
        'National ID Card',
        'Driver\'s License',
        'Birth Certificate',

        // Employment Documents
        'Employment Contract',
        'Offer Letter',
        'CV/Resume',
        'Reference Letter',
        'Performance Review',
        'Termination Letter',

        // Education Documents
        'Degree Certificate',
        'Diploma',
        'Academic Transcript',
        'Professional Certification',
        'Training Certificate',

        // Financial Documents
        'Bank Statement',
        'Tax Return',
        'Invoice',
        'Receipt',
        'Financial Statement',

        // Legal Documents
        'NDA (Non-Disclosure Agreement)',
        'Terms of Service',
        'Privacy Policy',
        'Service Agreement',
        'Partnership Agreement',

        // Business Licenses & Permits
        'Business License',
        'Operating Permit',
        'Health & Safety Certificate',
        'Fire Safety Certificate',
        'Environmental Compliance Certificate',

        // Insurance Documents
        'Health Insurance',
        'Liability Insurance',
        'Professional Indemnity Insurance',
        'Property Insurance',
        'Vehicle Insurance',

        // Vehicle Documents
        'Vehicle Registration',
        'MOT Certificate',
        'Vehicle Inspection Report',
        'Vehicle Ownership Certificate',

        // Property Documents
        'Property Deed',
        'Lease Agreement',
        'Property Insurance',
        'Building Inspection Report',
        'Property Tax Assessment',

        // Compliance Documents
        'GDPR Compliance Certificate',
        'ISO Certification',
        'Industry-specific Compliance',
        'Data Security Audit Report',
        'Quality Assurance Certificate',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->info('No companies found. No document types created.');

            return;
        }

        $totalCreated = 0;

        // For each company, create the common document types if they don't already exist
        foreach ($companies as $company) {
            $this->command->info("Adding common document types for company: {$company->name}");

            $created = 0;

            // Process each document type individually using updateOrCreate
            foreach ($this->commonDocumentTypes as $typeName) {
                $result = DocumentType::updateOrCreate(
                    [
                        'name' => $typeName,
                        'company_id' => $company->id,
                    ],
                    [
                        'description' => "Standard document type: {$typeName}",
                        'updated_at' => now(),
                    ]
                );

                // Check if this was a new record
                if ($result->wasRecentlyCreated) {
                    $created++;
                }
            }

            // Skip the rest if no new document types were created
            if ($created === 0) {
                $this->command->info('Company already has all common document types.');

                continue;
            }

            $totalCreated += $created;

            $this->command->info("Created {$created} new document types for {$company->name}");
        }

        $this->command->info("Seeding completed. Total document types created: {$totalCreated}");
    }
}
