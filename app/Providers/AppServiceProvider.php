<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (app()->environment('local') && request()->isSecure()) {
            URL::forceScheme('https');
        }

        // Set default timezone for Carbon and PHP
        Carbon::setLocale('en');
        date_default_timezone_set('Asia/Kuala_Lumpur');
    }
}
