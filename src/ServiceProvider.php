<?php

namespace Cloudcake\Documents;

use Livewire\Livewire;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

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

//        Livewire::addNamespace(
//            namespace: 'cloudcake-documents',
//            classNamespace: 'Cloudcake\\Documents\\Livewire\\',
//            classPath: __DIR__ . '/Livewire',
//            classViewPath: __DIR__ . '/../resources/views/livewire',
//        );
    }

    private function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}