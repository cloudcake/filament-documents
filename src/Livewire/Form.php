<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Components\Component;
use Cloudcake\Documents\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Support\Enums\Width;
use Stringable;

class Form extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['name'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-input-check';
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                IconPicker::make('icon')->grow(false),

                TextInput::make('name')->grow(),

                Select::make('mode')->live()->grow(false)->options([
                    'form' => 'Form',
                    'modal' => 'Modal',
                    'slideover' => 'Slide Over',
                ]),

                Group::make([
                    Select::make('width')
                        ->default(Width::ExtraLarge)
                        ->options(collect(Width::cases())->pluck('name', 'value')->flatMap(fn($value, $key) => [$key => str($value)->snake()->replace('_', ' ')->ucwords()]))
                ])->visible(fn($get) => $get('mode') === 'modal' || $get('mode') === 'slideover'),
            ]),

            static::BaseBuilder('fields')->addActionLabel('Add Field')
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $mode = $state['mode'] ?? 'form';

        $content = collect($state['fields'] ?? [])
            ->flatMap(function ($data) {
                $class = $data['type'] ?? null;

                if ($class && is_string($class) && class_exists($class))
                    return $class::renderView($data['data'] ?? [], false);

                return [];
            })->filter()->all();

        if ($mode === 'modal' || $mode === 'slideover')
            return [
                Action::make($state['name'])
                    ->label($state['name'])
                    ->icon($state['icon'] ?? null)
                    ->modal()
                    ->modalIcon($state['icon'] ?? null)
                    ->modalWidth(Width::tryFrom($state['width'] ?? Width::ExtraLarge->value))
                    ->modalSubmitActionLabel('Save')
                    ->slideOver($mode === 'slideover')
                    ->action(function ($data) {
                        foreach ($data as $key => $value)
                            Document::setVar($key, $value);
                    })
                    ->schema($content)
            ];

        return [
            Group::make($content)
                ->columns(1)
                ->columnSpanFull()
        ];
    }
}