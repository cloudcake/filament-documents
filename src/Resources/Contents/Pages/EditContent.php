<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use Cloudcake\Documents\Resources\Contents\ContentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Tabs\Tab;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
