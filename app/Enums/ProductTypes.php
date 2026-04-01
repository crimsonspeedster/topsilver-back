<?php

namespace App\Enums;

enum ProductTypes : string
{
    case Simple = 'simple';
    case Variation = 'variation';
    case Variable = 'variable';
}
