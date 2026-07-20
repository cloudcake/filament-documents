<?php

namespace Cloudcake\Documents\Resources\Categories;

use Cloudcake\Documents\Models\Category;
use Cloudcake\Documents\Pages\Documentation;
use Cloudcake\Documents\Resources\Categories\Pages\ListCategories;
use Cloudcake\Documents\Resources\Categories\Schemas\CategoryForm;
use Cloudcake\Documents\Resources\Categories\Schemas\CategoryInfolist;
use Cloudcake\Documents\Resources\Categories\Tables\CategoriesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $navigationParentItem = Documentation::NAV_LABEL;

    protected static ?string $model = Category::class;
    protected static string|BackedEnum|null $navigationIcon = 'tabler-file-text';

    protected Width | string | null $maxContentWidth = Width::SevenExtraLarge;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCategories::route('/'),
        ];
    }
}
