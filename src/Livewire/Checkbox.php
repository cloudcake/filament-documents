<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Stringable;

class Checkbox extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['label'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-check';
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            TextInput::make('label')->required(),
            Toggle::make('checked_by_default')->default($state['checked'] ?? false),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        return [
            \Filament\Forms\Components\Checkbox::make('checked-'.str()->uuid())
                ->label($state['label'])
                ->default($state['checked'] ?? false),
        ];
    }
}