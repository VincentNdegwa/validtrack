<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
class CompanyCreatedUserNotification extends Notification
{
    use Queueable;

    protected $company;
    protected $user;
    protected $password;

    public function __construct($company, $user, $password)
    {
        $this->company = $company;
        $this->user = $user;
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail', 'slack'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Company Account Has Been Created')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new company account has been created for you:')
            ->line('Company: ' . $this->company->name)
            ->line('Company Email: ' . $this->company->email)
            ->line('Login Email: ' . $this->user->email)
            ->line('Login Password: ' . $this->password)
            ->line('Please do not share your login details with anyone.')
            ->action('Login', url('/login'))
            ->line('Thank you!');
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