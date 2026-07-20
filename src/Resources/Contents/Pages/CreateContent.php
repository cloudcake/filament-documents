<?php

namespace Cloudcake\Documents\Resources\Contents\Pages;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Pages\Settings;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected static bool $canCreateAnother = false;
}
