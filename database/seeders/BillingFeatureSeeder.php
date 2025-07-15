<?php

namespace Database\Seeders;

use App\Models\BillingFeature;
use Illuminate\Database\Seeder;

class BillingFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all features from pricing tiers
        $features = [
            // Storage features
            [
                'name' => 'Document Storage',
                'key' => 'document_storage',
                'type' => 'number',
                'description' => 'Amount of storage space available for documents',
            ],
            
            // User management features
            [
                'name' => 'User Management',
                'key' => 'max_users',
                'type' => 'number',
                'description' => 'Maximum number of users allowed',
            ],
            
            // Document types features
            [
                'name' => 'Document Types Management',
                'key' => 'max_document_types',
                'type' => 'number',
                'description' => 'Maximum number of custom document types allowed',
            ],

            // Document management features
            [
                'name' => 'Document Management',
                'key' => 'max_documents',
                'type' => 'number',
                'description' => 'Maximum number of documents allowed',
            ],

            // Subject types features
            [
                'name' => 'Subject Types Management',
                'key' => 'max_subject_types',
                'type' => 'number',
                'description' => 'Maximum number of custom subject types allowed',
            ],

            // Subjects
            [
                'name' => 'Subject Management',
                'key' => 'max_subjects',
                'type' => 'number',
                'description' => 'Maximum number of custom subject allowed',
            ],
            
            // Document features
            [
                'name' => 'Document Expiry Alerts',
                'key' => 'document_expiry_alerts',
                'type' => 'boolean',
                'description' => 'Notification level for document expiry',
            ],
            [
                'name' => 'Manual Document Upload',
                'key' => 'manual_document_upload',
                'type' => 'boolean',
                'description' => 'Ability to manually upload documents',
            ],
            [
                'name' => 'Document Upload Requests',
                'key' => 'document_upload_requests',
                'type' => 'boolean',
                'description' => 'Ability to send document upload requests to external parties',
            ],
            
            // Security features
            [
                'name' => 'Role-Based Access Control',
                'key' => 'role_based_access',
                'type' => 'boolean',
                'description' => 'Level of role-based permission controls',
            ],
            [
                'name' => 'Verification Codes',
                'key' => 'verification_codes',
                'type' => 'boolean',
                'description' => 'Secure document submissions via verification codes',
            ],
            
            // Audit and notification features
            [
                'name' => 'Activity Logging',
                'key' => 'activity_logging',
                'type' => 'boolean',
                'description' => 'Audit trails for document activities',
            ],
            [
                'name' => 'Custom Notifications',
                'key' => 'custom_notifications',
                'type' => 'boolean',
                'description' => 'Customizable email notifications',
            ],
            
            // Advanced features
            // [
            //     'name' => 'Advanced Search & Filter',
            //     'key' => 'advanced_search',
            //     'type' => 'boolean',
            //     'description' => 'Advanced search and filtering capabilities',
            // ],
            [
                'name' => 'Bulk Operations',
                'key' => 'bulk_operations',
                'type' => 'boolean',
                'description' => 'Batch document processing',
            ],
            [
                'name' => 'Custom Domain',
                'key' => 'custom_domain',
                'type' => 'boolean',
                'description' => 'Use your own domain for accessing the ValidTrack system',
            ],
            [
                'name' => 'E-signature',
                'key' => 'esignature',
                'type' => 'boolean',
                'description' => 'Legally binding electronic signature capabilities for documents',
            ],
            [
                'name' => 'MultiTenant Support',
                'key' => 'multitenant',
                'type' => 'boolean',
                'description' => 'Host multiple separate organizations with complete data isolation',
            ],
            [
                'name' => 'Scheduled Reports',
                'key' => 'scheduled_reports',
                'type' => 'boolean',
                'description' => 'Automated report generation and delivery at specified intervals',
            ],
            [
                'name' => 'API Access',
                'key' => 'api_access',
                'type' => 'boolean',
                'description' => 'Full REST API access for custom integrations and automation',
            ],
            
            // Add-on features
            [
                'name' => 'Additional Storage',
                'key' => 'additional_storage',
                'type' => 'boolean',
                'description' => 'Option to purchase additional storage ($5/month per 10GB)',
            ],
            [
                'name' => 'SSO Integration',
                'key' => 'sso_integration',
                'type' => 'boolean',
                'description' => 'Single Sign-On integration capability ($10/month)',
            ],
            [
                'name' => 'Custom Integrations',
                'key' => 'custom_integrations',
                'type' => 'boolean',
                'description' => 'Custom integration services (Starting at $15/month)',
            ],
            [
                'name' => 'Advanced Analytics',
                'key' => 'advanced_analytics',
                'type' => 'boolean',
                'description' => 'Advanced analytics and reporting package ($20/month)',
            ],
            [
                'name' => 'Dedicated Account Manager',
                'key' => 'account_manager',
                'type' => 'boolean',
                'description' => 'Dedicated account manager service ($50/month)',
            ],
        ];

        // Create or update each feature
        foreach ($features as $feature) {
            BillingFeature::updateOrCreate(
                ['key' => $feature['key']],
                $feature
            );
        }

        $this->command->info('Billing features seeded successfully.');
    }
}
