<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\CodeEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Html;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Phiki\Grammar\Grammar;
use Stringable;

class CustomHtml extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        return null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-code';
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            CodeEditor::make('content')
                ->language(Language::Html)
                ->columnSpanFull()
                ->required(),
        ];
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getRenderSchema(array|null $state): array
    {
        return [
            Html::make(fn($get) => Blade::render($state['content'] ?? '', ['data' => json_encode($get('.'))]))->columnSpanFull()
        ];
    }
}