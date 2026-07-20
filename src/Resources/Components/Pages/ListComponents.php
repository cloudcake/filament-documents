<?php

namespace Cloudcake\Documents\Resources\Components\Pages;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Pages\Settings;
use Cloudcake\Documents\Resources\Components\Actions\CreateComponentAction;
use Cloudcake\Documents\Resources\Components\ComponentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListComponents extends ListRecords
{
    protected static string $resource = ComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_document')
                  ->label('View Document')
                  ->color('gray')
                  ->url(fn() => Document::viewPanel()->getUrl(), shouldOpenInNewTab: true),

            Action::make('edit_document')
                  ->label('Edit Settings')
                  ->color('gray')
                  ->url(fn() => Settings::getNavigationUrl(), shouldOpenInNewTab: false),
        ];
    }
}
