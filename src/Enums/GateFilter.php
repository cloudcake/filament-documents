<?php

namespace Cloudcake\Documents\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum GateFilter: string implements HasLabel, HasIcon, HasDescription
{
    public const string StatePath = 'gate.filter';
    public const string Label = 'Display';

    case Display = 'show';
    case Unlisted = 'unlist';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Display  => 'tabler-eye-spark',
            self::Unlisted => 'tabler-shield-off',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Display  => 'Show Restricted',
            self::Unlisted => 'Unlisted',
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::Display  => 'Display the link, but prevent accessing the content with a message.',
            self::Unlisted => 'Hide the link, but allow accessing the content with the direct link.',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->flatMap(fn($case) => [
                $case->value => "
                    <div class=\"flex flex-col\">
                        <span class=\"font-medium text-gray-900 dark:text-white\">{$case->getLabel()}</span>
                        <span class=\"text-xs text-gray-500 dark:text-gray-400\">{$case->getDescription()}</span>
                    </div>"
            ])->toArray();
    }
}