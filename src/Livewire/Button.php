<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Stringable;

class Button extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['title'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-app-window-bottom';
    }

    protected static function shouldPreview(): bool
    {
        return true;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                IconPicker::make('icon')->live()->grow(false),
                TextInput::make('title')->live()->required(),
            ]),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        return [
            Action::make('button-' . str()->uuid())
                ->icon($state['icon'] ?? null)
                ->label($state['title'] ?? 'Button')
        ];
    }
}