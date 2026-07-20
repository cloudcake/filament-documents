<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Models\Concerns\FillsCurrentPanelOwner;
use Cloudcake\Documents\Models\Scopes\CurrentPanel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([CurrentPanel::class])]
class Rating extends Model
{
    use HasUuids;
    use FillsCurrentPanelOwner;

    const null UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'id'          => 'string',
            'content_id' => 'string',
        ];
    }

    protected $fillable = [
        'id',
        'panel_id',
        'content_id',
        'ip_address',
        'rater_id',
        'rater_type',
        'rating',
    ];


    public function content() : BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function getTable(): string
    {
        return Document::getRatingsTableName();
    }
}