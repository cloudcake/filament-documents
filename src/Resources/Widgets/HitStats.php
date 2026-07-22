<?php

namespace Cloudcake\Documents\Resources\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class HitStats extends BaseWidget
{
    protected ?string $heading = 'Visit Statistics';

    protected ?string $description = 'An overview of documentation viewer content interactions.';

    protected function getStats(): array
    {
        $hits = \Cloudcake\Documents\Models\Stat::hits()->sum('count');
        $hits = Number::format($hits, maxPrecision: 3);

        $misses = \Cloudcake\Documents\Models\Stat::misses()->sum('count');
        $misses = Number::format($misses, maxPrecision: 3);

        $forbidden = \Cloudcake\Documents\Models\Stat::forbidden()->sum('count');
        $forbidden = Number::format($forbidden, maxPrecision: 3);

        return [
            Stat::make('Hits', $hits)
                ->description('Total Document Hits')
                ->descriptionColor('gray')
                ->descriptionIcon('tabler-circle-check', IconPosition::Before)
                ->color('primary')
                ->icon('tabler-circle-check'),

            Stat::make('Misses', $misses)
                ->description('Number of non-existent slug hits.')
                ->descriptionColor('gray')
                ->descriptionIcon('tabler-error-404', IconPosition::Before)
                ->color('primary')
                ->icon('tabler-error-404'),

            Stat::make('Forbidden', $forbidden)
                ->description('Number of prohibited page hits.')
                ->descriptionColor('gray')
                ->descriptionIcon('tabler-shield-x', IconPosition::Before)
                ->color('primary')
                ->icon('tabler-shield-x'),
        ];
    }
}