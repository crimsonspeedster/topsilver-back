<?php
namespace App\Http\Resources;

use App\Models\ProductReview;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductReview
 */

class ProductReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'comment' => $this->comment,
            'status' => $this->status,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'has_replies' => ($this->replies_count ?? 0) > 0,
        ];
    }
}
