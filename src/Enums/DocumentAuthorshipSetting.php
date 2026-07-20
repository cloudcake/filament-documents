<?php

namespace Cloudcake\Documents\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentAuthorshipSetting: string implements HasLabel, HasDescription
{
    case Root = 'authorship';
    case ShowWriter = 'display_writer';
    case ShowEditor = 'display_editor';

    public function statePath(): string
    {
        if ($this === self::Root)
            return self::Root->value;

        return self::Root->value . '.' . $this->value;
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ShowWriter => 'Show the writer name in the document.',
            self::ShowEditor => 'Show the editor name in the document.',
            default          => null,
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return null;
    }
}