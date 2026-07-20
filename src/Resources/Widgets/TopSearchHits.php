<?php

namespace Cloudcake\Documents\Resources\Widgets;

use Cloudcake\Documents\Models\Stat;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class TopSearchHits extends ChartWidget
{
    protected ?string $heading = 'Coming Soon';

    private int $max = 10;

    /**
     * @return string|null
     */
    public function getHeading(): ?string
    {
        return "Top $this->max Hits";
    }

    public function getDescription(): string|Htmlable|null
    {
        return 'Most common search terms that render results.';
    }

    protected function getData(): array
    {
        $data = Stat::searchHits()
            ->orderBy('count', 'desc')
            ->take($this->max)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Hits',
                    'data' => $data->map(fn($item) => $item->count)->toArray(),
                ],
            ],
            'labels' => $data->map(fn($item) => str($item->data)->limit(24))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}