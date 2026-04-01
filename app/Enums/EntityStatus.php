<?php

namespace App\Enums;

enum EntityStatus : string
{
    case Draft = 'draft';
    case Published = 'published';
}
