<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Enums\DocumentAccessSetting;
use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
use Cloudcake\Documents\Models\Concerns\FillsCurrentPanelOwner;
use Cloudcake\Documents\Models\Scopes\CurrentPanel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ScopedBy([CurrentPanel::class])]
class Revision extends Model
{
    use HasUuids;
    use FillsCurrentPanelOwner;

    const null UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'id'          => 'string',
            'panel_id',
            'content_id'  => 'string',
            'editor_id'   => 'string',
            'editor_type' => 'string',
            'rating'      => 'integer',
            'diff'        => 'array',
        ];
    }

    protected $fillable = [
        'id',
        'panel_id',
        'content_id',
        'editor_id',
        'editor_type',
        'diff',
    ];


    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function author(): MorphTo
    {
        return $this->morphTo('author', 'editor_type', 'editor_id');
    }

    public function getAuthorNameAttribute(): ?string
    {
        $document = \Cloudcake\Documents\Models\Document::current();

        if (!$document->setting(DocumentAuthorshipSetting::ShowEditor, false))
            return 'Hidden';

        $nameAttribute = $document->setting(DocumentAccessSetting::AuthNameAttribute, 'name');

        return $this->author?->$nameAttribute;
    }

    public function getTable(): string
    {
        return Document::getRevisionsTableName();
    }
}