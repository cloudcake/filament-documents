<?php

namespace Cloudcake\Documents\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentAccessAuthRequirementSetting: string implements HasLabel
{
    case None = 'none';
    case Auth = 'auth';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::None => 'Public Access',
            self::Auth => 'Authenticated Access',
        };
    }
}