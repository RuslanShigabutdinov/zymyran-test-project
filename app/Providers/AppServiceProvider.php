<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Observers\KaspiProductObserver;
use App\Models\KaspiProduct;

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
        KaspiProduct::observe(KaspiProductObserver::class);
    }
}
