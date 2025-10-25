<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DocumentExpiryReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $documents,
        public int $daysUntilExpiry,
        public string $recipientType // 'admin' or 'subject'
    ) {}

    public function build()
    {
        try {
            $prefix = $this->daysUntilExpiry === 1 ? 'Tomorrow' : "In {$this->daysUntilExpiry} days";

            return $this->subject("{$prefix}: Document Expiry Notification")
                ->markdown('emails.document-expiry-reminder', [
                    'documents' => $this->documents,
                    'daysUntilExpiry' => $this->daysUntilExpiry,
                    'recipientType' => $this->recipientType,
                ]);
        } catch (\Throwable $e) {
            Log::error('Error building DocumentExpiryReminderMail: '.$e->getMessage(), [
                'exception' => $e,
                'daysUntilExpiry' => $this->daysUntilExpiry,
                'recipientType' => $this->recipientType,
            ]);
            throw $e;
        }
    }
}
