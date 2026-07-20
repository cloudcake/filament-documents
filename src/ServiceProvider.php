<?php

namespace Cloudcake\Documents;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot(): void
    {
        $this->bootAssets();
        $this->bootLanguage();
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
}