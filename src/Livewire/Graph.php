<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Enums\MermaidTheme;
use Cloudcake\Components\Forms\Components\MermaidInput;
use Cloudcake\Components\Infolists\Components\MermaidEntry;
use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Stringable;

class Graph extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['name'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-graph';
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                TextInput::make('name')
                    ->inlineLabel()
                    ->grow()
                    ->required(),

                Select::make('type')
                    ->grow(false)
                    ->inlineLabel()
                    ->options([
                        'bar' => 'Bar Chart',
                        'line' => 'Line Chart',
                        'mermaid' => 'Mermaid Graph',
                    ]),
            ]),

            MermaidInput::make('data')
                ->theme(MermaidTheme::Redux)
                ->darkTheme(MermaidTheme::ReduxDarkColor)
                ->rows(15),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        return [
            MermaidEntry::make('data')
                ->theme($state['theme'] ?? null)
                ->theme(MermaidTheme::Redux)
                ->darkTheme(MermaidTheme::ReduxDarkColor)
                ->autosize()
                ->panning()
                ->zooming()
                ->afterStateHydrated(fn($component) => $component->state($state['data']))
        ];
    }
}