<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Components\Component;
use Cloudcake\Documents\Document;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Text;
use Stringable;

class Input extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['label'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-cursor-text';
    }

    protected static function shouldPreview(): bool
    {
        return true;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                TextInput::make('label')->grow()->required(),
                Select::make('type')->grow()->options([
                    'email' => 'Email',
                    'numeric' => 'Numeric',
                    'password' => 'Password',
                    'text' => 'Text',
                    'uuid' => 'UUID',
                    'url' => 'URL',
                ]),
            ])->columnSpanFull(),

            \Filament\Schemas\Components\Section::make('Flags')
                ->collapsible()
                ->collapsed()
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    Checkbox::make('inline_label'),
                    Checkbox::make('store_in_environment')->live(),
                    Checkbox::make('has_tooltip')->live(),
                ]),

            \Filament\Schemas\Components\Section::make('Environment Context')
                ->visible(fn($get) => $get('store_in_environment'))
                ->collapsible()
                ->collapsed()
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('store_as')->grow(false)->required()->default(1),
                ]),

            \Filament\Schemas\Components\Section::make('Tooltip')
                ->visible(fn($get) => $get('has_tooltip'))
                ->collapsible()
                ->collapsed()
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('tooltip')->grow(false)->numeric()->default(1),
                    IconPicker::make('tooltip_icon'),
                ]),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $label = $state['label'] ?? '';
        $stateName = str($label)->lower()->snake()->toString();
        $default = $state['default'] ?? '';
        $type = $state['type'] ?? null;

        return [
            TextInput::make($stateName)
                ->placeholder("Enter $label")
                ->afterLabel([
                    Text::make(str("$stateName")->toHtmlString())
                        ->fontFamily('mono')
                        ->icon('tabler-variable')
                        ->tooltip("This form field stores its value as a variable named $stateName.")
                        ->color('primary')
                        ->badge()
                ])
                ->when(
                    !blank($default),
                    fn($component) => $component->afterStateHydrated(fn($component) => $component->state(Document::getVar($stateName, $default)))
                )
                ->afterStateUpdated(function ($state) use ($stateName) {
                    Document::setVar($stateName, $state);
                })
                ->live(debounce: 1000)
                ->numeric($type === 'numeric')
                ->uuid($type === 'uuid')
                ->email($type === 'email')
                ->url($type === 'url')
                ->password($type === 'password')
                ->revealable($type === 'password')
                ->label($label)
                ->required()
                ->inlineLabel($state['inline'] ?? false)
        ];
    }
}