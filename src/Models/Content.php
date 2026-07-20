<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Enums\DocumentAccessSetting;
use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
use Cloudcake\Documents\Enums\GateCheckType;
use Cloudcake\Documents\Models\Concerns\FillsCurrentPanelOwner;
use Cloudcake\Documents\Models\Concerns\HasRevisions;
use Cloudcake\Documents\Models\Scopes\CurrentPanel;
use Closure;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @property Collection $ratings
 * @property Collection $revisions
 * @property Collection $editor_names
 * @property string $writer_name
 * @property array $schema
 */
#[ScopedBy([CurrentPanel::class])]
class Content extends Model
{
    use HasUuids;
    use SoftDeletes;
    use FillsCurrentPanelOwner;
    use HasRevisions;

    private static Collection|null $cachedFullIndex = null;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'panel_id' => 'string',
            'parent_id' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'icon' => 'string',
            'sort' => 'integer',
            'published' => 'boolean',
            'featured' => 'boolean',
            'pinned' => 'boolean',
            'private' => 'boolean',
            'ratings_score' => 'integer',
            'ratings_total' => 'integer',
            'gate' => 'array',
            'tags' => 'array',
            'schema' => 'array',
            'custom' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected $fillable = [
        'id',
        'panel_id',
        'category_id',
        'parent_id',
        'title',
        'slug',
        'icon',
        'sort',
        'schema',
        'tags',
        'ratings_score',
        'ratings_total',
        'published',
        'featured',
        'pinned',
        'private',
        'custom',
        'gate',
        // Publish Date
        'created_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Content $model) {
            if (blank($model->slug))
                $model->slug = str($model->title)->slug();

            $hasUser = auth()->check();

            if ($hasUser) {
                $model->creator_type = auth()->user()->getMorphClass();
                $model->creator_id = auth()->id();
            }
        });
    }

    public function getTable(): string
    {
        return Document::getContentTableName();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('creator', 'creator_type', 'creator_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Content::class, 'parent_id');
    }

    public static function getFirstIndex(): ?static
    {
        return static::fullIndex(GateCheckType::Access)->first();
    }

    public static function fullIndex(GateCheckType $checkType, bool $forceRefresh = false, int|bool $limit = false, Closure $whereSubQuery = null): Collection
    {
        if (!is_null(static::$cachedFullIndex) && !$forceRefresh)
            return static::$cachedFullIndex
                ->when(
                    $checkType !== GateCheckType::None,
                    fn($collection) => $collection->filter(fn($record) => Document::allow($record, $checkType)));

        $indexColumns = ['id', 'parent_id', 'category_id', 'tags', 'title', 'slug', 'icon', 'published', 'featured', 'pinned', 'private', 'sort', 'gate'];

        static::$cachedFullIndex = self::query()
            ->select($indexColumns)
            ->whereNull('parent_id')
            ->where('published', true)
            ->with([
                'category',
                'children' => fn($q) => $q->select($indexColumns)
            ])
            ->withAggregate('category', 'sort')
            ->orderBy("category_sort", 'asc')
            ->orderBy("sort", 'asc')
            ->when($whereSubQuery, fn($query) => $query->where(fn($query) => $whereSubQuery($query)))
            ->when($limit, fn($query) => $query->limit($limit))
            ->get();

        return static::fullIndex($checkType, forceRefresh: false);
    }

    public static function search(string $term, int $limit = 5)
    {
        $split = collect(explode(' ', $term))->filter()->all();

        return static::fullIndex(checkType: GateCheckType::Access, forceRefresh: true, limit: $limit, whereSubQuery: function ($query) use ($term, $split) {

            $query->where('title', 'like', "%$term%");

            foreach ($split as $part) {
                $query
                    ->orWhere('title', 'like', "%$part%")
                    ->orWhereJsonContains('tags', $part);
            }
        });
    }

    /** @noinspection PhpUnused */
    public function getWriterNameAttribute(): ?string
    {
        $document = \Cloudcake\Documents\Models\Document::current();
        $nameAttribute = $document->setting(DocumentAccessSetting::AuthNameAttribute, 'name');

        if (!$document->setting(DocumentAuthorshipSetting::ShowWriter, false))
            return 'Hidden';

        return $this->creator->$nameAttribute;
    }

    /** @noinspection PhpUnused */
    public function getUrlAttribute(): ?string
    {
        $viewPanel = Document::viewPanel();

        return $viewPanel->getUrl() . '/' . $this->slug;
    }

    public function getCrumbsAttribute(): ?array
    {
        $appName = config('app.name');

        $crumbs = [
            $appName,
            'Documentation',
        ];

        if ($this->relationLoaded('category') && $this->category) {
            $crumbs[] = $this->category->name;
        }

        if ($this->relationLoaded('parent') && $this->parent) {
            if ($this->parent->category) {
                $crumbs[] = $this->parent->category->name;
            }

            $crumbs[] = $this->parent->title;
        }

        $crumbs[] = $this->title;

        return $crumbs;
    }
}
