<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Documents\Document;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class Form extends Component
{
    public static function block(): Block
    {
        return Block::make(static::class)
                    ->label(function ($state) {
                        if ($state['name'] ?? false)
                            return "Form: {$state['name']}";

                        return 'Form';
                    })
                    ->icon('tabler-input-check')
                    ->columns(12)
                    ->components(fn($state) => static::editorSchema($state));
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(fn() => static::renderSchema($this->data));
    }

    public static function editorSchema(array $data): array
    {
        return [
            TextInput::make('columns')->columnSpan(1)->numeric()->default(1),

            TextInput::make('name')->columnSpan(12),

            Select::make('mode')->options([
                'form'      => 'Form',
                'modal'     => 'Modal',
                'slideover' => 'Slide Over',
            ]),

            Builder::make('fields')
                   ->columnSpanFull()
                   ->addActionAlignment(Alignment::Left)
                   ->addActionLabel('Add Field')
                   ->collapsible()
                   ->collapsed(fn($operation) => $operation === 'edit')
                   ->blocks([
                       Block::make('input')
                            ->label(function ($state) {
                                if ($state['name'] ?? false)
                                    return "Input: {$state['name']}";

                                return 'Input';
                            })
                            ->schema([
                                TextInput::make('column_span')->columnSpan(1)->numeric()->default(1),
                                TextInput::make('name'),
                                TextInput::make('default'),
                                Select::make('type')->options([
                                    'email'    => 'Email',
                                    'numeric'  => 'Numeric',
                                    'password' => 'Password',
                                    'text'     => 'Text',
                                    'uuid'     => 'UUID',
                                    'url'      => 'URL',
                                ]),
                                Checkbox::make('inline'),
                            ]),

                       Block::make('text')
                            ->label(function ($state) {
                                if ($state['content'] ?? false) {
                                    return str($state['content'])->limit(48)->prepend('Text: ')->stripTags()->toString();
                                }

                                return 'Text';
                            })
                            ->schema([
                                TextInput::make('column_span')->columnSpan(1)->numeric()->default(1),
                                RichEditor::make('text')->hiddenLabel(),
                            ]),
                   ])
        ];
    }

    public static function renderSchema(array $data): array
    {
        return [
            Section::make()
                   ->columns($data['columns'] ?? 1)
                   ->columnSpanFull()
                   ->schema(function () use ($data) {
                       $components = [];

                       foreach ($data['fields'] ?? [] as $field) {
                           $label = $field['data']['name'] ?? '';
                           $stateName = str($label)->lower()->snake()->toString();
                           $columnSpan = $field['data']['column_span'] ?? 1;
                           $default = $field['data']['default'] ?? null;

                           $component = match ($field['type'] ?? null) {
                               'input' => TextInput::make($stateName)
                                                   ->placeholder("Enter $label")
                                                   ->belowContent([
                                                       Text::make(str("<code>$stateName</code>")->toHtmlString())
                                                           ->icon('tabler-variable')
                                                           ->tooltip("This form field stores its value as a variable named $stateName.")
                                                           ->color('primary')
                                                           ->badge()
                                                   ])
                                                   ->afterStateHydrated(fn($component) => $component->state(Document::getVar($stateName, $default)))
                                                   ->live(debounce: 1000)
                                                   ->afterStateUpdated(fn($state) => Document::setVar($stateName, $state))
                                                   ->numeric($field['data']['type'] === 'numeric')
                                                   ->uuid($field['data']['type'] === 'uuid')
                                                   ->email($field['data']['type'] === 'email')
                                                   ->url($field['data']['type'] === 'url')
                                                   ->password($field['data']['type'] === 'password')
                                                   ->revealable($field['data']['type'] === 'password')
                                                   ->label($label)
                                                   ->columnSpan($columnSpan)
                                                   ->inlineLabel($field['data']['inline'] ?? false),

                               'text'  => Text::make(str($field['data']['text'])->toHtmlString())
                                              ->columnSpan($columnSpan),

                               default => null,
                           };

                           if ($component)
                               $components[] = $component;
                       }

                       return $components;
                   })
        ];
    }
}