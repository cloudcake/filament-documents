<?php

namespace Cloudcake\Documents\Resources\Categories\Pages;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Pages\Settings;
use Cloudcake\Documents\Resources\Categories\Actions\CreateCategoryAction;
use Cloudcake\Documents\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

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

            CreateCategoryAction::make(),
        ];
    }
}
