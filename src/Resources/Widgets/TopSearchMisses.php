<?php

namespace Cloudcake\Documents\Resources\Widgets;

use Cloudcake\Documents\Models\Stat;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class TopSearchMisses extends ChartWidget
{
    private int $max = 10;

    /**
     * @return string|null
     */
    public function getHeading(): ?string
    {
        return "Top $this->max Misses";
    }

    public function getDescription(): string|Htmlable|null
    {
        return 'Most common search terms that do not render any results.';
    }

    protected function getData(): array
    {
        $data = Stat::searchMisses()
            ->orderBy('count', 'desc')
            ->take($this->max)
            ->get();

        $hasEnoughData = $data->count() == $this->max;

        if (!$hasEnoughData)
            return ['datasets' => null, 'labels' => ['Insufficient Data']];

        return [
            'datasets' => [
                [
                    'label' => 'Misses',
                    'data' => $data->map(fn($item) => $item->count)->toArray(),
                ],
            ],
            'labels' => $data->map(fn($item) => str($item->data)->limit(24)->wrap('“', '”'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}