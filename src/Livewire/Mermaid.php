<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Enums\MermaidTheme;
use Cloudcake\Components\Forms\Components\MermaidInput;
use Cloudcake\Components\Infolists\Components\MermaidEntry;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class Mermaid extends Component
{
    public static function block(): Block
    {
        return Block::make(static::class)
            ->label(function ($state) {
                if ($state['title'] ?? false) {
                    return $state['title'];
                }

                return 'Mermaid Diagram';
            })
            ->icon('tabler-graph')
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
            TextInput::make('title')
                ->label('Title')
                ->inlineLabel()
                ->grow()
                ->required(),

            Toggle::make('allow_panning'),

            Toggle::make('allow_zooming'),

//            Select::make('theme')
//                ->live()
//                ->options(MermaidTheme::class),
//
//            Select::make('dark_theme')
//                ->live()
//                ->options(MermaidTheme::class),

            MermaidInput::make('data')
                ->theme(MermaidTheme::Redux)
                ->darkTheme(MermaidTheme::ReduxDarkColor)
                ->rows(15)
                ->placeholder("graph TD\n    A[Start] --> B{Condition?}\n    B -->|Yes| C[Process A]\n    B -->|No| D[Process B]"),
        ];
    }

    public static function renderSchema(array $data): array
    {
        $components = [];

        if ($data['title'] ?? false)
            $components[] = Text::make($data['title'])->icon('tabler-graph')->badge();

        $components[] = MermaidEntry::make('data')
            ->theme($data['theme'] ?? null)
            ->theme(MermaidTheme::Redux)
            ->darkTheme(MermaidTheme::ReduxDarkColor)
            ->panning($data['allow_panning'] ?? false)
            ->zooming($data['allow_zooming'] ?? false)
            ->afterStateHydrated(fn($component) => $component->state($data['data']));

        return $components;
    }
}