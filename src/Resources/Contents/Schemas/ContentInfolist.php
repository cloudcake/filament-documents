<?php

namespace Cloudcake\Documents\Resources\Contents\Schemas;

use Filament\Schemas\Schema;

class ContentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

            ]);
    }
}
