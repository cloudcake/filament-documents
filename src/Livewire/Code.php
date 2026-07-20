<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\CodeEntry;
use Filament\Schemas\Schema;
use Phiki\Grammar\Grammar;

class Code extends Component
{
    public static function block(): Block
    {
        return Block::make(static::class)
            ->label(function ($state) {
                if ($state['code'] ?? false) {
                    return strtoupper($state['lang']) . ' Code';
                }

                return 'Code';
            })
            ->icon('tabler-code')
            ->components(fn($state) => static::editorSchema($state));
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(static::renderSchema($this->data));
    }

    public static function editorSchema(array $data): array
    {
        return [
            Select::make('lang')
                ->label('Syntax Highlighting')
                ->searchable()
                ->live()
                ->options(Grammar::class)
                ->required(),

            CodeEditor::make('code')
                ->language(fn($get) => $get('lang') ? Language::tryFrom($get('lang')->value) : null)
                ->required(),
        ];
    }

    public static function renderSchema(array $data): array
    {
        return [
            CodeEditor::make('code')
                ->hiddenLabel()
                ->afterStateHydrated(fn($component) => $component->state($data['code']))
                ->language(Language::tryFrom($data['lang'] ?? ''))
                ->disabled()
        ];
    }
}