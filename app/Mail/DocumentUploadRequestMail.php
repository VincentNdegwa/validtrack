<?php

namespace App\Mail;

use App\Models\DocumentUploadRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DocumentUploadRequestMail extends Mailable implements ShouldQueue
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
        try {
            $url = route('public.document-upload', ['token' => $this->uploadRequest->token]);
            $expiryTime = $this->uploadRequest->expires_at->diffForHumans();

            return $this->subject('Document Upload Request for ' . $this->uploadRequest->subject->name)
                ->markdown('emails.document-upload-request', [
                    'uploadRequest' => $this->uploadRequest,
                    'url' => $url,
                    'expiryTime' => $expiryTime,
                ]);
        } catch (\Throwable $e) {
            Log::error('Failed to build DocumentUploadRequestMail: ' . $e->getMessage(), [
                'exception' => $e,
                'upload_request_id' => $this->uploadRequest->id ?? null,
            ]);
            throw $e;
        }
    }
}
