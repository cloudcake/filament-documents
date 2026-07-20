<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Pages\Settings;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

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

            CreateAction::make()->createAnother(false)->slideOver(),
        ];
    }
}
