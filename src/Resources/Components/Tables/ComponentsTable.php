<?php

namespace Cloudcake\Documents\Resources\Components\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\HeaderActionsPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComponentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActionsPosition(HeaderActionsPosition::Adaptive)
            ->columns([
                TextColumn::make('name')
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn($record) => $record->component_name),

                TextColumn::make('created_at')
                    ->alignRight()
                    ->dateTime()
            ])
            ->recordActions([
                EditAction::make()->modal()->modalWidth(Width::SevenExtraLarge),
                DeleteAction::make(),
            ]);
    }
}
