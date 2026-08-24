<?php

namespace Cloudcake\Documents\Pages;

use Cloudcake\Documents\Models\Document;
use Cloudcake\Documents\Resources\Widgets\HitsChart;
use Cloudcake\Documents\Resources\Widgets\HitStats;
use Cloudcake\Documents\Resources\Widgets\TopSearchHits;
use Cloudcake\Documents\Resources\Widgets\TopSearchMisses;
use Cloudcake\Documents\Resources\Widgets\SearchStats;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class Documentation extends Page implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    public const string NAV_LABEL = 'Documentation';

    protected string $view = 'cloudcake-documents::layouts.page';

    protected static string|BackedEnum|null $navigationIcon = 'tabler-certificate';

    protected static ?string $title = self::NAV_LABEL;

    protected static ?string $navigationLabel = self::NAV_LABEL;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    public Document|null $record = null;

    public array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_document')
                ->label('View Document')
                ->color('gray')
                ->url(fn() => url()->to(filament()->getCurrentPanel()->getPlugin(\Cloudcake\Documents\Document::$id)->getAdminFor()), shouldOpenInNewTab: true),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HitStats::make(),
            SearchStats::make(),
            TopSearchHits::make(),
            TopSearchMisses::make(),
        ];
    }

    public function mount(): void
    {
        $this->record = Document::current();
        $this->data = $this->record->settings ?? [];
        $this->schema->fill($this->data);
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function save()
    {
        $this->record = Document::current();

        $this->callHook('beforeValidate');

        $data = $this->schema->getState();

        $this->callHook('afterValidate');

        $this->record->settings = $data;
        $this->record->save();

        Notification::make()
            ->icon(static::$navigationIcon)
            ->title(static::getNavigationLabel() . ' Updated')
            ->body('Successfully updated.')
            ->send();
    }
}