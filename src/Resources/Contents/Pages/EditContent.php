<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use BackedEnum;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabIcon(): string|BackedEnum|Htmlable|null
    {
        return 'tabler-pencil';
    }
}
