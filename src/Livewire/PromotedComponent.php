<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class PromotedComponent extends Component
{
    public static function block(): Block
    {
        return Block::make(static::class)
            ->icon('tabler-star')
            ->label(function ($state, $set) {

                if ($state['id'] ?? false) {
                    $component = \Cloudcake\Documents\Models\Component::select('id', 'name')->find($state['id']);

                    return str($component->name)
                        ->wrap('<span class="text-gray-500">', '</span>')
                        ->prepend('Component ')
                        ->toHtmlString();
                }

                if ($state['type'] ?? false)
                    return str($state['type'])
                        ->afterLast('\\')
                        ->kebab()
                        ->replace(['-', '_'], ' ')
                        ->apa()
                        ->prepend('<span class="text-gray-500">Component</span> ')
                        ->toHtmlString();

                return 'Promoted Component';
            })
            ->components(fn($get) => [
                Select::make('id')
                    ->inlineLabel()
                    ->searchable()
                    ->label('Component')
                    ->required()
                    ->options(\Cloudcake\Documents\Models\Component::select(['id', 'name'])->get()->pluck('name', 'id')),
            ]);
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(fn($state) => static::renderSchema($this->data));
    }

    public static function editorSchema(array $data): array
    {
        return [];
    }

    public static function renderSchema(array $data): array
    {
        $component = \Cloudcake\Documents\Models\Component::find($data['id']);

        /** @var ?Component $class */
        if ($component && class_exists($class = $component->type))
            return $class::renderSchema($component->schema ?? []);

        return [];
    }
}