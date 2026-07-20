<?php

namespace Cloudcake\Documents\Enums;

enum RenderState
{
    case OK;
    case NoContent;
    case NotFound;
    case NotAllowed;
}