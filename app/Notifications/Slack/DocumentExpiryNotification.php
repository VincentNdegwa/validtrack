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

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Document $document,
        protected int $daysUntilExpiry
    ) {
    }


    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $urgencyEmoji = $this->daysUntilExpiry <= 7 ? '🚨' : 
                       ($this->daysUntilExpiry <= 14 ? '⚠️' : '📅');

        return (new SlackMessage)
            ->text('Document Expiry Alert')
            ->headerBlock('Document Expiry Alert')
            ->contextBlock(function (ContextBlock $block) use ($urgencyEmoji) {
                $block->text("{$urgencyEmoji} Document ID: #{$this->document->id}");
                $block->text("Company: {$this->document->company->name}");
            })
            ->sectionBlock(function (SectionBlock $block) {
                $block->text("*{$this->document->documentType->name}* will expire in *{$this->daysUntilExpiry} days*")->markdown();
                $block->field("*Document Name:*\n{$this->document->name}")->markdown();
                $block->field("*Expiry Date:*\n{$this->document->expiry_date->format('M d, Y')}")->markdown();
            })
            ->sectionBlock(function (SectionBlock $block) {
                $block->text("*Subject Details:*\n{$this->document->subject->name}")->markdown();
                
                if ($this->document->notes) {
                    $block->field("*Notes:*\n{$this->document->notes}")->markdown();
                }
            })
            ->dividerBlock()
            ->when($this->document->file_path, function(SlackMessage $message) {
                $message->sectionBlock(function (SectionBlock $block) {
                    $block->text("<" . url("/documents/{$this->document->id}") . "|View Document>")->markdown();
                });
            });
    }
}