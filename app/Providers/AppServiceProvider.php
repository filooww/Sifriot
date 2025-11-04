<?php

namespace App\Providers;

use App\Events\MetadataConfirmed;
use App\Listeners\ApplyConfirmedMetadataToPublication;
use Illuminate\Support\Facades\Event;
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
        // Register metadata confirmation listener
        Event::listen(
            MetadataConfirmed::class,
            ApplyConfirmedMetadataToPublication::class,
        );
    }
}
