<?php

namespace Cloudcake\Documents\Enums;

enum SafeColours : string
{
    case Vanilla = 'gray';
    case Primary = 'primary';
    case Success = 'success';
    case Informative = 'info';
    case Critical = 'danger';
    case Warning = 'warning';
}