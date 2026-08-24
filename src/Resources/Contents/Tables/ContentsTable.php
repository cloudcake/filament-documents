<?php

namespace Cloudcake\Documents\Resources\Contents\Tables;

use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Models\Document;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\HeaderActionsPosition;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class ContentsTable
{
    public static function configure(Table $table, bool $isChildTable = false): Table
    {
        $ratingSystemEnabled = Document::current()->setting(DocumentRatingSystemSetting::Enabled, false);

        return $table
            ->headerActionsPosition(HeaderActionsPosition::Adaptive)
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['creator', 'revisions.author'])
                ->when(!$isChildTable,
                    fn($query) => $query->whereNull('parent_id')
                        ->withCount(['children'])
                        ->withAggregate('category', 'sort')
                        ->orderBy("category_sort", 'asc')
                )
                ->orderBy("sort", 'asc')
            )
            ->reorderableColumns()
            ->when(
                !$isChildTable,
                fn(Table $table) => $table
                    ->defaultGroup('category.name')
                    ->groupingDirectionSettingHidden()
                    ->groupingSettingsHidden()
                    ->groups([
                        Group::make('category.name')
                            ->label('Category')
                            ->getTitleFromRecordUsing(fn($record) => $record->category ? '#' . $record->category->sort . ' ' . $record->category->name : null)
                    ])
            )
            ->columns([
                TextColumn::make('title')
                    ->limit(80)
                    ->tooltip(fn($state) => strlen($state) > 80 ? $state : null),

                IconColumn::make('published')
                    ->alignCenter()
                    ->falseColor('gray')
                    ->label('Published'),

                IconColumn::make('private')
                    ->alignCenter()
                    ->trueColor('primary')
                    ->trueIcon('tabler-world-off')
                    ->falseColor('primary')
                    ->falseIcon('tabler-world')
                    ->tooltip(fn($state) => $state ? 'Private' : 'Public')
                    ->label('Access'),

                TextColumn::make('writer')
                    ->badge()
                    ->tooltip(function ($record) {
                        $revisions = $record->revisions;

                        if ($revisions->count() > 0 && $revisions->first()->author_name !== $record->writer_name)
                            return "Written by $record->writer_name & edited by {$record->editor_names->implode(', ')}";

                        return null;
                    })
                    ->getStateUsing(function ($record) {
                        $editorCount = count($record->editor_names);

                        if ($editorCount > 0) {
                            return [
                                $record->writer_name,
                                str('editor')->plural($editorCount)->prepend($editorCount . ' ')->toString(),
                            ];
                        }

                        return $record->writer_name;
                    })
                    ->label('Writer(s)')
                    ->toggleable(),

                TextColumn::make('children_count')
                    ->visible(!$isChildTable)
                    ->label('Sub-Contents')
                    ->alignCenter()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),

                TextColumn::make('ratings_score')
                    ->visible($ratingSystemEnabled)
                    ->sortable()
                    ->label('Rating')
                    ->alignCenter()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),

                TextColumn::make('size')
                    ->getStateUsing(function ($record) {
                        $jsonString = json_encode($record->schema);
                        $bytes = mb_strlen($jsonString, '8bit');
                        return Number::fileSize($bytes);
                    })->toggleable(),
            ])
            ->recordActions([
                EditAction::make()->url(fn($record) => ContentResource::getUrl('edit', [
                    'record' => $record->getKey()
                ])),

                ReplicateAction::make()->label('Copy')->mutateRecordDataUsing(function (array $data): array {
                    $data['title'] = $data['title'] . ' (copy)';

                    return $data;
                })->requiresConfirmation(),

                DeleteAction::make(),
            ]);
    }
}
