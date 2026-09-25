<?php

namespace Cloudcake\Documents\Components;

use Cloudcake\Components\Forms\Components\IconPicker;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\CodeEntry;
use Filament\Panel;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Phiki\Grammar\Grammar;
use Stringable;

class ActionComponent extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['label'] ?? null;
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
                IconPicker::make('icon')->grow(false),
                TextInput::make('label')->required(),
                Select::make('display_mode')
                    ->grow(false)
                    ->required()
                    ->live()
                    ->options([
                        'button' => 'Button',
                        'link' => 'Link',
                    ]),

                Select::make('action_type')
                    ->visible(fn($get) => $get('display_mode') === 'link' || $get('display_mode') === 'button')
                    ->required()
                    ->live()
                    ->options([
                        'app_page_url' => 'Application Page Link',
                        'external_url' => 'External URL',
                        'custom' => 'Custom',
                    ]),
            ]),

            TextInput::make('url')
                ->label('URL')
                ->required()
                ->url()
                ->visible(fn($get) => $get('action_type') === 'external_url'),

            Flex::make([
                Select::make('panel')
                    ->live()
                    ->searchable()
                    ->required()
                    ->searchable()
                    ->options(fn() => collect(filament()->getPanels())->mapWithKeys(fn(Panel $panel) => [$panel->getId() => str($panel->getId())->snake()->replace('_', ' ')->ucwords()]))
                    ->afterStateUpdated(fn($set) => $set('resource', null)),

                Select::make('page')
                    ->live()
                    ->searchable()
                    ->options(function ($get) {
                        $panel = filament()->getPanel($get('panel'));

                        if (!$panel)
                            return [];

                        return collect([
                            ...$panel->getPages(),
                            ...$panel->getResources()
                        ])->flatMap(fn($v, $k) => [$k => str($v)->afterLast('\\')->append(' ')->append(str($v)->replace('\\', ' -> ')->wrap('(', ')'))]);
                    }),

            ])->visible(fn($get) => $get('action_type') === 'app_page_url'),

            Group::make([
                CodeEntry::make('signature_header')
                    ->extraAttributes(['class' => 'code-merge-head'])
                    ->hiddenLabel()
                    ->default('  function(array $arguments, array $data, Component $component) {')
                    ->grammar(Grammar::Php),

                CodeEditor::make('code')
                    ->hiddenLabel()
                    ->default('// Write your action logic here.')
                    ->autofocus()
                    ->language(CodeEditor\Enums\Language::Cpp),

                CodeEntry::make('signature_footer')
                    ->afterContent([
                        Flex::make([
                            \Filament\Actions\Action::make('cheatsheet')
                                ->slideOver()
                                ->modalHeading("Actions Cheatsheet")
                                ->modalDescription("One of the downsides of editing code without the help of an IDE is that you should be proficient in namespacing and remembering certain namespaces, but let's be real, we all forget namespaces with the hundreds of thousands we go through. This page serves as a dictionary of sorts (AKA cheatsheet) to assist in writing your action.")
                                ->modalIcon('tabler-script')
                                ->schema([
                                    CodeEntry::make('sending_notifications')
                                        ->grammar(Grammar::Cpp)
                                        ->default("\Filament\Notifications\Notification::make('test')
                        ->title('test')
                        ->send();"),
                                ])
                                ->link(),

                            \Filament\Actions\Action::make('execute')
                                ->action(fn(array $arguments, array $data, \Filament\Schemas\Components\Component $component, $get) => static::runAction($arguments, $data, $component, $get('../code')))
                                ->link(),
                        ])
                    ])
                    ->extraAttributes(['class' => 'code-merge-foot'])
                    ->hiddenLabel()
                    ->default('  }')
                    ->grammar(Grammar::Php),

            ])->extraAttributes(['class' => 'code-merge'])->visible(fn($get) => $get('action_type') === 'custom'),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        return [
            Action::make('action')
                ->icon($state['icon'] ?? null)
                ->label($state['label'] ?? 'Button')
                ->action(fn(array $arguments, array $data, \Filament\Schemas\Components\Component $component) => static::runAction($arguments, $data, $component, $state['code'] ?? ''))
        ];
    }

    protected static function runAction(array $arguments, array $data, \Filament\Schemas\Components\Component $component, string $code): void
    {
        $genericError = \Filament\Notifications\Notification::make('generic_error')
            ->title('Something went wrong')
            ->body("We couldn't process the action you requested, please try again later.")
            ->icon('tabler-exclamation-circle')
            ->color('danger')
            ->iconColor('danger');

        try {
            eval($code);
        } catch (\Exception|\Error $e) {
            // Don't do anything as to prevent displaying debug messages to users
            $genericError->send();
        }
    }
}