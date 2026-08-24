<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Illuminate\Contracts\Support\Htmlable;

class CreateSubContent extends CreateContent
{
    public Content|null $parentContent = null;

    public function mount(): void
    {
        parent::mount();

        $this->parentContent = static::getModel()::find(request()->route('parent'));
    }

    public function getBreadcrumbs(): array
    {
        return [
            ContentResource::getUrl('index') => 'Contents',
            ContentResource::getUrl('edit', [
                'record' => $this->parentContent->getKey()
            ]) => $this->parentContent->title,
            'Create Sub-Content'
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Create '{$this->parentContent->title}' Sub-Content";
    }

    protected function getRedirectUrl(): string
    {
        return ContentResource::getUrl('edit-sub-content', [
            'parent' => $this->record->parent,
            'record' => $this->record->getKey(),
        ]);
    }
}
