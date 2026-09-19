<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Alignment;
use Stringable;

class Wizard extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['name'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-wand';
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Repeater::make('steps')
                ->addActionAlignment(Alignment::Left)
                ->addActionLabel('Add Step')
                ->collapsible()
                ->collapseAllAction(fn($action) => $action->visible(false))
                ->expandAllAction(fn($action) => $action->visible(false))
                ->addBetweenAction(fn($action) => $action->visible(false))
    ->cloneable()
                ->itemLabel(fn($state) => $state['title'] ?? 'New Tab')
                ->schema([
                    Flex::make([
                        IconPicker::make('icon')->grow(false),

                        TextInput::make('title'),
                    ]),

                    TextInput::make('description'),

                    Builder::make('content')
                        ->blockNumbers(false)
                        ->blockIcons()
                        ->columnSpanFull()
                        ->addActionAlignment(Alignment::Right)
                        ->addActionLabel('Add Content')
                        ->collapsible()
                        ->collapsed()
                        ->collapseAllAction(fn($action) => $action->visible(false))
                        ->expandAllAction(fn($action) => $action->visible(false))
                        ->addBetweenAction(fn($action) => $action->visible(false))
                        ->blocks(fn() => Component::blocks()),
                ])
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $steps = collect($state['steps'] ?? [])
            ->map(
                fn($step) => Step::make($step['title'])
                    ->description($step['description'] ?? null)
                    ->icon($step['icon'])
                    ->afterValidation(function($data) {
                        Notification::make('test')
                            ->body(json_encode($data))
                            ->send();
                    })
                    ->schema(collect($step['content'] ?? [])
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
            \Filament\Schemas\Components\Wizard::make($steps),
        ];
    }
}