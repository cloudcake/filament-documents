<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Models\Concerns\FillsCurrentPanelOwner;
use Cloudcake\Documents\Models\Scopes\CurrentPanel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([CurrentPanel::class])]
class Category extends Model
{
    use HasUuids;
    use FillsCurrentPanelOwner;

    const null UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'id'          => 'string',
            'panel_id'    => 'string',
            'sort'        => 'integer',
            'name'        => 'string',
            'collapsible' => 'boolean',
            'published'   => 'boolean',
            'featured'    => 'boolean',
            'pinned'      => 'boolean',
            'private'     => 'boolean',
            'custom'   => 'array',
            'gate'        => 'array',
        ];
    }

    protected $fillable = [
        'id',
        'panel_id',
        'sort',
        'name',
        'icon',
        'collapsible',
        'published',
        'featured',
        'pinned',
        'private',
        'custom',
        'gate',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function getTable(): string
    {
        return Document::getCategoriesTableName();
    }
}