<?php

namespace Cloudcake\Documents\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component as LivewireComponent;

/**
 * @property Schema $schema
 */
abstract class Component extends LivewireComponent implements HasSchemas, HasActions
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public function render()
    {
        return view('cloudcake-documents::layouts.schema');
    }

    public function mount(array $data = [])
    {
        $this->data = $data;
        $this->schema->fill($data);
    }

    public function tags(): array
    {
        return $this->data['data']['tags'] ?? [];
    }

    public static function editorSchema(array $data): array
    {
        return [];
    }

    public static function renderSchema(array $data): array
    {
        return [];
    }
}