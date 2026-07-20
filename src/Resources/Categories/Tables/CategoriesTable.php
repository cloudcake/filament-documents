<?php

namespace Cloudcake\Documents\Resources\Categories\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\HeaderActionsPosition;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActionsPosition(HeaderActionsPosition::Adaptive)
            ->modifyQueryUsing(fn($query) => $query->withCount('contents'))
            ->defaultSort('sort', 'asc')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('sort')
                    ->label('Order'),

                TextColumn::make('name')
                    ->sortable(),

                IconColumn::make('published')
                    ->sortable()
                    ->alignCenter()
                    ->falseColor('gray')
                    ->label('Published'),

                IconColumn::make('private')
                    ->sortable()
                    ->alignCenter()
                    ->falseColor('gray')
                    ->label('Private')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contents_count')
                    ->label('Contents')
                    ->alignCenter()
                    ->badge()
                    ->numeric(decimalPlaces: 0),

                TextColumn::make('created_at')
                    ->alignRight()
                    ->dateTime()
            ])
            ->recordActions([
                EditAction::make()->modal()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ]);
    }
}
