<?php

namespace Habib\MediaManager;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Habib\MediaManager\Http\Livewire\MediaManager as MediaManagerComponent;

class MediaManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mediamanager.php', 'mediamanager');
    }

    public function boot()
    {
        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mediamanager');

        // routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // livewire component
        Livewire::component('media-manager', MediaManagerComponent::class);


        // publishable
        $this->publishes([
            __DIR__.'/../config/mediamanager.php' => config_path('mediamanager.php'),
        ], 'config');

        // Publishable views
        $this->publishes([
            __DIR__.'/../resources/views/includes/media-modal.blade.php' =>
                resource_path('views/vendor/mediamanager/includes/media-modal.blade.php'),
        ], 'mediamanager-views');

        // Publish all views if needed
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/mediamanager'),
        ], 'mediamanager-all');
    }
}
