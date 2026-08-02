<?php

namespace App\Providers;

use App\Modules\Reminders\Contracts\WhatsAppProvider;
use App\Modules\Reminders\Infrastructure\WhatsAppCloudApiProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppProvider::class, WhatsAppCloudApiProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public-routine', function (Request $request): Limit {
            $tokenFingerprint = hash('sha256', (string) $request->route('token'));

            return Limit::perMinute(60)->by($request->ip().'|'.$tokenFingerprint);
        });
    }
}
