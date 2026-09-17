<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Html;
use Stringable;

class Heading extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['title'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-heading';
    }

    protected static function shouldPreview(): bool
    {
        return true;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                IconPicker::make('icon')->live()->grow(false),
                TextInput::make('title')->live()->required(),
            ]),

            Checkbox::make('subheading'),

            RichEditor::make('description')
                ->columnSpanFull()
                ->toolbarButtons([
                    ['bold', 'italic', 'underline', 'strike', 'link'],
                    ['h1', 'h2', 'h3', 'h4'],
                    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                    ['table'],
                    ['undo', 'redo']
                ])
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $title = $state['title'] ?? 'Untitled';
        $description = Html::make(RichContentRenderer::make($state['description'] ?? '')->toHtml())->toHtmlString();
        $subheading = $state['subheading'] ?? false;

        return $subheading ? [
            Html::make(<<<HTML
            <header class="fi-header">
                <h1 class="fi-header-heading-subheading">
                    $title
                </h1>
            </header>
            <div class="mt-3 text-gray-500">$description</div>
            HTML)
        ] : [
            Html::make(<<<HTML
            <header class="fi-header">
                <h1 class="fi-header-heading">
                    $title
                </h1>
            </header>
            <div class="mt-3 text-gray-500">$description</div>
            HTML)
        ];
    }
}