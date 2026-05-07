<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

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
        $timezone = (string) config('app.timezone', 'UTC');
        date_default_timezone_set($timezone);

        Carbon::serializeUsing(function (Carbon $carbon) use ($timezone) {
            return $carbon->copy()->setTimezone($timezone)->format('Y-m-d H:i:s');
        });
    }
}
