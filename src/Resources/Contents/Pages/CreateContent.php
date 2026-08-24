<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use Cloudcake\Documents\Resources\Contents\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected static bool $canCreateAnother = false;
}
