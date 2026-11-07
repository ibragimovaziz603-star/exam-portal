<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- 1-qo'shimcha

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 2-qo'shimcha: Render uchun majburiy HTTPS
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}