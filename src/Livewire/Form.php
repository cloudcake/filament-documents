<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;

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
            Flex::make([
                IconPicker::make('icon')->grow(false),

                TextInput::make('name')->grow(),
            ]),

            Flex::make([
                TextInput::make('columns')->grow(false)->numeric()->default(1),
            ]),

            Select::make('mode')->grow(false)->options([
                'form' => 'Form',
                'modal' => 'Modal',
                'slideover' => 'Slide Over',
            ]),

            Group::make([
                Select::make('width')
                    ->default(Width::ExtraLarge)
                    //  ->required()
                    ->options(collect(Width::cases())->pluck('name', 'value')->flatMap(fn($value, $key) => [$key => str($value)->snake()->replace('_', ' ')->ucwords()]))
            ])->visible(fn($get) => $get('mode') === 'modal' || $get('mode') === 'slideover'),

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
                                'email' => 'Email',
                                'numeric' => 'Numeric',
                                'password' => 'Password',
                                'text' => 'Text',
                                'uuid' => 'UUID',
                                'url' => 'URL',
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
        $schema = [];

        $mode = $data['mode'] ?? 'form';

        foreach ($data['fields'] ?? [] as $field) {
            $label = $field['data']['name'] ?? '';
            $stateName = str($label)->lower()->snake()->toString();
            $columnSpan = $field['data']['column_span'] ?? 1;
            $default = $field['data']['default'] ?? null;

            $schema[] = match ($field['type'] ?? null) {
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
                    ->afterStateUpdated(function ($state) use ($mode, $stateName) {
                        if ($mode === 'form')
                            Document::setVar($stateName, $state);
                    })
                    ->live(debounce: 1000)
                    ->numeric($field['data']['type'] === 'numeric')
                    ->uuid($field['data']['type'] === 'uuid')
                    ->email($field['data']['type'] === 'email')
                    ->url($field['data']['type'] === 'url')
                    ->password($field['data']['type'] === 'password')
                    ->revealable($field['data']['type'] === 'password')
                    ->label($label)
                    ->columnSpan($columnSpan)
                    ->inlineLabel($field['data']['inline'] ?? false),

                'text' => Text::make(str($field['data']['text'])->toHtmlString())
                    ->columnSpan($columnSpan),

                default => null,
            };
        }


        if ($mode === 'modal' || $mode === 'slideover')
            return [
                Action::make($data['name'])
                    ->label($data['name'])
                    ->icon($data['icon'] ?? null)
                    ->modal()
                    ->modalIcon($data['icon'] ?? null)
                    ->modalWidth(Width::tryFrom($data['width'] ?? Width::ExtraLarge->value))
                    ->modalSubmitActionLabel('Save')
                    ->slideOver($mode === 'slideover')
                    ->action(function ($data) {
                        foreach ($data as $key => $value)
                            Document::setVar($key, $value);
                    })
                    ->schema($schema)
            ];

        return [
            Section::make($data['name'] ?? null)
                ->columns($data['columns'] ?? 1)
                ->icon($data['icon'] ?? null)
                ->columnSpanFull()
                ->schema($schema)
        ];
    }
}