<?php

namespace App\Console\Commands;

use App\Mail\DocumentExpiryReminderMail;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Subject;
use App\Notifications\Slack\DocumentExpiryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDocumentExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:send-expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for documents that are about to expire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting document expiry reminder check...');        
        $companies = Company::all();
        
        foreach ($companies as $company) {
            //document_expiry_alerts
            $hasAccess = check_if_company_has_feature($company, 'document_expiry_alerts');
            if ($hasAccess) {
                $this->processCompanyReminders($company);
            }            
        }
        
        $this->info('Document expiry reminder check completed.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Process reminders for a specific company
     */
    protected function processCompanyReminders(Company $company)
    {
        $notificationsEnabled = $this->getCompanySetting(
            $company->id, 
            CompanySetting::NOTIFICATION_EMAIL_ENABLED, 
            true
        );
        
        if (!$notificationsEnabled) {
            $this->info("Notifications disabled for company: {$company->name}");
            return;
        }

        
        $reminderDays = $this->getCompanySetting(
            $company->id,
            CompanySetting::REMINDER_DEFAULT_DAYS,
            [30, 14, 7, 1]
        );
        
        if (is_string($reminderDays)) {
            $decodedArray = json_decode($reminderDays, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedArray)) {
                $reminderDays = $decodedArray;
            } elseif (is_numeric($reminderDays)) {
                $reminderDays = [(int)$reminderDays];
            }
        }
        
        Log::info("Reminder days for company {$company->id}: ", [
            'reminderDays' => $reminderDays
        ]);
        
        if (!is_array($reminderDays) || empty($reminderDays)) {
            $this->warn("No reminder days configured for company: {$company->name}");
            return;
        }
        
        // Process each reminder day
        foreach ($reminderDays as $days) {
            $this->processReminderForDays($company, (int)$days);
        }
    }
    
    /**
     * Get a company setting with fallback to default
     */
    protected function getCompanySetting($companyId, $key, $default)
    {
        $setting = CompanySetting::where('company_id', $companyId)
            ->where('key', $key)
            ->first();
            
        if ($setting) {
            // Since the value might be stored as a JSON string, we need to return it as is
            // The conversion will happen in the calling method
            return $setting->value ?? $default;
        }
        
        // Get default value from CompanySetting class constants
        $defaultValue = CompanySetting::getDefaultValue($key);
        
        // If default value exists in the constants, use it, otherwise use the provided default
        return $defaultValue !== null ? $defaultValue : $default;
    }
    
    /**
     * Process reminders for a specific day threshold
     */
    protected function processReminderForDays(Company $company, int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->startOfDay();
        
        $expiringDocuments = Document::where('company_id', $company->id)
            ->where('status', 1) 
            ->whereDate('expiry_date', $targetDate->toDateString())
            ->with(['documentType', 'subject'])
            ->get();
            
        if ($expiringDocuments->isEmpty()) {
            return;
        }
        
        $this->info("Found {$expiringDocuments->count()} documents expiring in {$days} days for company: {$company->name}");
        
        $documentsBySubject = $expiringDocuments->groupBy('subject_id');
        
        // Format documents for admin email
        $adminDocuments = $expiringDocuments->map(function ($doc) {
            return [
                'id' => $doc->id,
                'document_type' => $doc->documentType->name,
                'subject_name' => $doc->subject->name,
                'issue_date' => Carbon::parse($doc->issue_date)->format('Y-m-d'),
                'expiry_date' => Carbon::parse($doc->expiry_date)->format('Y-m-d'),
            ];
        })->toArray();
        
        // Send admin notifications (email and Slack)
        try {

            Mail::to($company->email)
                ->queue(new DocumentExpiryReminderMail(
                    $adminDocuments, 
                    $days, 
                    'admin'
                ));

            if ($company->has_slack_integration && $company->slackIntegration) {
                foreach ($expiringDocuments as $document) {
                    try {
                        $company->notify(new DocumentExpiryNotification(
                            $document,
                            $days,
                            $company->slackIntegration->webhook_url,
                            $company->slackIntegration->access_token
                        ));
                    } catch (\Exception $slackError) {
                        Log::error("Failed to send Slack notification for document {$document->id}: " . $slackError->getMessage());
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to send admin notifications for company {$company->id}: " . $e->getMessage());
        }
        
        // Send subject notifications
        foreach ($documentsBySubject as $subjectId => $documents) {
            $subject = Subject::find($subjectId);
            
            if (!$subject || !$subject->email) {
                continue;
            }
            
            $subjectDocuments = $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_type' => $doc->documentType->name,
                    'subject_name' => $doc->subject->name,
                    'issue_date' => Carbon::parse($doc->issue_date)->format('Y-m-d'),
                    'expiry_date' => Carbon::parse($doc->expiry_date)->format('Y-m-d'),
                ];
            })->toArray();
            
            try {
                Mail::to($subject->email)
                    ->queue(new DocumentExpiryReminderMail(
                        $subjectDocuments, 
                        $days, 
                        'subject'
                    ));
            } catch (\Exception $e) {
                Log::error("Failed to send subject reminder email for subject {$subjectId}: " . $e->getMessage());
            }
        }
    }
}
