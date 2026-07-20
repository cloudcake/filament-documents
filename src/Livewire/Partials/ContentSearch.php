<?php

namespace Cloudcake\Documents\Livewire\Partials;

use Cloudcake\Documents\Enums\StatKey;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Models\Stat;
use Livewire\Component;

class ContentSearch extends Component
{
    public string $term = '';

    public array $results = [];

    public function render()
    {
        return view('cloudcake-documents::livewire.content-search');
    }

    public function search()
    {
        $this->results = [];

        if (!empty($this->term)) {
            $term = strtolower($this->term);

            $this->results = Content::search($term)->all();

            if (blank($this->results))
                $stat = Stat::firstOrCreate(['key' => StatKey::SearchMiss, 'data' => $term], ['count' => 0]);
            else
                $stat = Stat::firstOrCreate(['key' => StatKey::SearchHit, 'data' => $term], ['count' => 0]);

            $stat->increment('count');
        }
    }
}