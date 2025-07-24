<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['mail'];
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
}