<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class RichContent extends Component
{
    public static function block(): Block
    {
        return Block::make(static::class)
            ->label(function ($state) {

                if ($state['raw'] ?? false) {
                    return str(RichContentRenderer::make($state['raw'])->toText())->before(PHP_EOL)->limit(32, preserveWords: true);
                }

                return 'Rich Content';
            })
            ->icon('tabler-html')
            ->components(fn($state) => static::editorSchema($state));
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->extraAttributes(['class' => 'documentation'])
            ->components(fn($state) => static::renderSchema($this->data));
    }

    public static function editorSchema(array $data): array
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

    public static function renderSchema(array $data): array
    {
        return [
            Html::make(RichContentRenderer::make($data['raw'])->toHtml())->toHtmlString()
        ];
    }
}