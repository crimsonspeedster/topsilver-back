<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PaginationSchema",
    type: "object"
)]
class PaginationSchema
{
    #[OA\Property(example: 10)]
    public int $total_pages;

    #[OA\Property(example: 1)]
    public int $current_page;

    #[OA\Property(example: 15)]
    public int $per_page;

    #[OA\Property(example: 150)]
    public int $total_items;

    #[OA\Property(example: true)]
    public bool $has_more_pages;
}
