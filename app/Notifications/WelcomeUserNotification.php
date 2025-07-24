<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;

class WelcomeUserNotification extends Notification
{
    use Queueable;

    protected $company;

    public function __construct($company)
    {
        $this->company = $company;
    }

    public function via($notifiable)
    {
        return ['mail', 'slack'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to ' . $this->company->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your account has been created for ' . $this->company->name . '.')
            ->line('You can now log in and start using your workspace.')
            ->action('Login', url('/login'))
            ->line('Thank you for joining us!');
    }

    public function toSlack(object $notifiable)
    {
        return (new SlackMessage)
            ->to('#validtrack-companies')
            ->text('A new company has been registered!')
            ->headerBlock('Company Registration')
            ->contextBlock(function (ContextBlock $block) use ($notifiable) {
                $block->text('A new user has been registered by the system.');
            })
            ->sectionBlock(function (SectionBlock $block) use ($notifiable) {
                $block->text('Company and owner details:');
                $block->field("*Company Name:*\n" . $this->company->name)->markdown();
                $block->field("*Company Email:*\n" . $this->company->email)->markdown();
                $block->field("*Owner Name:*\n" . $notifiable->name)->markdown();
                $block->field("*Owner Email:*\n" . $notifiable->email)->markdown();
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('Welcome to the platform!');
            });
    }
}