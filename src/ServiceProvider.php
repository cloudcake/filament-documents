<?php

namespace Cloudcake\Documents;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        // Required for livewire 4 since we do deep nesting things.
        config([
            'livewire.payload.max_nesting_depth' => 20,
        ]);
    }

    public function boot(): void
    {
        $this->bootAssets();
        $this->bootLanguage();
        $this->bootMigrations();
        $this->bootViews();
    }

    private function bootAssets(): void
    {
    }

    private function bootLanguage(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cloudcake-documents');
    }

    private function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cloudcake-documents');
    }

    private function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}