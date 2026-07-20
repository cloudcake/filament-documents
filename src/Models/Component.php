<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Models\Concerns\FillsCurrentPanelOwner;
use Cloudcake\Documents\Models\Scopes\CurrentPanel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

#[ScopedBy([CurrentPanel::class])]
class Component extends Model
{
    use HasUuids;
    use FillsCurrentPanelOwner;

    const null UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'panel_id' => 'string',
            'creator_id' => 'string',
            'creator_type' => 'string',
            'name' => 'string',
            'schema' => 'array',
        ];
    }

    protected $fillable = [
        'id',
        'panel_id',
        'creator_id',
        'creator_type',
        'name',
        'schema',
        'created_at',
    ];

    public function getTable(): string
    {
        return Document::getComponentsTableName();
    }

    public function getComponentNameAttribute(): string
    {
        return str($this->type)
            ->afterLast('\\')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->apa()
            ->toString();
    }
}