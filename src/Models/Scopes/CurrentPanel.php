<?php

namespace Cloudcake\Documents\Models\Scopes;

use Cloudcake\Documents\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentPanel implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!app()->runningInConsole()) {
            $panelId = filament()->getCurrentPanel()?->getId();
            $viewPanelId = Document::viewPanel()?->getId();

            $builder->when(
                $viewPanelId != null && $panelId == $viewPanelId,
                fn($q) => $q->where('panel_id', $panelId)
            );
        }
    }
}