<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
