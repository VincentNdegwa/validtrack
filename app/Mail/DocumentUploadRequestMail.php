<?php

namespace App\Mail;

use App\Models\DocumentUploadRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentUploadRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public DocumentUploadRequest $uploadRequest
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        $url = route('public.document-upload', ['token' => $this->uploadRequest->token]);
        $expiryTime = $this->uploadRequest->expires_at->diffForHumans();
        
        return $this->subject('Document Upload Request for ' . $this->uploadRequest->subject->name)
            ->markdown('emails.document-upload-request', [
                'uploadRequest' => $this->uploadRequest,
                'url' => $url,
                'expiryTime' => $expiryTime,
            ]);
    }
}
