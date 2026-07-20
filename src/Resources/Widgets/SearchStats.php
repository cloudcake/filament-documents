<?php

namespace Cloudcake\Documents\Resources\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class SearchStats extends BaseWidget
{
    protected ?string $heading = 'Search Statistics';

    protected ?string $description = 'An overview of documentation viewer searching interactions.';

    protected function getStats(): array
    {
        $searches = \Cloudcake\Documents\Models\Stat::searches()->sum('count');
        $searches = Number::format($searches, maxPrecision: 3);

        $searchHits = \Cloudcake\Documents\Models\Stat::searchHits()->sum('count');
        $searchHits = Number::format($searchHits, maxPrecision: 3);

        $searchMisses = \Cloudcake\Documents\Models\Stat::searchMisses()->sum('count');
        $searchMisses = Number::format($searchMisses, maxPrecision: 3);

        return [
            Stat::make('Searches', $searches)
                ->description('Total Document Searches')
                ->descriptionIcon('tabler-search')
                ->color('primary')
                ->icon('tabler-search'),

            Stat::make('Search Hits', $searchHits)
                ->description('Number of searches rendering results.')
                ->descriptionIcon('tabler-mood-search')
                ->color('primary')
                ->icon('tabler-mood-search'),

            Stat::make('Search Misses', $searchMisses)
                ->description('Number of searches missing results.')
                ->descriptionIcon('tabler-search-off')
                ->color('primary')
                ->icon('tabler-search-off'),
        ];
    }
}