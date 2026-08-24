<?php

namespace Cloudcake\Documents\Resources\Contents\RelationshipManagers;

use Cloudcake\Documents\Resources\Contents\ContentResource;
use Cloudcake\Documents\Resources\Contents\Schemas\ContentForm;
use Cloudcake\Documents\Resources\Contents\Tables\ContentsTable;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContentRelationshipManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Sub-Content';

    protected static string|BackedEnum|null $icon = 'tabler-sitemap';


    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Sub-Content')
                ->url(fn (): string => ContentResource::getUrl('create-sub-content', [
                    'parent' => $this->getOwnerRecord()->getKey(),
                ])),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ContentsTable::configure($table, true)
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['creator', 'revisions.author'])
                ->whereNotNull('parent_id')
                ->orderBy("sort", 'asc')
            );
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return !$ownerRecord->parent_id;
    }
}