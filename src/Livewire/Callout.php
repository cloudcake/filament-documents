<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Enums\SafeColours;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Stringable;

class Callout extends Component
{
    protected static function getEditorSchema(array|null $state = []): array
    {
        return [
            Flex::make([
                IconPicker::make('icon')
                    ->inlineLabel()
                    ->grow(false)
                    ->live(),

                TextInput::make('heading')
                    ->inlineLabel()
                    ->live()
                    ->required(),

                Select::make('colour')
                    ->inlineLabel()
                    ->live()
                    ->required()
                    ->options(SafeColours::class)
                    ->searchable()
                    ->grow(false),
            ])->columns(3),

            RichEditor::make('description')
                ->live()
                ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'])
                ->required(),
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $heading = $state['heading'] ?? 'Untitled';
        $description = $state['description'] ?? 'No description';
        $colour = $state['colour'] ?? null;
        $icon = $state['icon'] ?? null;

        if ($description) {
            $html = new HtmlString(RichEditor\RichContentRenderer::make($description)->toHtml());

            // Callout doesn't support paragraphs since the wrapper in the callout description is already a paragraph.
            $description = str($html)->remove('<p>')->remove('</p>')->toHtmlString();
        }

        return [
            \Filament\Schemas\Components\Callout::make()
                ->heading($heading)
                ->description($description)
                ->color($colour)
//                ->actions([
//                    Action::make('test')->link()->icon('tabler-notification'),
//                ])
                ->icon($icon)
                ->dehydrated(false),
        ];
    }

    protected static function getLabel(array|null $state = []): string|null
    {
        return $state['heading'] ?? null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-notification';
    }
}