<?php

namespace App\Listeners;

use App\Notifications\WelcomeUserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Registered $event)
    {
        $user = $event->user;
        $company = $user->company;
        $user->notify(new WelcomeUserNotification($company));
    }
}
