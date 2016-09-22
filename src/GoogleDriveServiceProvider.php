<?php

namespace Pixbit\GoogleDrive;

use Illuminate\Support\ServiceProvider;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-google-drive.php' => config_path('laravel-google-drive.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-google-drive.php', 'laravel-google-drive');

        $this->app->bind(GoogleDrive::class, function () {

            $driveId = config('laravel-google-drive.drive_id');

            return GoogleDriveFactory::createForDriveId($driveId);
        });

        $this->app->alias(GoogleDrive::class, 'laravel-google-drive');
    }
}
