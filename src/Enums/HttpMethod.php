<?php

namespace Cloudcake\Documents\Enums;

use BackedEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HttpMethod: string implements HasColor, HasIcon, HasLabel
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case PATCH = 'PATCH';
    case TRACE = 'TRACE';
    case QUERY = 'QUERY';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GET     => Color::Lime,
            self::PUT     => Color::Blue,
            self::DELETE  => Color::Red,
            self::HEAD    => Color::Green,
            self::OPTIONS => Color::Yellow,
            self::PATCH   => Color::Purple,
            default       => Color::Indigo,
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::GET     => 'tabler-http-get',
            self::PUT     => 'tabler-http-put',
            self::DELETE  => 'tabler-http-delete',
            self::HEAD    => 'tabler-http-head',
            self::OPTIONS => 'tabler-http-options',
            self::PATCH   => 'tabler-http-patch',
            self::POST    => 'tabler-http-post',
            default       => 'tabler-api',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }

    public function hasBody(): bool
    {
        return match ($this) {
            self::POST,
            self::PUT,
            self::PATCH,
            self::QUERY => true,
            default     => false,
        };;
    }
}