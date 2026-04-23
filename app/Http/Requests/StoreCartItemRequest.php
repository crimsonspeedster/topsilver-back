<?php
namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreCartItemRequest extends FormRequest
{
    private ?Product $product = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $productId = $this->input('product_id');

        $this->product = Product::withCount('variants')
            ->find($productId);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $productVariantId = $this->input('product_variant_id');

            if (!$this->product) {
                return;
            }

            if (
                $this->product->variants_count > 0 &&
                !$productVariantId
            ) {
                $validator->errors()->add(
                    'product_variant_id',
                    'This product requires selecting a variant.'
                );
            }
        });
    }
}
