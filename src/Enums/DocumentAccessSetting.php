<?php

namespace Cloudcake\Documents\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentAccessSetting: string implements HasLabel, HasDescription
{
    case Root = 'access';
    case AuthRequirement = 'requirement';
    case AuthNameAttribute = 'writer_name_attribute';
    case AuthAllowLogin = 'allow_login';
    case AuthAllowRegister = 'allow_register';

    public function statePath(): string
    {
        if ($this === self::Root)
            return self::Root->value;

        return self::Root->value . '.' . $this->value;
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::AuthAllowLogin => 'Allow optional login',
            self::AuthAllowRegister => 'Allow optional registration',
            self::AuthRequirement => 'Access Mode',
            default => null,
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::AuthAllowLogin => "When enabled, viewers can sign in regardless of the access mode.",
            self::AuthAllowRegister => "When enabled, viewers can register regardless of the access mode.",
            default => null,
        };
    }

    public function getOptions(): array
    {
        return match ($this) {
            self::AuthRequirement => [
                DocumentAccessAuthRequirementSetting::None->value => DocumentAccessAuthRequirementSetting::None->getLabel(),
                DocumentAccessAuthRequirementSetting::Auth->value => DocumentAccessAuthRequirementSetting::Auth->getLabel(),
            ],
            default => []
        };
    }

    public function getHintIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::AuthNameAttribute,
            self::AuthRequirement => 'tabler-exclamation-circle',
            default => null,
        };
    }

    public function getHintTooltip(): string|Htmlable|null
    {
        return match ($this) {
            self::AuthNameAttribute => "The attribute on your authentication model that identifies your users, typically 'name', however if you use a custom column, you may use it. This data may be visible on your documentation, do not expose any private information you're uncomfortable sharing publicly.",
            self::AuthRequirement => "Controls whether or not the documentation is available to the public or authentication is required in order to access it.",
            default => null,
        };
    }
}