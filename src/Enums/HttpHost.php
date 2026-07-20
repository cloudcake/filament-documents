<?php

namespace Cloudcake\Documents\Enums;

use BackedEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HttpHost: string implements HasColor, HasIcon, HasLabel, HasDescription
{
    case Current = 'current';
    case External = 'external';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::External => Color::Lime,
            default        => Color::Indigo,
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::External => 'tabler-http-get',
            default        => 'tabler-api',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Current  => 'Current',
            self::External => 'External',
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return 'test';
    }
}