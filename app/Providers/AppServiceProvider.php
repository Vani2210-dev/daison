<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

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
        if (!app()->runningInConsole() && app()->bound('request')) {
            $req = request();
            if (
                $req->isSecure() ||
                $req->header('x-forwarded-proto') === 'https' ||
                str_contains($req->getHttpHost() ?? '', 'ngrok')
            ) {
                URL::forceScheme('https');
            }
        }
    }
}
