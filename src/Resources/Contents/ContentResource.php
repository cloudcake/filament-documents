<?php

namespace Cloudcake\Documents\Resources\Contents;

use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Pages\Documentation;
use Cloudcake\Documents\Resources\Contents\Pages\CreateContent;
use Cloudcake\Documents\Resources\Contents\Pages\CreateSubContent;
use Cloudcake\Documents\Resources\Contents\Pages\EditContent;
use Cloudcake\Documents\Resources\Contents\Pages\EditSubContent;
use Cloudcake\Documents\Resources\Contents\Pages\ListContents;
use Cloudcake\Documents\Resources\Contents\RelationshipManagers\ContentRelationshipManager;
use Cloudcake\Documents\Resources\Contents\RelationshipManagers\RevisionRelationshipManager;
use Cloudcake\Documents\Resources\Contents\Schemas\ContentForm;
use Cloudcake\Documents\Resources\Contents\Schemas\ContentInfolist;
use Cloudcake\Documents\Resources\Contents\Tables\ContentsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationParentItem = Documentation::NAV_LABEL;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-file-text';

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;


    public static function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContents::route('/'),
            'create' => CreateContent::route('/create'),
            'edit' => EditContent::route('/{record}/edit'),
            'create-sub-content' => CreateSubContent::route('/{parent}/create'),
            'edit-sub-content' => EditSubContent::route('/{parent}/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ContentRelationshipManager::make(),
            RevisionRelationshipManager::make()
        ];
    }
}
