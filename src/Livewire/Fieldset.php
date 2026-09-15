<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;
use Stringable;

class Fieldset extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['title'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-square-dashed';
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            TextInput::make('title'),

            TextInput::make('columns')->integer()->minValue(1)->maxValue(12),

            Builder::make('content')
                ->blockNumbers(false)
                ->blockIcons()
                ->columnSpanFull()
                ->addActionAlignment(Alignment::Right)
                ->addActionLabel('Add Content')
                ->collapsible()
                ->collapsed(fn($operation) => $operation === 'edit')
                ->collapseAllAction(fn($action) => $action->visible(false))
                ->expandAllAction(fn($action) => $action->visible(false))
                ->addBetweenAction(fn($action) => $action->visible(false))
                ->blocks(fn() => Component::blocks()),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $content = collect($state['content'] ?? [])
            ->flatMap(function ($data) {
                $class = $data['type'] ?? null;

                if (class_exists($class))
                    return $class::renderView($data['data'], false);

                return null;
            })->filter()->all();

        return [
            \Filament\Schemas\Components\Fieldset::make($state['title'] ?? '')
                ->columns($state['columns'] ?? 1)
                ->columnSpanFull()
                ->schema($content)
        ];
    }
}