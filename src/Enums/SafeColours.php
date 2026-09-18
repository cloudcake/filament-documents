<?php

namespace Cloudcake\Documents\Enums;

enum SafeColours : string
{
    case Vanilla = 'gray';
    case Success = 'success';
    case Informative = 'info';
    case Critical = 'danger';
    case Warning = 'warning';
}