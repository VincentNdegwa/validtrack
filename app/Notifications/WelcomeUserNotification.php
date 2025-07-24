<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['mail'];
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
}