<?php

namespace App\Http\Requests;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderInOneClickRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');
        $name = $this->input('name');
        $email = $this->input('email');
        $comment = $this->input('comment');

        $this->merge([
            'name' => $name ? trim($name) : null,
            'phone' => $phone ? $this->normalize_phone($phone) : null,
            'email' => $email ? strtolower(trim($email)) : null,
            'comment' => $comment ? trim($comment) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'regex:/^380\d{9}$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'comment' => ['nullable', 'string', 'max:500'],
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id'),
            ],
        ];
    }

    private function normalize_phone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (!str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $variantId = $this->input('variant_id');

                if (! $variantId) {
                    return;
                }

                $belongsToProduct = ProductVariant::query()
                    ->where('id', $variantId)
                    ->where('product_id', $this->input('product_id'))
                    ->exists();

                if (!$belongsToProduct) {
                    $validator->errors()->add(
                        'variant_id',
                        'The selected variant does not belong to the product.'
                    );
                }
            },
        ];
    }
}
