<?php

namespace Cloudcake\Documents\Resources\Categories\Actions;

use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;

class CreateCategoryAction extends CreateAction
{
        public static function make(?string $name = null): static
        {
            return parent::make($name)
                ->modal()
                ->modalWidth(Width::Medium)
                ->createAnother(false);
        }
}