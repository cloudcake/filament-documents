<?php

namespace Cloudcake\Documents\Resources\Contents\RelationshipManagers;

use Cloudcake\Documents\Resources\Contents\Schemas\ContentForm;
use Cloudcake\Documents\Resources\Contents\Tables\ContentsTable;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\CodeEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Phiki\Grammar\Grammar;
use Symfony\Component\Yaml\Yaml;

class RevisionRelationshipManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revisions';

    protected static string|BackedEnum|null $icon = 'tabler-git-cherry-pick';

    public function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['author', 'content']))
            ->columns([
                TextColumn::make('editor')
                    ->getStateUsing(fn($record) => $record->author_name),

                TextColumn::make('diff_size')
                    ->label('Diff Size')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $jsonString = json_encode($record->diff);
                        $bytes = mb_strlen($jsonString, '8bit');
                        return Number::fileSize($bytes);
                    }),

                TextColumn::make('created_at')
                    ->label('Edited')
                    ->alignRight()
                    ->dateTime()
            ])
            ->recordActions([
                Action::make('view_diff')
                    ->label('Diff')
                    ->icon('tabler-git-cherry-pick')
                    ->modal()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Dismiss')
                    ->schema([
                        Grid::make()->components([
                            CodeEntry::make('diff_before')
                                ->label('Before')
                                ->grammar(Grammar::Yaml)
                                ->getStateUsing(fn($record) => str(Yaml::dump($record->diff[0], 20, Yaml::DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES))->trim(PHP_EOL)),

                            CodeEntry::make('diff_after')
                                ->label('After')
                                ->grammar(Grammar::Yaml)
                                ->getStateUsing(fn($record) => str(Yaml::dump($record->diff[1], 20, Yaml::DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES))->trim(PHP_EOL)),
                        ])
                    ]),

//                Action::make('view_past_state')
//                    ->label('View Past State')
//                    ->disabled()
//                    ->tooltip("Feature coming soon")
//                    ->url(fn($record) => $record->content->url . '?revision=' . $record->id),
//
//                Action::make('restore_state')
//                    ->label('Restore State')
//                    ->disabled()
//                    ->tooltip("Feature coming soon")
//                    ->url(fn($record) => $record->content->url . '?revision=' . $record->id)
            ]);
    }
}