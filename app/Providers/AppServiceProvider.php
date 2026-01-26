<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;


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
        RateLimiter::for('ask-question', function (Request $request) {
            // IP + phone (থাকলে) দিয়ে আলাদা আলাদা limit
            $key = $request->ip() . '|' . ($request->input('phone') ?? 'na');
            return Limit::perMinute(5)->by($key);
        });
    }
}
