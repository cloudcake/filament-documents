<?php

namespace Cloudcake\Documents\Models\Concerns;

use Cloudcake\Documents\Document;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait FillsCurrentPanelOwner
{
    /**
     * Boot the trait.
     */
    protected static function bootFillsCurrentPanelOwner(): void
    {
        if (!app()->runningInConsole()) {
            static::creating(function (Model $model) {
                $model->panel_id = Document::viewPanel()?->getId();
            });
        }
    }
}