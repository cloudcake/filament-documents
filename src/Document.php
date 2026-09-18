<?php

namespace Cloudcake\Documents;

use Cloudcake\Documents\Enums\GateCheckType;
use Cloudcake\Documents\Enums\GateFilter;
use Cloudcake\Documents\Enums\GateRequirement;
use Cloudcake\Documents\Livewire\Partials\ContentSearch;
use Cloudcake\Documents\Models\Category;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Pages\Documentation;
use Cloudcake\Documents\Pages\Settings;
use Cloudcake\Documents\Pages\ViewDocument;
use Cloudcake\Documents\Resources\Categories\CategoryResource;
use Cloudcake\Documents\Resources\Components\ComponentResource;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Exception;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Infolists\Components\CodeEntry;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Phiki\Theme\Theme;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class Document implements Plugin
{
    public static string $id = 'cloudcake-documents';
    private string|null $adminFor = null;

    /**
     * @return static
     */
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return static::$id;
    }

    /**
     * @throws Exception
     */
    public function register(Panel $panel): void
    {
        $panel
            ->assets([
                Css::make('cloudcake-documents-css', __DIR__ . '/../resources/dist/css/document.css'),
            ])
            ->when(
                fn() => $this->adminFor,
                fn(Panel $panel) => $panel
                    ->bootUsing(function () {
                        if (!filament()->getPanel($this->adminFor))
                            throw new Exception("The documentation view panel has not been setup correctly. A panel named '$this->adminFor' does not exist. Please follow the installation instructions before using the admin panel.");
                    })
                    ->pages([
                        Documentation::class,
                        Settings::class,
                    ])
                    ->resources([
                        CategoryResource::class,
                        ComponentResource::class,
                        ContentResource::class,
                    ]),
                function (Panel $panel) {

                    // IMPORTANT: When route caching happens it's running in console, therefore, we need to tell
                    // laravel about our page first and only then we can return early.
                    $panel->login()->pages([
                        ViewDocument::class,
                    ]);

                    if (app()->runningInConsole())
                        return;

                    CodeEntry::configureUsing(fn(CodeEntry $codeEntry) => $codeEntry
                        ->lightTheme(Theme::TokyoNight)
                        ->darkTheme(Theme::TokyoNight)
                    );

                    $panel
                        ->renderHook(PanelsRenderHook::TOPBAR_LOGO_AFTER, fn(): string => Blade::render(
                            "@livewire('" . ContentSearch::class . "', [])"
                        ))
                        ->navigation(fn(NavigationBuilder $builder) => $builder
                            ->items(static::getNavigationItems($panel))
                            ->groups(static::getNavigationGroups($panel))
                        );
                }
            );
    }

    public function boot(Panel $panel): void
    {

        $panel
            ->when(
                fn() => $this->adminFor && $panel->getId() == $this->adminFor,
                fn() => throw new Exception("This panel ({$panel->getId()}) cannot provision for itself, the 'for' parameter must be a different panel ID. Attempted to set 'for' to '$this->adminFor' which is the current panel.")
            );
    }

    public static function allow(Content|Category $comparable, GateCheckType $checkType): bool
    {
        if ($comparable->relationLoaded('category') && $comparable->category ?? false)
            return static::allows($comparable->category, $checkType) && static::allows($comparable, $checkType);

        return static::allows($comparable, $checkType);
    }

    private static function allows(Content|Category $comparable, GateCheckType $checkType): bool
    {
        // If the content isn't published, immediately refuse access.
        if (!$comparable->published)
            return false;

        // If the content isn't marked private, immediately allow access.
        if (!($comparable->private ?? false))
            return true;

        // If for some reason there's a problem and the content is private
        // but there's no gate data, deny access and return early since
        // it is marked private, but without rules.
        if (blank($comparable->gate ?? []))
            return false;

        $filter = GateFilter::tryFrom($comparable->gate['filter'] ?? '');

        // If we cannot resolve to a filter, we safely deny and return early.
        if (!$filter)
            return false;

        // If we're only trying to list the index (not access content) and the
        // filter is of type display, allow and return early.
        if ($checkType === GateCheckType::Index && $filter === GateFilter::Display)
            return true;

        // If we're only trying to list the index (not access contact and the
        // filter is of type unlisted, deny and return early.
        if ($checkType === GateCheckType::Index && $filter === GateFilter::Unlisted)
            return false;

        $requirement = GateRequirement::tryFrom($comparable->gate['requirement'] ?? '');

        // If we cannot evaluate to a requirement, but we know the content is marked private,
        // deny and return early as there's nothing else we can do at this point.
        if (!$requirement)
            return false;

        if ($requirement === GateRequirement::None)
            return true;

        $panel = Document::viewPanel() ?? false;
        $user = $panel?->auth()->user();
        $hasUser = !blank($user) || !$panel;

        if (!$hasUser)
            return false;

        if ($requirement === GateRequirement::SimpleAuth)
            return true;
        else {
            $canAccess = false;

            if ($requirement === GateRequirement::FunctionResult) {
                $attribute = $gate['attribute'] ?? false;
                $values = $gate['values'] ?? [];

                if (!$attribute || empty($values))
                    return false;

                $result = $user->$attribute();

                if (is_null($result) || $result === false)
                    return false;

                $canAccess =
                    ($result === true) ||
                    (is_array($result) && array_any($values, fn($value) => in_array($value, $result))) ||
                    (is_string($result) && in_array($result, $values, true));
            }
        }

        return $canAccess;
    }

    public function admin(string $for): static
    {
        $this->adminFor = $for;

        return $this;
    }

    public function isAdmin(): bool
    {
        return !blank($this->adminFor);
    }

    public function getAdminFor(): string
    {
        return $this->adminFor;
    }

    public static function getContentTableName(): string
    {
        return config('documents.migration.documents.documents', 'panel_documents');
    }

    public static function getRevisionsTableName(): string
    {
        return config('documents.migration.documents.revisions', 'panel_document_revisions');
    }

    public static function getRatingsTableName(): string
    {
        return config('documents.migration.documents.ratings', 'panel_document_ratings');
    }

    public static function getSettingsTableName(): string
    {
        return config('documents.migration.documents.settings', 'panel_document_settings');
    }

    public static function getCategoriesTableName()
    {
        return config('documents.migration.documents.categories', 'panel_document_categories');
    }

    public static function getComponentsTableName()
    {
        return config('documents.migration.documents.components', 'panel_document_components');
    }

    public static function getStatsTableName()
    {
        return config('documents.migration.documents.stats', 'panel_document_stats');
    }

    private static function getNavigationItems(Panel $panel): array
    {
        $contentWithoutCategories = Content::fullIndex(GateCheckType::Index)->whereNull('category_id');

        $navigationItems = [];

        foreach ($contentWithoutCategories as $content) {
            $navigationItems[] = static::makeNavigationItem($panel, $content);
        }

        return $navigationItems;
    }

    private static function getNavigationGroups(Panel $panel): array
    {
        $contents = Content::fullIndex(GateCheckType::Index);

        $categories = $contents
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->keyBy('id')
            ->sort(fn($a, $b) => $a->sort <=> $b->sort)
            ->map(fn($category) => NavigationGroup::make($category->name)
                ->extraSidebarAttributes(['class' => $category->featured ? 'featured' : ''])
                ->collapsible($category->collapsible)
            );

        foreach ($contents as $content) {
            if ($content->category) {
                /** @var NavigationGroup $categoryNavGroup */
                $categoryNavGroup = $categories[$content->category->id];
                $categoryNavGroup->items([
                    ...$categoryNavGroup->getItems(),
                    static::makeNavigationItem($panel, $content)
                ]);
            }
        }

        return $categories->values()->toArray();
    }

    private static function makeNavigationItem(Panel $panel, Content $model, bool $nested = false): NavigationItem
    {
        $item = NavigationItem::make($model->title)
            ->url(fn() => static::safePath($panel, $model->slug))
            ->icon($model->icon ?? 'tabler-cube')
            ->extraAttributes(['class' => $model->featured ? 'featured' : ''])
            ->badge(fn($badge) => $model->featured ? 'Featured' : null)
            ->isActiveWhen(fn() => request()->route()->parameter('slug1') === $model->slug);

        if (!$nested && $model->relationLoaded('children')) {
            $childItems = [];

            foreach ($model->children as $child) {
                $childItems[] = self::makeNavigationItem($panel, $child, true);
            }

            $item->childItems($childItems);
        }

        return $item;
    }

    private static function safePath(Panel $panel, string $path): string
    {
        return str($panel->getPath() . '/' . $path)->replace('//', '/')->start('/')->toString();
    }

    public static function setVar(string $key, mixed $value): void
    {
        $variables = static::getVar();

        data_set($variables, $key, $value);

        session()->put("environment-variables", $variables);
    }

    public static function getVar(string|null $key = null, mixed $default = null): mixed
    {
        $variables = session()->get("environment-variables", $default);

        if (blank($key))
            return $variables;

        return data_get($variables, $key, $default);
    }

    public static function viewPanel(): ?Panel
    {
        /** @var Document $plugin */
        $plugin = filament()->getCurrentPanel()->getPlugin(static::$id);

        return $plugin->isAdmin()
            ? filament()->getPanel($plugin->getAdminFor())
            : filament()->getCurrentPanel();
    }

    private static function getViewerModel(): ?Model
    {
        $panel = static::viewPanel();

        if (!$panel)
            return null;

        $guard = $panel->getAuthGuard();
        $provider = config("auth.guards.$guard.provider");
        $model = config("auth.providers.$provider.model");

        if (!$model)
            return null;

        /** @var Model $model */
        return new $model;
    }

    public static function getAvailableViewerAttributes(array $returnTypes = ['array', 'bool', 'string']): array
    {
        $model = static::getViewerModel();
        $results = [];

        try {
            $reflection = new ReflectionClass($model::class);

            /**
             * @var array<ReflectionProperty|ReflectionMethod> $objects
             */
            $iterateReflection = function (array $objects, &$results) use ($model, $returnTypes) {
                foreach ($objects as $object) {
                    // Skip inheritance methods
                    if ($object->getDeclaringClass()->getName() !== $model::class)
                        continue;

                    $returnType = $object->getReturnType();

                    if (!$returnType instanceof ReflectionNamedType)
                        continue;

                    if (!in_array($returnType->getName(), $returnTypes))
                        continue;

                    if ($object instanceof ReflectionMethod) {
                        $parameters = collect($object->getParameters())
                            ->map(fn($parameter) => $parameter->getName())
                            ->implode(', ');

                        $results[$object->getName()] = $object->getName() . "($parameters): " . $returnType->getName();
                    }
                }
            };

            $iterateReflection($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC), $results);
        } catch (\ReflectionException) {
            // Don't care, just return empty results.
        }

        return $results;
    }

    public static function getViewerAttributeReturnType(string $methodOrPropertyName): ?string
    {
        $model = static::getViewerModel();

        try {
            $reflection = new ReflectionClass($model::class);

            if (method_exists($model, $methodOrPropertyName)) {
                return $reflection->getMethod($methodOrPropertyName)->getReturnType()->getName();
            }
        } catch (\Exception) {
            return null;
        }

        return null;
    }
}
