<?php

namespace Cloudcake\Documents\Resources\Contents\Schemas;

use Cloudcake\Components\Forms\Components\IconPicker;
use Cloudcake\Documents\Document;
use Cloudcake\Documents\Enums\GateFilter;
use Cloudcake\Documents\Enums\GateRequirement;
use Cloudcake\Documents\Livewire\Callout;
use Cloudcake\Documents\Livewire\Code;
use Cloudcake\Documents\Livewire\Form;
use Cloudcake\Documents\Livewire\HttpRequest;
use Cloudcake\Documents\Livewire\Graph;
use Cloudcake\Documents\Livewire\PromotedComponent;
use Cloudcake\Documents\Livewire\RichContent;
use Cloudcake\Documents\Livewire\Tabs;
use Cloudcake\Documents\Models\Category;
use Cloudcake\Documents\Models\Component;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Resources\Categories\Schemas\CategoryForm;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use JetBrains\PhpStorm\NoReturn;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Content')
                            ->icon('tabler-text-plus')
                            ->columnSpan(2)
                            ->headerActions([
                                Action::make('convert_to_component')
                                    ->color('gray')
                                    ->iconButton()
                                    ->tooltip('Convert to Component')
                                    ->icon('tabler-star')
                                    ->modalWidth(Width::Small)
                                    ->schema(fn(array $arguments, Section $component) => static::promotionSchema($arguments, $component))
                                    ->action(fn(array $arguments, array $data, Section $component) => static::promote($arguments, $data, $component))
                                    ->modalDescription(new HtmlString("This action will convert the page into a reusable component and replace all content with the component reference.<br><br>Are you sure you want to do this?"))
                                    ->requiresConfirmation()
                            ])
                            ->components([
                                TextInput::make('title')
                                    ->required()
                                    ->inlineLabel(),

                                Builder::make('schema')
                                    ->hiddenLabel()
                                    ->blockNumbers(false)
                                    ->blockIcons()
                                    ->columnSpanFull()
                                    ->addActionAlignment(Alignment::Left)
                                    ->addActionLabel('Add Content')
                                    ->collapsible()
                                    ->cloneable()
                                    ->collapsed(fn($operation) => $operation === 'edit')
                                    ->collapseAllAction(fn($action) => $action->visible(false))
                                    ->expandAllAction(fn($action) => $action->visible(false))
                                    ->addBetweenAction(fn($action) => $action->visible(false))
                                    ->extraItemActions([
                                        Action::make('promote_to_component')
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

                                        Action::make('preview')
                                            ->modal()
                                            ->modalWidth(Width::FiveExtraLarge)
                                            ->tooltip('Preview')
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Dismiss')
                                            ->icon('tabler-eye')
                                            ->schema(function (array $arguments, array $data, Builder $component) {
                                                $itemSchema = $component->getState()[$arguments['item']];

                                                /** @var class-string<\Cloudcake\Documents\Livewire\Component> $class */
                                                $class = $itemSchema['type'];

                                                if (class_exists($class))
                                                    return $class::renderPreview($itemSchema['data'] ?? []);

                                                return [];
                                            })
                                    ])
                                    ->blocks(\Cloudcake\Documents\Livewire\Component::blocks()),
                            ]),

                        Hidden::make('categoryPrivateHint')->dehydrated(false),
                        Hidden::make('categoryUnpublishedHint')->dehydrated(false),

                        Section::make('')
                            ->columnSpan(1)
                            ->compact(false)
                            ->components([
                                Select::make('category_id')
                                    ->placeholder('None')
                                    ->hintIcon('tabler-help')
                                    ->hintIconTooltip("If no category is selected, the content will be prioritised in the navigation pane.")
                                    ->createOptionForm(fn(Schema $schema) => CategoryForm::configure($schema))
                                    ->createOptionAction(fn(Action $action) => $action->modalWidth(Width::Medium))
                                    ->label('Category')
                                    ->visible(fn($get) => !$get('parent_id'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $category = Category::withoutGlobalScopes()->find($state);

                                            $set('categoryPrivateHint', $category?->private);
                                            $set('categoryUnpublishedHint', !$category?->published);
                                        } else {
                                            $set('categoryPrivateHint', false);
                                            $set('categoryUnpublishedHint', false);
                                        }
                                    })
                                    ->afterStateHydrated(function ($record, $set) {
                                        if ($record && $record->category) {
                                            $set('categoryPrivateHint', $record->category->private);
                                            $set('categoryUnpublishedHint', !$record->category->published);
                                        }
                                    })
                                    ->belowContent([
                                        Text::make("Unpublished Category")
                                            ->badge()
                                            ->visible(fn($get) => $get('../categoryUnpublishedHint')),

                                        Text::make("Private Category")
                                            ->badge()
                                            ->visible(fn($get) => $get('../categoryPrivateHint')),
                                    ])
                                    ->relationship('category', 'name'),

                                Text::make(fn($record) => "Parent modification unavailable because this content has {$record->children->count()} sub-content associated.")
                                    ->visible(fn($record) => $record && $record->children->count() > 0),

                                Select::make('parent_id')
                                    ->hintIcon('tabler-help')
                                    ->hintIconTooltip("If a parent is selected, the category will be forced to none as the item will inherit the parent's category.")
                                    ->placeholder('None')
                                    ->label('Parent')
                                    ->live()
                                    ->visible(fn($record) => !$record || $record->children->count() === 0)
                                    ->default(fn($record) => request()->route('parent'))
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $parent = Content::withoutGlobalScopes()->select(['private', 'published'])->find($state);

                                            $set('parentPrivateHint', $parent?->private);
                                            $set('parentUnpublishedHint', !$parent?->published);
                                            $set('category_id', null);
                                        } else {
                                            $set('parentPrivateHint', false);
                                            $set('parentUnpublishedHint', false);
                                        }
                                    })
                                    ->afterStateHydrated(function ($record, $set) {
                                        if ($record && $record->parent) {
                                            $set('parentPrivateHint', $record->parent->private);
                                            $set('parentUnpublishedHint', !$record->parent->published);
                                        }
                                    })
                                    ->afterLabel([
                                        Text::make("Creating Sub-Content")
                                            ->badge()
                                            ->color('primary')
                                            ->visible(fn() => request()->route('parent')),
                                    ])
                                    ->belowContent([
                                        Text::make("Unpublished Parent")
                                            ->badge()
                                            ->visible(fn($get) => $get('../parentUnpublishedHint')),

                                        Text::make("Private Parent")
                                            ->badge()
                                            ->visible(fn($get) => $get('../parentUnpublishedHint')),

                                        Text::make("")
                                    ])
                                    ->relationship(
                                        name: 'parent',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn($query) => $query->whereNull('parent_id'),
                                        ignoreRecord: true
                                    ),

                                IconPicker::make('icon')->grow(false)->inlineLabel(),

                                TextInput::make('sort')
                                    ->required()
                                    ->default(10)
                                    ->label('Priority')
                                    ->hintIcon('tabler-help')
                                    ->hintIconTooltip("Lower priority positions move the content closer to the top of the navigation pane. This value is relative to the category (if any) the content belongs to.")
                                    ->inlineLabel()
                                    ->numeric(),

                                TextInput::make('slug')
                                    ->inlineLabel()
                                    ->rules('lowercase|string|alpha_dash')
                                    ->unique(modifyRuleUsing: function (Unique $rule) {
                                        return $rule->where('panel_id', Document::viewPanel()->getId());
                                    }),

                                TagsInput::make('tags')
                                    ->inlineLabel()
                                    ->hintIcon('tabler-help')
                                    ->hintIconTooltip('Tags assist search functions to find relevant content. Combining this information with your highest search miss terms you can help you better search filtering.')
                                    ->belowContent([
                                        Text::make('Assists search to find relevant content.')->size(Size::ExtraSmall)
                                    ])
                                    ->color('primary')
                                    ->reorderable()
                                    ->nestedRecursiveRules([
                                        'min:3',
                                        'max:32',
                                    ]),

                                Fieldset::make('')
                                    ->contained(false)
                                    ->columnSpanFull()
                                    ->components([
                                        Toggle::make('published')
                                            ->live()
                                            ->afterLabel([
                                                Text::make('Not Published')
                                                    ->badge()
                                                    ->color('gray')
                                                    ->visible(fn($get) => !$get('../published')),

                                            ])
                                            ->belowLabel("Unpublished content is hidden.")
                                            ->columnSpanFull(),

                                        DatePicker::make('created_at')
                                            ->default(now())
                                            ->label('Published Date')
                                            ->beforeOrEqual(now())
                                            ->columnSpanFull()
                                            ->visible(fn($get) => $get('published')),

                                        Toggle::make('featured')
                                            ->belowContent("Highlights & applies visual aesthetics.")
                                            ->columnSpanFull(),

                                        Toggle::make('private')
                                            ->live()
                                            ->belowContent("Restricts content to criteria.")
                                            ->columnSpanFull(),

                                        Select::make(GateFilter::StatePath)
                                            ->label(GateFilter::Label)
                                            ->live()
                                            ->columnSpanFull()
                                            ->required()
                                            ->native(false)
                                            ->searchable()
                                            ->allowHtml()
                                            ->visible(fn($get) => $get('private'))
                                            ->options(GateFilter::getOptions()),

                                        Select::make(GateRequirement::StatePath)
                                            ->label(GateRequirement::Label)
                                            ->live()
                                            ->required()
                                            ->columnSpanFull()
                                            ->native(false)
                                            ->searchable()
                                            ->allowHtml()
                                            ->visible(fn($get) => $get('private'))
                                            ->options(GateRequirement::getOptions()),

                                        Select::make('gate.attribute')
                                            ->live()
                                            ->required()
                                            ->visible(fn($get) => $get('private') && $get(GateRequirement::StatePath) === GateRequirement::FunctionResult->value)
                                            ->label("Private Gate Function")
                                            ->afterStateUpdated(fn($set, $state) => $set('gate.type', Document::getViewerAttributeReturnType($state)))
                                            ->columnSpanFull()
                                            ->aboveContent("Only functions returning an array, boolean or a string are selectable and supported.")
                                            ->options(fn() => Document::getAvailableViewerAttributes()),

                                        Hidden::make('gate.function_return_type'),

                                        Repeater::make('gate.values')
                                            ->required()
                                            ->label('Result equals one of')
                                            ->simple(TextInput::make('value'))
                                            ->addActionLabel('Add Match')
                                            ->addActionAlignment(Alignment::Left)
                                            ->columnSpanFull()
                                            ->visible(fn($get) => $get('private')
                                                && $get(GateFilter::StatePath)
                                                && $get(GateFilter::StatePath) !== GateFilter::Display->value
                                                && $get(GateRequirement::StatePath)
                                                && $get(GateRequirement::StatePath) === GateRequirement::FunctionResult->value
                                                && ($get('gate.function_return_type') === 'array' || $get('gate.function_return_type') === 'string')),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    #[NoReturn]
    private static function promote(array $arguments, array $data, Builder|Section $component): void
    {
        $itemSchema = $component->getState()[$arguments['item']];

        $model = new Component();
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

    private static function promotionSchema(array $arguments, Builder|Section $component): array
    {
        if ($component instanceof Builder) {
            $itemSchema = $component->getState()[$arguments['item']];
            $itemType = $itemSchema['type'];
        }
        else {
            $itemSchema = $component->getState();
            $itemType = 'layout';
        }

        return [
            TextInput::make('name')
                ->required(),

            Text::make(str($itemType)
                ->afterLast('\\')
                ->kebab()
                ->replace(['-', '_'], ' ')
                ->apa())->badge()->color('primary')
        ];
    }
}
