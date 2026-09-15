<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Illuminate\Support\HtmlString;
use Phiki\Grammar\Grammar;
use Stringable;

class Code extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        if ($state['lang'] ?? false)
            return str($state['lang'])->ucwords();

        return null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-code';
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Flex::make([
                Select::make('lang')
                    ->label('Syntax Highlighting')
                    ->inlineLabel()
                    ->searchable()
                    ->live()
                    ->options(Grammar::class)
                    ->grow(false)
                    ->required(),
            ]),

            CodeEditor::make('code')
                ->language(fn($get) => $get('lang') ? Language::tryFrom($get('lang')->value) : null)
                ->required(),
        ];
    }

    protected static function shouldPreview(): bool
    {
        return false;
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $code = $state['code'] ?? '';
        $lang = $state['lang'] ?? Language::Json->value;

        return [
            CodeEditor::make('code')
                ->hiddenLabel()
                ->afterStateHydrated(fn($component) => $component->state($code))
                ->language(Language::tryFrom($lang))
                ->disabled()
        ];
    }
}