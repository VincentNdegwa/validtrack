<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPermissions = $this->getDefaultPermissions();

        // Get all companies
        $companies = Company::all();
        
        // Create global permissions (not tied to any company)
        $globalPermissions = [
            [
                'name' => 'global-admin',
                'display_name' => 'Global Administrator',
                'description' => 'Has full access to all features across all companies',
            ],
            [
                'name' => 'company-create',
                'display_name' => 'Create Companies',
                'description' => 'Can create new companies',
            ],
            [
                'name' => 'company-view-all',
                'display_name' => 'View All Companies',
                'description' => 'Can view all companies in the system',
            ],
        ];
        
        foreach ($globalPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'company_id' => null],
                [
                    'display_name' => $permission['display_name'],
                    'description' => $permission['description'],
                ]
            );
        }
        
        // Create company-specific permissions for each company
        foreach ($companies as $company) {
            foreach ($defaultPermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission['name'], 'company_id' => $company->id],
                    [
                        'display_name' => $permission['display_name'],
                        'description' => $permission['description'],
                    ]
                );
            }
        }
    }
    
    /**
     * Get the default permissions list.
     * These will be created for each company.
     *
     * @return array
     */
    public static function getDefaultPermissions(): array
    {
        return [
            // User permissions
            [
                'name' => 'users-view',
                'display_name' => 'View Users',
                'description' => 'Can view all users in the company',
            ],
            [
                'name' => 'users-create',
                'display_name' => 'Create Users',
                'description' => 'Can create new users in the company',
            ],
            [
                'name' => 'users-edit',
                'display_name' => 'Edit Users',
                'description' => 'Can edit existing users in the company',
            ],
            [
                'name' => 'users-delete',
                'display_name' => 'Delete Users',
                'description' => 'Can delete users from the company',
            ],
            
            // Role permissions
            [
                'name' => 'roles-view',
                'display_name' => 'View Roles',
                'description' => 'Can view all roles in the company',
            ],
            [
                'name' => 'roles-create',
                'display_name' => 'Create Roles',
                'description' => 'Can create new roles in the company',
            ],
            [
                'name' => 'roles-edit',
                'display_name' => 'Edit Roles',
                'description' => 'Can edit existing roles in the company',
            ],
            [
                'name' => 'roles-delete',
                'display_name' => 'Delete Roles',
                'description' => 'Can delete roles from the company',
            ],
            
            // Subject permissions
            [
                'name' => 'subjects-view',
                'display_name' => 'View Subjects',
                'description' => 'Can view all subjects in the company',
            ],
            [
                'name' => 'subjects-create',
                'display_name' => 'Create Subjects',
                'description' => 'Can create new subjects in the company',
            ],
            [
                'name' => 'subjects-edit',
                'display_name' => 'Edit Subjects',
                'description' => 'Can edit existing subjects in the company',
            ],
            [
                'name' => 'subjects-delete',
                'display_name' => 'Delete Subjects',
                'description' => 'Can delete subjects from the company',
            ],
            
            // Subject Type permissions
            [
                'name' => 'subject-types-view',
                'display_name' => 'View Subject Types',
                'description' => 'Can view all subject types in the company',
            ],
            [
                'name' => 'subject-types-create',
                'display_name' => 'Create Subject Types',
                'description' => 'Can create new subject types in the company',
            ],
            [
                'name' => 'subject-types-edit',
                'display_name' => 'Edit Subject Types',
                'description' => 'Can edit existing subject types in the company',
            ],
            [
                'name' => 'subject-types-delete',
                'display_name' => 'Delete Subject Types',
                'description' => 'Can delete subject types from the company',
            ],
            
            // Document permissions
            [
                'name' => 'documents-view',
                'display_name' => 'View Documents',
                'description' => 'Can view all documents in the company',
            ],
            [
                'name' => 'documents-create',
                'display_name' => 'Create Documents',
                'description' => 'Can create new documents in the company',
            ],
            [
                'name' => 'documents-edit',
                'display_name' => 'Edit Documents',
                'description' => 'Can edit existing documents in the company',
            ],
            [
                'name' => 'documents-delete',
                'display_name' => 'Delete Documents',
                'description' => 'Can delete documents from the company',
            ],
            
            // Document Type permissions
            [
                'name' => 'document-types-view',
                'display_name' => 'View Document Types',
                'description' => 'Can view all document types in the company',
            ],
            [
                'name' => 'document-types-create',
                'display_name' => 'Create Document Types',
                'description' => 'Can create new document types in the company',
            ],
            [
                'name' => 'document-types-edit',
                'display_name' => 'Edit Document Types',
                'description' => 'Can edit existing document types in the company',
            ],
            [
                'name' => 'document-types-delete',
                'display_name' => 'Delete Document Types',
                'description' => 'Can delete document types from the company',
            ],
            
            // Dashboard permissions
            [
                'name' => 'dashboard-view',
                'display_name' => 'View Dashboard',
                'description' => 'Can view the company dashboard',
            ],
            
            // Reports permissions
            [
                'name' => 'reports-view',
                'display_name' => 'View Reports',
                'description' => 'Can view company reports',
            ],
            [
                'name' => 'reports-export',
                'display_name' => 'Export Reports',
                'description' => 'Can export company reports',
            ],

            // Company Settings permissions
            [
                'name' => 'company-settings-view',
                'display_name' => 'View Company Settings',
                'description' => 'Can view company settings',
            ],
            [
                'name' => 'company-settings-edit',
                'display_name' => 'Edit Company Settings',
                'description' => 'Can edit company settings',
            ],

            // Activity Log permissions
            [
                'name' => 'activity-log-view',
                'display_name' => 'View Activity Log',
                'description' => 'Can view the activity log of the company',
            ],

            // Billing permissions
            [
                'name' => 'manage-billing',
                'display_name' => 'Manage Billing',
                'description' => 'Can manage billing plans and features',
            ],
      
        ];
    }
}
