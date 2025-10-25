<?php

namespace App\Notifications\Slack;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Slack\SlackRoute;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DocumentExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Document $document,
        protected int $daysUntilExpiry
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['slack']; 
    }

    /**
     * Get the Slack representation of the notification using strict Block Kit.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        if ($this->daysUntilExpiry <= 7) {
            $urgencyEmoji = '🚨';
            $mainText = "⚠️ **DOCUMENT EXPIRATION WARNING** ⚠️";
            $expiryText = "*_{$this->document->documentType->name}_* is expiring **in less than a week!**";
        } elseif ($this->daysUntilExpiry <= 14) {
            $urgencyEmoji = '⚠️';
            $mainText = "Document Expiration Alert";
            $expiryText = "*_{$this->document->documentType->name}_* is expiring soon in *{$this->daysUntilExpiry} days*!";
        } else {
            $urgencyEmoji = '📅';
            $mainText = "Document Expiration Notice";
            $expiryText = "*_{$this->document->documentType->name}_* will expire in *{$this->daysUntilExpiry} days*";
        }

        $documentUrl = url("/documents/{$this->document->slug}");

        $message = (new SlackMessage)
            ->headerBlock($mainText)
            ->sectionBlock(function (SectionBlock $block) use ($urgencyEmoji, $expiryText, $documentUrl) {
                $block->text("<!channel> {$urgencyEmoji} {$expiryText}")->markdown();
            })

            ->dividerBlock()

            ->sectionBlock(function (SectionBlock $block) {
                $block->field("*Type:*\n{$this->document->documentType->name}")->markdown();
                $block->field("*Subject:*\n{$this->document->subject->name}")->markdown();
                $block->field("*Issued:*\n{$this->document->issue_date->format('M d, Y')}")->markdown();
                $block->field("*Expires:*\n:alarm_clock: **{$this->document->expiry_date->format('M d, Y')}**")->markdown();
            })

            ->when($this->document->notes, function(SlackMessage $message) {
                 $message->sectionBlock(function (SectionBlock $block) {
                    $block->text(":pencil: *Notes:*\n> {$this->document->notes}")->markdown();
                 });
                 $message->dividerBlock();
            })

            ->when($this->document->file_url, function (SlackMessage $message) use ($documentUrl) {
                $message->actionsBlock(function ($block) use ($documentUrl) {
                    $block->button('View/Renew Document', $documentUrl)
                          ->value('document_view_' . $this->document->id);
                });
            })
            
            ->contextBlock(function (ContextBlock $block) {
                $block->text("*Document ID:* #{$this->document->id} | *Company:* {$this->document->company->name}")->markdown();
                $block->text("Uploaded by: {$this->document->uploader->name} | Last updated: {$this->document->updated_at->diffForHumans()}")->markdown();
            });

        return $message;
    }
}