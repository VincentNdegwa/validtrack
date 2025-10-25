<?php

namespace App\Listeners;

use App\Events\CompanyCreated;
use App\Notifications\CompanyCreatedUserNotification;

class SendCompanyCreatedEmail
{
    public function handle(CompanyCreated $event)
    {
        $user = $event->user;
        $company = $event->company;
        $password = $event->password;
        $user->notify(new CompanyCreatedUserNotification($company, $user, $password));
    }
}
