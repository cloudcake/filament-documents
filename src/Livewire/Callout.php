<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\IconPicker\Forms\Components\IconPicker;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class Callout extends Component
{
    public static function block(): Block
    {
        return Block::make(static::class)
                    ->label(function ($state) {
                        if ($state['heading'] ?? false) {
                            return "Callout: {$state['heading']}";
                        }

                        return 'Callout';
                    })
                    ->icon('tabler-notification')
                    ->components(fn($state) => static::editorSchema($state));
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(fn($state) => static::renderSchema($this->data));
    }

    public static function editorSchema(array $data): array
    {
        return [
            IconPicker::make('icon'),
            TextInput::make('heading')->required(),
            RichEditor::make('description')->required(),
            Select::make('colour')->required()->options([
                'gray'    => 'Default',
                'primary' => 'Primary',
                'success' => 'Success',
                'danger'  => 'Danger',
                'warning' => 'Warning',
                'info'    => 'Informative',
            ]),
        ];
    }

    public static function renderSchema(array $data): array
    {

        return [
            \Filament\Schemas\Components\Callout::make()
                                                ->heading($data['heading'])
                                                ->description(new HtmlString(RichEditor\RichContentRenderer::make($data['description'])->toHtml()))
                                                ->color($data['colour'] ?? 'gray')
                                                ->icon($data['icon'] ?? null)
                                                ->dehydrated(false),
        ];
    }
}