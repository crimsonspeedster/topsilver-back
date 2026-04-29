<?php
namespace App\Http\Resources;

use App\Models\Bundle;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Bundle
 */

class BundleResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'old_price' => $this->old_price,
            'price' => $this->price,
            'price_formatted' => $currency->format($this->price)->format(),
            'old_price_formatted' => $this->old_price ? $currency->format($this->old_price)->format(): null,
            'items' => BundleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
