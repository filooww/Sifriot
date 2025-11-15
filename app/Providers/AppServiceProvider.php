<?php

namespace App\Providers;

use App\Contracts\FileStorageServiceInterface;
use App\Contracts\LoggerServiceInterface;
use App\Events\MetadataConfirmed;
use App\Listeners\ApplyConfirmedMetadataToPublication;
use App\Services\FileStorageService;
use App\Services\LoggerService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is called during the container's registration phase.
     * It's where we bind interfaces to implementations for dependency injection.
     */
    public function register(): void
    {
        /**
         * Register File Storage Service
         *
         * Using singleton() means Laravel will create ONE instance of FileStorageService
         * and reuse it throughout the application lifecycle.
         * This is efficient for stateless services.
         */
        $this->app->singleton(
            FileStorageServiceInterface::class,
            FileStorageService::class
        );

        /**
         * Register Logger Service
         *
         * Same pattern - one instance throughout the app
         */
        $this->app->singleton(
            LoggerServiceInterface::class,
            LoggerService::class
        );
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
