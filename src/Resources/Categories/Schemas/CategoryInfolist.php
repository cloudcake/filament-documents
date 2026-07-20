<?php

namespace Cloudcake\Documents\Resources\Categories\Schemas;

use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

            ]);
    }
}
