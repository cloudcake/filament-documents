<?php

namespace Cloudcake\Documents\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentLayoutSetting: string implements HasLabel, HasDescription
{
    case Root = 'layout';
    case MaxContentWidth = 'max_content_width';
    case PrimaryColour = 'primary_colour';

    public function statePath(): string
    {
        if ($this === self::Root)
            return self::Root->value;

        return self::Root->value . '.' . $this->value;
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ShowTags => 'Display tags',
            default => null,
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::ShowTags => "When enabled, viewers will see tags at the title of the page.",
            default => null,
        };
    }
}