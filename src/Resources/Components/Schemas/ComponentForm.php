<?php

namespace Cloudcake\Documents\Resources\Components\Schemas;

use Cloudcake\Documents\Livewire\RichComponent;
use Cloudcake\Documents\Livewire\Spacer;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class ComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(function ($record) {
                $class = $record->type;

                return [
                    TextInput::make('name')->label('Name')->required(),

                    Group::make()
                        ->statePath('schema')
                        ->components($class::editorSchema($record->schema ?? [])),
                ];
            });
    }
}
