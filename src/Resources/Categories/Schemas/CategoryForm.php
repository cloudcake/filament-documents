<?php

namespace Cloudcake\Documents\Resources\Categories\Schemas;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Livewire\RichCategory;
use Cloudcake\Documents\Livewire\Spacer;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(function ($record) {
                return [
                    TextInput::make('name')
                        ->inlineLabel()
                        ->columnSpanFull(),

                    Fieldset::make()->columns(1)
                        ->columnSpanFull()
                        ->contained(false)
                        ->components([
                            Toggle::make('published')
                                ->columnSpanFull()
                                ->belowLabel('Mark as published: Visible documentation content.')
                                ->afterLabel([
                                    Text::make('Not Published')
                                        ->badge()
                                        ->color('gray')
                                        ->visible(fn($get) => !$get('../published')),
                                ]),

                            Toggle::make('collapsible')
                                ->columnSpanFull()
                                ->belowLabel('Whether viewers may collapse the category in the navigation pane.'),

                            Toggle::make('featured')
                                ->columnSpanFull()
                                ->belowLabel('Mark as featured: Highlights references.'),

                            Toggle::make('private')
                                ->live()
                                ->columnSpanFull()
                                ->belowLabel('Mark as private based on criteria.'),

                            Select::make('gate.attribute')
                                ->live()
                                ->required()
                                ->visible(fn($get) => $get('private'))
                                ->label("Access Gate")
                                ->afterStateUpdated(fn($set, $state) => $set('gate.type', Document::getViewerAttributeReturnType($state)))
                                ->columnSpanFull()
                                ->options(fn() => Document::getAvailableViewerAttributes()),

                            Hidden::make('gate.type'),

                            Repeater::make('gate.values')
                                ->required()
                                ->label('Result equals one of')
                                ->simple(TextInput::make('value'))
                                ->addActionLabel('Add Possibility')
                                ->columnSpanFull()
                                ->visible(fn($get) => $get('gate.type') === 'array' || $get('gate.type') === 'string'),
                        ])
                ];
            });
    }
}
