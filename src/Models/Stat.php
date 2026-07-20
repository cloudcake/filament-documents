<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Enums\StatKey;
use Cloudcake\Documents\Models\Concerns\FillsCurrentPanelOwner;
use Cloudcake\Documents\Models\Scopes\CurrentPanel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([CurrentPanel::class])]
class Stat extends Model
{
    use HasUuids;
    use FillsCurrentPanelOwner;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'panel_id' => 'string',
            'key' => StatKey::class,
            'count' => 'integer',
            'data' => 'string',
        ];
    }

    protected $fillable = [
        'id',
        'panel_id',
        'key',
        'count',
        'data',
    ];

    public function getTable(): string
    {
        return Document::getStatsTableName();
    }

    public static function searches(): Builder
    {
        return static::query()
            ->orWhere('key', StatKey::SearchHit)
            ->orWhere('key', StatKey::SearchMiss);
    }

    public static function searchMisses(): Builder
    {
        return static::query()->where('key', StatKey::SearchMiss);
    }

    public static function searchHits(): Builder
    {
        return static::query()->where('key', StatKey::SearchHit);
    }
}