<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Livewire\Component as LivewireComponent;
use Stringable;

/**
 * @property Schema $schema
 */
abstract class Component extends LivewireComponent implements HasSchemas, HasActions
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public const array BLOCKS_CLASSES = [
        Button::class,
        Callout::class,
        Code::class,
        Fieldset::class,
        Form::class,
        Heading::class,
        HttpRequest::class,
        Layout::class,
        Mermaid::class,
        PromotedComponent::class,
        RichContent::class,
        Section::class,
        Tabs::class,
        Wizard::class,
    ];

    public function render(): View
    {
        return view('cloudcake-documents::layouts.schema');
    }

    public function mount(array $data = []): void
    {
        $this->data = $data;
        $this->schema->fill($data);
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->extraAttributes(['class' => 'documentation'])
            ->columns($this->data['columns'] ?? 1)
            ->components(fn($state) => static::renderView($this->data, false));
    }

    protected abstract static function getLabel(array|null $state = []): string|null;

    protected abstract static function getIcon(array|null $state = []): string|Stringable|null;

    protected abstract static function getEditorSchema(array|null $state): array;

    protected abstract static function getRenderSchema(array|null $state): array;

    protected static function shouldPreview(): bool
    {
        return true;
    }

    private static function getData(array|null $state = [], bool $isState = true): array
    {
        if (!$isState)
            return $state ?? [];

        return collect($state)->firstWhere('type', static::class)['data'] ?? [];
    }

    private static function label(array|null $state = []): string|HtmlString
    {
        $displayLabel = static::getLabel($state);
        $componentLabel = str(basename(static::class))
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->ucwords();

        if (!$displayLabel)
            return $componentLabel;

        return str(static::getLabel($state) ?? '')
            ->prepend(
                $componentLabel
                    ->wrap('<span class="text-gray-500">', '</span>')
                    ->append(' ')
            )
            ->toHtmlString();
    }

    public static function block(array $additionalSchema = []): Block
    {
        return Block::make(static::class)
            ->label(fn($state) => static::label($state))
            ->icon(fn($state) => static::getIcon($state))
            ->components(fn($state) => [
                ...$additionalSchema,
                ...static::editorSchema(static::getData($state)),
            ]);
    }

    public static function renderPreview(array $state): array
    {
        return [
            Group::make(static::renderView($state, false))
                ->extraAttributes(['class' => 'documentation'])
                ->columns(1)
                ->columnSpanFull()
        ];
    }

    public static function renderView(array $state, bool $isState = true): array
    {
        $data = static::getData($state, $isState);

        return [
            Group::make(static::getRenderSchema($data))
                ->columns($data['columns'] ?? 1)
                ->columnSpan($data['span'] ?? 'full')
                ->grow($data['grow'] ?? true)
        ];
    }

    public static function editorSchema(array|null $state = null): array
    {
        return [
            ...static::getEditorSchema(static::getData($state))
        ];
    }

    public static function blocks(array $additionalSchema = []): array
    {
        return collect(static::BLOCKS_CLASSES)
            ->map(fn($class) => $class::block($additionalSchema))->toArray();
    }
}