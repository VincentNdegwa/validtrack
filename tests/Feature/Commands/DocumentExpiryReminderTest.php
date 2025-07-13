<?php

namespace Tests\Feature\Commands;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use App\Models\User;
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

        Carbon::setTestNow(Carbon::create(2025, 1, 1)->startOfDay());
        \Illuminate\Support\Facades\Log::info('Test date set to:', ['now' => Carbon::now()->toDateTimeString()]);
        
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'admin@testcompany.com',
            'owner_id' => null,
        ]);

        $user = User::factory()
            ->forCompany($company)
            ->create();

        $company->owner_id = $user->id;
        $company->save();

        // Create a subject
        $subject = Subject::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Subject',
            'email' => 'subject@example.com',
            'user_id' => $user->id
        ]);

        // Create a document type
        $documentType = DocumentType::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Document',
        ]);
        
        $today = Carbon::now()->startOfDay();
        $documents = [];
        
        $reminderDays = [1, 7, 14, 30];
        foreach ($reminderDays as $days) {
            $expiryDate = $today->copy()->addDays($days);
            
            \Illuminate\Support\Facades\Log::info("Creating test document expiring in {$days} days", [
                'expiry_date' => $expiryDate->toDateString(),
            ]);
            
            $documents[] = Document::factory()->create([
                'company_id' => $company->id,
                'subject_id' => $subject->id,
                'document_type_id' => $documentType->id,
                'issue_date' => $today->copy()->subDays(30),
                'expiry_date' => $expiryDate, // This should match exactly the date the command looks for
                'status' => 1,
            ]);
        }
        
        // Create a document that shouldn't trigger a reminder
        $documents[] = Document::factory()->create([
            'company_id' => $company->id,
            'subject_id' => $subject->id,
            'document_type_id' => $documentType->id,
            'issue_date' => $today->copy()->subDays(30),
            'expiry_date' => $today->copy()->addDays(60), // Expires in 60 days (no reminder)
            'status' => 1,
        ]);
        
        // Set custom reminder days for the company - ensure it's properly encoded for JSON casting
        $reminderDays = [30, 14, 7, 1];
        
        CompanySetting::create([
            'company_id' => $company->id,
            'key' => CompanySetting::REMINDER_DEFAULT_DAYS,
            'value' => $reminderDays,
        ]);
        
        // Verify the setting was created correctly
        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'key' => CompanySetting::REMINDER_DEFAULT_DAYS,
        ]);
        
        // Add logging to help debug
        $setting = CompanySetting::where('company_id', $company->id)
            ->where('key', CompanySetting::REMINDER_DEFAULT_DAYS)
            ->first();
            
        \Illuminate\Support\Facades\Log::info('Test reminder days:', [
            'value' => $setting->value,
            'value_type' => gettype($setting->value),
            'raw_value' => $setting->getRawOriginal('value'),
            'raw_type' => gettype($setting->getRawOriginal('value')),
        ]);
        
        
        $this->logExpiryDates($company->id);
        
        $this->artisan('documents:send-expiry-reminders', ['--verbose' => true])
            ->expectsOutput('Starting document expiry reminder check...')
            ->assertSuccessful();
            
        // we expect exactly 1 email for each reminder day (1, 7, 14, 30) × 2 recipients (admin + subject)
        $expectedEmails = count([1, 7, 14, 30]) * 2; // 8 emails in total
        
        Mail::assertQueued(\App\Mail\DocumentExpiryReminderMail::class, $expectedEmails);
        
        foreach ([1, 7, 14, 30] as $days) {
            Mail::assertQueued(\App\Mail\DocumentExpiryReminderMail::class, function ($mail) use ($subject, $days) {
                return $mail->daysUntilExpiry === $days && 
                       $mail->recipientType === 'subject' &&
                       $mail->hasTo($subject->email);
            });
            
            // Check admin emails
            Mail::assertQueued(\App\Mail\DocumentExpiryReminderMail::class, function ($mail) use ($company, $days) {
                return $mail->daysUntilExpiry === $days && 
                       $mail->recipientType === 'admin' &&
                       $mail->hasTo($company->email);
            });
        }
        
        // Reset the mocked time
        Carbon::setTestNow(null);
    }
    
    /**
     * Log document expiry dates to help with debugging
     */
    private function logExpiryDates($companyId)
    {
        $today = Carbon::now()->startOfDay();
        $documents = Document::where('company_id', $companyId)->get();
        
        \Illuminate\Support\Facades\Log::info('Document expiry dates:', [
            'today' => $today->toDateString(),
            'documents' => $documents->map(function($doc) use ($today) {
                $expiry = Carbon::parse($doc->expiry_date)->startOfDay();
                $daysUntil = $today->diffInDays($expiry, false);
                
                return [
                    'id' => $doc->id,
                    'expiry_date' => $expiry->toDateString(),
                    'days_until_expiry' => $daysUntil,
                    'matches_reminder' => in_array($daysUntil, [1, 7, 14, 30]),
                    'document_status' => $doc->status,
                ];
            })
        ]);
    }
}
