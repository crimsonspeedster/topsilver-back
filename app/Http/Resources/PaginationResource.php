<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationResource extends JsonResource
{
    public function __construct(private readonly LengthAwarePaginator $paginator)
    {
        parent::__construct($paginator);
    }

    public function toArray($request): array
    {
        return [
            'total_items' => $this->paginator->total(),
            'total_pages' => $this->paginator->lastPage(),
            'current_page' => $this->paginator->currentPage(),
            'per_page' => $this->paginator->perPage(),
            'has_more_pages' => $this->paginator->hasMorePages(),
        ];
    }
}
