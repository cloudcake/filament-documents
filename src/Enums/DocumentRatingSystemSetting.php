<?php

namespace Cloudcake\Documents\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentRatingSystemSetting: string implements HasLabel, HasDescription
{
    case Root = 'rating';
    case Enabled = 'enabled';
    case ShowRatingCount = 'show_rating_count';
    case AllowAnonymous = 'anonymous';
    case AccumulativeMode = 'accumulative';
    case Options = 'options';

    public function statePath(): string
    {
        if ($this === self::Root)
            return self::Root->value;

        return self::Root->value . '.' . $this->value;
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Enabled          => "Enable Rating System",
            self::ShowRatingCount  => "Display rating count",
            self::AllowAnonymous   => "Allow anonymous ratings",
            self::AccumulativeMode => "Accumulative mode",
            self::Options          => "Rating options",
            default                => null,
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::Enabled          => "When enabled, viewers can rate content.",
            self::ShowRatingCount  => "When enabled, the number of ratings is displayed to all viewers the content page.",
            self::AllowAnonymous   => "When enabled, anonymous ratings are allowed.",
            self::AccumulativeMode => "Upon rating, lower options will be also be highlighted. When disabled, only the selected rating is highlighted. Note when this is enabled, your options should be ranked in ascending order of their respective values.",
            self::Options          => "The rating options available to viewers.",
            default                => null,
        };
    }
}