<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;
use Stringable;

class Layout extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return !blank($state['type'] ?? null) ? str($state['type'])->ucwords() : null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-layout';
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                Select::make('type')
                    ->live()
                    ->inlineLabel()
                    ->grow(false)
                    ->options([
                        'grid' => 'Grid',
                        'flex' => 'Flex',
                    ]),

                TextInput::make('columns')
                    ->inlineLabel()
                    ->grow(false)
                    ->integer()
                    ->minValue(1)
                    ->visible(fn($get) => $get('type') === 'grid')
                    ->required(),
            ]),

            Builder::make('content')
                ->blockPreviews(areInteractive: true)
                ->blockNumbers(false)
                ->blockIcons()
                ->columnSpanFull()
                ->cloneable()
                ->addActionAlignment(Alignment::Right)
                ->addActionLabel('Add Content')
                ->collapsible()
                ->collapseAllAction(fn($action) => $action->visible(false))
                ->expandAllAction(fn($action) => $action->visible(false))
                ->addBetweenAction(fn($action) => $action->visible(false))
                ->blocks(function ($get) {
                    $extraSchema = [];
                    $type = $get('type') ?? 'flex';

                    if ($type === 'grid')
                        $extraSchema[] =
                            TextInput::make('span')

                                ->integer()
                                ->minValue(1)
                                ->maxValue(fn() => $get('columns'))
                                ->label('Column Span')
                                ->inlineLabel()
                                ->grow(false);
                    else
                        $extraSchema[] =
                            Checkbox::make('grow')
                                ->grow(false)
                                ->inlineLabel();

                    return Component::blocks($extraSchema);
                }),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $schema = [];

        $type = $state['type'] ?? 'grid';
        $content = collect($state['content'] ?? [])
            ->flatMap(function ($data) {
                $class = $data['type'] ?? null;

                if (class_exists($class))
                    return $class::renderView($data['data'], false);

                return null;
            })->filter()->all();

        if ($type === 'grid')
            $schema = $content;
        else
            $schema[] = Flex::make($content);

        return $schema;
    }
}