<?php

namespace Cloudcake\Documents\Components;

use Cloudcake\Documents\Livewire\Action;
use Cloudcake\Documents\Livewire\Button;
use Cloudcake\Documents\Livewire\Callout;
use Cloudcake\Documents\Livewire\Checkbox;
use Cloudcake\Documents\Livewire\Code;
use Cloudcake\Documents\Livewire\CustomHtml;
use Cloudcake\Documents\Livewire\Fieldset;
use Cloudcake\Documents\Livewire\Form;
use Cloudcake\Documents\Livewire\Graph;
use Cloudcake\Documents\Livewire\Heading;
use Cloudcake\Documents\Livewire\HttpRequest;
use Cloudcake\Documents\Livewire\Input;
use Cloudcake\Documents\Livewire\Layout;
use Cloudcake\Documents\Livewire\PromotedComponent;
use Cloudcake\Documents\Livewire\RichContent;
use Cloudcake\Documents\Livewire\Section;
use Cloudcake\Documents\Livewire\Tabs;
use Cloudcake\Documents\Livewire\Text;
use Cloudcake\Documents\Livewire\Wizard;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text as FilamentText;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use JetBrains\PhpStorm\NoReturn;
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
        // Temporary:
        Heading::class,

        // Keepers
        Action::class,
        Button::class,
        Callout::class,
        Checkbox::class,
        Code::class,
        CustomHtml::class,
        Fieldset::class,
        Form::class,
        HeadingComponent::class,
        HttpRequest::class,
        Input::class,
        Layout::class,
        Graph::class,
        PromotedComponent::class,
        RichContent::class,
        Section::class,
        Tabs::class,
        Text::class,
        Wizard::class,
    ];

    public static function getSchemaFromData($data): array|null
    {
        $class = $data['type'] ?? null;

        // @TODO: Remove this once refactoring is done
        if (!class_exists($class)) {
            $class = str(Component::class)->beforeLast('\\')->append($class . 'Component');

            dd($class);
        }

        if (class_exists($class))
            return $class::renderView($data['data'], false);

        return null;
    }

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


    private static function getData(array|null $state = [], bool $isState = true): array
    {
        if (!$isState)
            return $state ?? [];

        $state = collect($state);

        $schema = $state->firstWhere('type', static::class);

        if (!$schema)
            $schema = $state->firstWhere('type', static::class . 'Component');

        return $schema['data'] ?? [];
    }

    protected static function formalLabel(): string
    {
        return str(basename(static::class))
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->ucwords()
            ->toString();
    }

    public static function label(array|null $state = []): string|HtmlString
    {
        $displayLabel = static::getLabel($state);
        $componentLabel = str(static::formalLabel());

        if (!$displayLabel)
            return $componentLabel;

        return str(static::getLabel($state) ?? '')
            ->prepend(
                $componentLabel
                    ->wrap('<span class="text-gray-500">', '</span>')
                    ->prepend('<span class="mr-2 text-gray-500">·</span>')
                    ->append('<span class="mx-2 text-gray-500">·</span>')
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

    public static function blocks(array $additionalSchema = []): array
    {
        return collect(static::BLOCKS_CLASSES)
            ->map(fn($class) => $class::block($additionalSchema))->toArray();
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

    public static function editorSchema(array|null $state = null, bool $isState = true): array
    {
        return [
            ...static::getEditorSchema(static::getData($state, $isState))
        ];
    }

    public static function BaseBuilder(string $statePath = 'schema'): Builder
    {
        return Builder::make($statePath)
            ->blockPickerColumns(2)
            ->hiddenLabel()
            ->blockNumbers(false)
            ->blockIcons()
            ->columnSpanFull()
            ->addActionAlignment(Alignment::Left)
            ->addActionLabel('Add Content')
            ->cloneable()
            ->collapsible()
            ->collapsed(fn($operation) => $operation === 'edit')
            ->collapseAllAction(fn($action) => $action->visible(false))
            ->expandAllAction(fn($action) => $action->visible(false))
            ->addBetweenAction(fn($action) => $action->visible(false))
            ->extraItemActions([
                FilamentAction::make('promote_to_component')
                    ->tooltip("Promote to Reusable Component")
                    ->icon('tabler-star')
                    ->visible(function (array $arguments, Builder $component) {
                        $itemSchema = $component->getState()[$arguments['item']];

                        if (!$itemSchema)
                            return false;

                        if ($itemSchema['type'] === PromotedComponent::class)
                            return false;

                        return true;
                    })
                    ->modal()
                    ->modalWidth(Width::Medium)
                    ->schema(fn(array $arguments, Builder $component) => static::promotionSchema($arguments, $component))
                    ->modalSubmitActionLabel('Promote')
                    ->action(fn(array $arguments, array $data, Builder $component) => static::promote($arguments, $data, $component)),

                FilamentAction::make('preview')
                    ->modal()
                    ->modalWidth(Width::FitContent)
                    ->modalHeading(function (array $arguments, array $data, Builder $component) {
                        $itemSchema = $component->getState()[$arguments['item']];

                        /** @var class-string<Component> $class */
                        $class = $itemSchema['type'];

                        return str($class::label($itemSchema['data'] ?? []))->prepend('Preview')->toHtmlString();
                    })
                    ->modalIcon('tabler-eye')
                    ->modalDescription(str("Please note that there may be variations in styling on the final<br>content view as it depends what theme properties have been configured.")->toHtmlString())
                    ->tooltip('Preview')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Dismiss')
                    ->icon('tabler-eye')
                    ->schema(function (array $arguments, array $data, Builder $component) {
                        $itemSchema = $component->getState()[$arguments['item']];

                        /** @var class-string<Component> $class */
                        return Component::getSchemaFromData($itemSchema);
                    }),

                FilamentAction::make('layout_wrap')
                    ->modal()
                    ->modalWidth(Width::FitContent)
                    ->modalHeading('Wrap Components')
                    ->modalIcon('tabler-app-window')
                    ->modalDescription('This will create a layout wrapper around all components in the nest.')
                    ->tooltip('Wrap all in a layout.')
                    ->icon('tabler-app-window')
                    ->schema([
                        Select::make('layout_type')
                            ->options([
                                'flex' => 'Flex',
                                'grid' => 'Grid',
                            ]),
                    ]),
            ])
            ->blocks(Component::blocks());
    }

    #[NoReturn]
    public static function promote(array $arguments, array $data, \Filament\Schemas\Components\Component $component): void
    {
        $itemSchema = $component->getState()[$arguments['item']];

        $model = new \Cloudcake\Documents\Models\Component();
        $model->name = $data['name'];
        $model->schema = $itemSchema['data'];
        $model->type = $itemSchema['type'];
        $model->creator_id = auth()->id();
        $model->creator_type = auth()->user()->getMorphClass();
        $model->save();

        $state = $component->getState();

        $state[$arguments['item']] = [
            'data' => [
                'id' => $model->id,
            ],
            'type' => PromotedComponent::class,
        ];

        $component->state($state);
    }

    public static function promotionSchema(array $arguments, \Filament\Schemas\Components\Component $component): array
    {
        if ($component instanceof Builder) {
            $itemSchema = $component->getState()[$arguments['item']];
            $itemType = $itemSchema['type'];
        } else {
            $itemSchema = $component->getState();
            $itemType = 'layout';
        }

        return [
            TextInput::make('name')
                ->required(),

            FilamentText::make(str($itemType)
                ->afterLast('\\')
                ->kebab()
                ->replace(['-', '_'], ' ')
                ->apa())->badge()->color('primary')
        ];
    }
}