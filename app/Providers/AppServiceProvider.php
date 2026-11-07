<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <--- Shu qator qo'shilishi shart

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
        // Faqat Ustozlar uchun ruxsat
        Gate::define('isTeacher', function ($user) {
            return $user->role === 'teacher';
        });
    }
}