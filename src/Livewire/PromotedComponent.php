<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\Select;
use Stringable;

class PromotedComponent extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        if ($state['id'] ?? false) {
            $component = \Cloudcake\Documents\Models\Component::select('id', 'name')->find($state['id']);

            return $component->name;
        }

        if ($state['type'] ?? false)
            return str($state['type'])
                ->afterLast('\\')
                ->snake()
                ->replace('_', ' ')
                ->ucwords()
                ->toString();

        return null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
       return 'tabler-star';
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Select::make('id')
                ->live()
                ->inlineLabel()
                ->searchable()
                ->label('Component')
                ->required()
                ->options(\Cloudcake\Documents\Models\Component::select(['id', 'name'])->get()->pluck('name', 'id')),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        if (!($state['id'] ?? false))
            return [];

        $component = \Cloudcake\Documents\Models\Component::find($state['id']);

        /** @var ?Component $class */
        if ($component && class_exists($class = $component->type))
            return $class::renderView($component->schema ?? [], false);

        return [];
    }
}