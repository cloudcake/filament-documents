<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Components\Component;
use Filament\Actions\Action as FilamentAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\CodeEntry;
use Filament\Panel;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Phiki\Grammar\Grammar;
use Stringable;

class Action extends Component
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
                    ->required()
                    ->live()
                    ->options([
                        'app_page_url' => 'Application Page Link',
                        'external_url' => 'External URL',
                        'custom' => 'Custom',
                    ]),
            ])->visible(fn($get) => $get('display_mode') === 'link' || $get('display_mode') === 'button'),

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
            FilamentAction::make('action')
                ->icon($state['icon'] ?? null)
                ->label($state['label'] ?? 'Button')
                ->action(fn(array $arguments, array $data, \Filament\Schemas\Components\Component $component) => static::runAction($arguments, $data, $component, $state['code'] ?? ''))
        ];
    }

    protected static function runAction(array $arguments, array $data, \Filament\Schemas\Components\Component $component, string $code): void
    {
        $highRiskFunctions = [
            // Process / shell execution
            'exec',
            'system',
            'shell_exec',
            'passthru',
            'proc_open',
            'popen',
            'pcntl_exec',
            'pcntl_fork',
            'pcntl_signal',
            'pcntl_alarm',
            'proc_nice',
            'proc_terminate',
            'proc_get_status',
            'proc_close',

            // Code evaluation / dynamic inclusion
            'eval',
            'assert',
            'create_function',
            'preg_replace',          // especially dangerous with /e modifier
            'include',
            'include_once',
            'require',
            'require_once',
            'call_user_func',
            'call_user_func_array',
            'forward_static_call',
            'forward_static_call_array',
            'extract',
            'compact',
            'parse_str',

            // File system (write / delete / permission)
            'file_put_contents',
            'file_get_contents',
            'fwrite',
            'fputs',
            'unlink',
            'rmdir',
            'mkdir',
            'rename',
            'copy',
            'move_uploaded_file',
            'chmod',
            'chown',
            'chgrp',
            'touch',
            'link',
            'symlink',
            'tempnam',
            'tmpfile',
            'fopen',
            'fclose',
            'glob',
            'scandir',
            'opendir',
            'readdir',

            // Network / sockets
            'fsockopen',
            'pfsockopen',
            'stream_socket_client',
            'stream_socket_server',
            'curl_exec',
            'curl_multi_exec',
            'mail',
            'mb_send_mail',

            // Dangerous info / config
            'phpinfo',
            'ini_set',
            'ini_alter',
            'ini_restore',
            'putenv',
            'dl',
            'getenv',
            'apache_setenv',

            // Serialization
            'unserialize',
            'serialize',

            // Other high-risk
            'header',
            'setcookie',
            'setrawcookie',
            'error_reporting',
            'register_shutdown_function',
            'register_tick_function',

            // Laravel-specific / framework helpers & facades
            'dd',
            'dump',
            'ray',
            'var_dump',
            'print_r',
            'var_export',

            // Laravel facades / static calls (string form for simple blacklisting)
            'DB::',
            'DB::statement',
            'DB::unprepared',
            'DB::raw',
            'Schema::',
            'Artisan::',
            'Artisan::call',
            'Artisan::queue',
            'Storage::',
            'File::',
            'Http::',
            'Mail::',
            'Notification::',
            'Queue::',
            'Cache::',
            'Config::',
            'Config::set',
            'Env::',
            'Log::',
            'Process::',
            'Route::',
            'View::',
            'Blade::',
            'Event::',
            'Gate::',
            'Auth::',
            'App::',

            // Common Laravel class prefixes (for partial matching)
            'Illuminate\\Support\\Facades\\DB',
            'Illuminate\\Support\\Facades\\Artisan',
            'Illuminate\\Support\\Facades\\Schema',
            'Illuminate\\Support\\Facades\\File',
            'Illuminate\\Support\\Facades\\Storage',
            'Illuminate\\Support\\Facades\\Http',
            'Illuminate\\Support\\Facades\\Mail',
            'Illuminate\\Support\\Facades\\Notification',
            'Illuminate\\Support\\Facades\\Queue',
            'Illuminate\\Support\\Facades\\Cache',
            'Illuminate\\Support\\Facades\\Config',
            'Illuminate\\Support\\Facades\\Env',
            'Illuminate\\Support\\Facades\\Log',
            'Illuminate\\Support\\Facades\\Process',
            'Illuminate\\Filesystem\\',
            'Symfony\\Component\\Process\\Process',
        ];

        $genericError = \Filament\Notifications\Notification::make('generic_error')
            ->title('Something went wrong')
            ->body("We couldn't process the action you requested, please try again later.")
            ->icon('tabler-exclamation-circle')
            ->color('danger')
            ->iconColor('danger');

        if (str($code)->contains($highRiskFunctions)) {
            $genericError->send();
            return;
        }

        try {
            eval($code);
        } catch (\Exception|\Error) {
            // Don't do anything as to prevent displaying debug messages to users

            $genericError->send();
        }
    }
}