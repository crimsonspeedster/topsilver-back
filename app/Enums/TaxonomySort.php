<?php

namespace App\Enums;

enum TaxonomySort: string

{
    case NEWEST = 'newest';
    case OLDEST = 'oldest';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';
    case SELLING = 'selling';
}
