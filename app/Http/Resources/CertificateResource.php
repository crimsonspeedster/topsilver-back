<?php
namespace App\Http\Resources;

use App\Models\Certificate;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Certificate
 */

class CertificateResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'code' => $this->code,
            'value' => $currency->format($this->value)->format(),
        ];
    }
}
