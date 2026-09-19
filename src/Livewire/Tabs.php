<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Alignment;
use Stringable;

class Tabs extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['name'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-tabs';
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Select::make('direction')
                ->options([
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical',
                ]),

            Repeater::make('tabs')
                ->addActionAlignment(Alignment::Left)
                ->addActionLabel('Add Tab')
                ->collapsible()
                ->collapsed(fn($operation) => $operation === 'edit')
                ->collapseAllAction(fn($action) => $action->visible(false))
                ->expandAllAction(fn($action) => $action->visible(false))
                ->addBetweenAction(fn($action) => $action->visible(false))
                ->itemLabel(fn($state) => $state['title'] ?? 'New Tab')
                ->schema([
                    Flex::make([
                        IconPicker::make('icon')->grow(false),

                        TextInput::make('title'),
                    ]),

                    Builder::make('content')
                        ->cloneable()
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
                ])
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $tabs = collect($state['tabs'] ?? [])
            ->map(
                fn($tab) => Tab::make($tab['title'])
                    ->icon($tab['icon'])
                    ->schema( collect($tab['content'] ?? [])
                        ->flatMap(function ($data) {
                            $class = $data['type'] ?? null;

                            if (class_exists($class))
                                return $class::renderView($data['data'], false);

                            return [];
                        })
                        ->all())
            )
            ->toArray();

        return [
            \Filament\Schemas\Components\Tabs::make('tabs')
                ->vertical(($state['direction'] ?? 'horizontal') === 'vertical')
                ->tabs($tabs)
        ];
    }
}