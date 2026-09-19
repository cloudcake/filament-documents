<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Html;
use Stringable;

class Text extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        if ($state['text'] ?? false) {
            return str(RichContentRenderer::make($state['text'])->toText())->limit(24, preserveWords: true);
        }

        return null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-cursor-text';
    }

    protected static function shouldPreview(): bool
    {
        return true;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            TextInput::make('span')->columnSpan(1)->numeric()->default(1),
            RichEditor::make('text')->hiddenLabel(),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $text = $state['text'] ?? '';
        $columnSpan = $state['span'] ?? 1;

        return [
            Html::make(RichContentRenderer::make($text)->toHtml())->columnSpan($columnSpan)->toHtmlString(),
        ];
    }
}