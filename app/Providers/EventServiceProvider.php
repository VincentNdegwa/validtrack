<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Illuminate\Auth\Events\Registered::class => [
            \App\Listeners\SendWelcomeEmail::class,
        ],
        \App\Events\CompanyCreated::class => [
            \App\Listeners\SendCompanyCreatedEmail::class,
        ],
    ];
}
