<?php

namespace Cloudcake\Documents\Enums;

use Cloudcake\Documents\Document;
use BackedEnum;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum GateRequirement: string implements HasLabel, HasIcon, HasDescription
{
    public const string StatePath = 'gate.requirement';
    public const string Label = 'Access Requirement';

    case None = 'none';
    case SimpleAuth = 'auth';
    case FunctionResult = 'func';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::None           => 'tabler-shield-check',
            self::SimpleAuth     => 'tabler-shield-check',
            self::FunctionResult => 'tabler-math-function',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::None           => "None",
            self::SimpleAuth     => "Simple Auth",
            self::FunctionResult => "Functional Evaluation",
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::None           => "This allows even guests to access this content unless the document is restricted to authenticated users only.",
            self::SimpleAuth     => "Only requires that the viewer is authenticated. The content will be invisible to viewers that are not authenticated.",
            self::FunctionResult => "Content is only visible to authenticated users where a specific function on the user model evaluates to true.",
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