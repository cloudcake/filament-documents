<?php

namespace Cloudcake\Documents\Resources\Widgets;

use Cloudcake\Documents\Models\Stat;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class HitsChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    /**
     * @return string|null
     */
    public function getHeading(): ?string
    {
        return "Hit Type Frequency";
    }

    public function getDescription(): string|Htmlable|null
    {
        return 'Most recent hits to misses to forbid render results.';
    }

    protected function getData(): array
    {
//        $data = Stat::hits()
//            ->take($this->max)
//            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Hits',
                    'data' => collect(array_fill(0, 31, 0))->map(fn() => random_int(1, 100))->toArray(),
                    'tension' => $this->getTension(),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'pointBackgroundColor' => 'rgba(75, 192, 192, 0.9)',
                    'pointBorderColor' => 'rgba(75, 192, 192, 0.5)',
                    'pointRadius' => 2
                ],
                [
                    'label' => 'Misses',
                    'data' => collect(array_fill(0, 31, 0))->map(fn() => random_int(1, 100))->toArray(),
                    'tension' => $this->getTension(),
                    'borderColor' => 'rgba(75, 75, 192, 1)',
                    'pointBackgroundColor' => 'rgba(75, 75, 192, 0.9)',
                    'pointBorderColor' => 'rgba(75, 75, 192, 0.5)',
                    'pointRadius' => 2
                ],
                [
                    'label' => 'Forbidden',
                    'data' => collect(array_fill(0, 31, 0))->map(fn() => random_int(1, 100))->toArray(),
                    'tension' => $this->getTension(),
                    'borderColor' => 'rgba(192, 75, 75, 1)',
                    'pointBackgroundColor' => 'rgba(192, 75, 75, 0.9)',
                    'pointBorderColor' => 'rgba(192, 75, 75, 0.5)',
                    'pointRadius' => 2
                ]
            ],
            'labels' => collect(array_fill(0, 31, 0))->map(fn($item, $index) => $index)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getTension(): float
    {
        return 0.2;
    }
}