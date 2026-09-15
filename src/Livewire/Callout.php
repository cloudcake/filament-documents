<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Components\Forms\Components\IconPicker;
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
                    ->grow(false)
                    ->live(),

                TextInput::make('heading')
                    ->live()
                    ->required(),

                Select::make('colour')
                    ->live()
                    ->required()
                    ->options(collect(Color::all())->flatMap(fn($v, $k) => [$k => str($k)->ucfirst()])->sort())
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

        if ($colour) {
            $colour = Color::all()[$colour] ?? Color::Neutral;
        } else
            $colour = Color::Neutral;

        if ($description)
            $description = new HtmlString(RichEditor\RichContentRenderer::make($description)->toHtml());

        return [
            \Filament\Schemas\Components\Callout::make()
                ->heading($heading)
                ->description($description)
                ->color($colour)
                ->icon($icon)
                ->iconColor($colour)
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