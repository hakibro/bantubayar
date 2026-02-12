<?php

namespace App\Providers;

use App\Services\SiswaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiswaService::class, function () {
            return new SiswaService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto Hitung Lunas Observer
        \App\Models\SiswaPembayaran::observe(\App\Observers\SiswaPembayaranObserver::class);
    }
}
