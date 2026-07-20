<?php

namespace Cloudcake\Documents\Models\Concerns;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Enums\DocumentAccessSetting;
use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
use Cloudcake\Documents\Models\Revision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @mixin Model
 */
trait HasRevisions
{
    protected static function bootHasRevisions(): void
    {
        if (!app()->runningInConsole()) {
            static::updating(function (Model $model) {
                $currentPanel = filament()->getCurrentPanel();

                if (!blank($model->getDirtyForUpdate()) && $currentPanel && $currentPanel->getId() !== Document::viewPanel()->getId()) {
                    $hasUser = auth()->check();

                    $dirty = $model->getDirtyForUpdate();
                    $dirtyKeys = array_keys($dirty);

                    $model->revisions()->create([
                        'editor_id' => $hasUser ? auth()->id() : null,
                        'editor_type' => $hasUser ? auth()->user()->getMorphClass() : null,
                        'diff' => [
                            collect($model->getOriginal())->filter(fn($item, $key) => in_array($key, $dirtyKeys)),
                            $dirty
                        ]
                    ]);
                }
            });
        }
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class);
    }

    /** @noinspection PhpUnused */
    public function getEditorNamesAttribute(): SupportCollection
    {
        $document = \Cloudcake\Documents\Models\Document::current();
        $nameAttribute = $document->setting(DocumentAccessSetting::AuthNameAttribute, 'name');

        if (!$document->setting(DocumentAuthorshipSetting::ShowEditor, false))
            return collect(['Hidden']);

        return $this->revisions()
            ->select(['id', 'editor_id', 'editor_type'])
            ->with('author')
            ->get()
            ->filter(fn($record) => $record->author)
            ->map(fn($record) => $record->author->$nameAttribute)
            ->unique();
    }
}