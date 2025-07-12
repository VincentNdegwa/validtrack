<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentExpiryReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $documents,
        public int $daysUntilExpiry,
        public string $recipientType // 'admin' or 'subject'
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        $prefix = $this->daysUntilExpiry === 1 ? 'Tomorrow' : "In {$this->daysUntilExpiry} days";
        
        return $this->subject("{$prefix}: Document Expiry Notification")
            ->markdown('emails.document-expiry-reminder', [
                'documents' => $this->documents,
                'daysUntilExpiry' => $this->daysUntilExpiry,
                'recipientType' => $this->recipientType
            ]);
    }
}
