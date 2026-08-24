<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use BackedEnum;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Illuminate\Contracts\Support\Htmlable;

class EditSubContent extends EditContent
{
    public Content|null $parentContent = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->parentContent = $this->getModel()::find(request()->route('parent'));
    }

    public function getBreadcrumbs(): array
    {
        return [
            ContentResource::getUrl('index') => 'Contents',
            ContentResource::getUrl('edit', [
                'record' => $this->parentContent->getKey()
            ]) => $this->parentContent->title,
            'Edit Sub-Content'
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Edit {$this->record->title}";
    }
}
