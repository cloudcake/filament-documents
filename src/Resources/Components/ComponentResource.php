<?php

namespace Cloudcake\Documents\Resources\Components;

use Cloudcake\Documents\Models\Component;
use Cloudcake\Documents\Pages\Documentation;
use Cloudcake\Documents\Resources\Components\Pages\CreateComponent;
use Cloudcake\Documents\Resources\Components\Pages\EditComponent;
use Cloudcake\Documents\Resources\Components\Pages\ListComponents;
use Cloudcake\Documents\Resources\Components\RelationshipManagers\ComponentRelationshipManager;
use Cloudcake\Documents\Resources\Components\Schemas\ComponentForm;
use Cloudcake\Documents\Resources\Components\Schemas\ComponentInfolist;
use Cloudcake\Documents\Resources\Components\Tables\ComponentsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;

class ComponentResource extends Resource
{
    protected static ?string $model = Component::class;

    protected static ?string $navigationParentItem = Documentation::NAV_LABEL;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-file-text';

    protected Width | string | null $maxContentWidth = Width::SevenExtraLarge;

    public static function form(Schema $schema): Schema
    {
        return ComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComponentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListComponents::route('/'),
        ];
    }
}
