<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Documents\Blocks\HeroBlock;
use Cloudcake\Documents\Components\Component;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Schemas\Components\Html;
use Stringable;

class RichContent extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        if ($state['raw'] ?? false) {
            return str(RichContentRenderer::make($state['raw'])->toText())->limit(24, preserveWords: true);
        }

        return null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-html';
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            RichEditor::make('raw')
                ->toolbarButtons([
                    ['bold', 'italic', 'underline', 'strike', 'link'],
                    ['h1', 'h2', 'h3', 'h4'],
                    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                    ['table'],
                    ['undo', 'redo']
                ])
                ->hiddenLabel()
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        return [
            Html::make(RichContentRenderer::make($state['raw'])->toHtml())->toHtmlString(),
        ];
    }
}