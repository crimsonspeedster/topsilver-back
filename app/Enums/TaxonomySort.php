<?php

namespace App\Enums;

enum TaxonomySort: string

{
    case NEWEST = 'newest';
    case OLDEST = 'oldest';
    case ALPHA_ASC = 'alpha_asc';
    case ALPHA_DESC = 'alpha_desc';
}
