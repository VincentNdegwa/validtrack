<?php

namespace Tests\Feature\Commands;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DocumentExpiryReminderTest extends TestCase
{
    use RefreshDatabase;
    
    public function testDocumentExpiryReminderCommand()
    {
        Mail::fake();
        
        // Create test data
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'admin@testcompany.com',
        ]);
        
        // Create a subject
        $subject = Subject::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Subject',
            'email' => 'subject@example.com'
        ]);
        
        // Create a document type
        $documentType = DocumentType::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Document',
        ]);
        
        // Create documents with different expiry dates
        $dates = [
            1 => Carbon::now()->addDays(1), // Expires tomorrow
            7 => Carbon::now()->addDays(7), // Expires in 7 days
            14 => Carbon::now()->addDays(14), // Expires in 14 days
            30 => Carbon::now()->addDays(30), // Expires in 30 days
            60 => Carbon::now()->addDays(60), // Not in reminder range
        ];
        
        foreach ($dates as $days => $date) {
            Document::factory()->create([
                'company_id' => $company->id,
                'subject_id' => $subject->id,
                'document_type_id' => $documentType->id,
                'issue_date' => Carbon::now()->subDays(30),
                'expiry_date' => $date,
                'status' => 1,
            ]);
        }
        
        // Set custom reminder days for the company
        CompanySetting::create([
            'company_id' => $company->id,
            'key' => CompanySetting::REMINDER_DEFAULT_DAYS,
            'value' => [30, 14, 7, 1],
        ]);
        
        // Run the command
        $this->artisan('documents:send-expiry-reminders')
            ->assertSuccessful();
            
        // Assertion: you would check for sent emails here
        // This is a basic test structure
    }
}
