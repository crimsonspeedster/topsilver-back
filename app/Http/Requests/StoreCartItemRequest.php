<?php
namespace App\Http\Requests;

use App\Models\Bundle;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreCartItemRequest extends FormRequest
{
    private ?Model $entity = null;
    private array $allowedEntities = [
        'product' => Product::class,
        'bundle' => Bundle::class,
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('entity_type');
        $id = $this->input('entity_id');

        if (!isset($this->allowedEntities[$type])) {
            return;
        }

        $class = $this->allowedEntities[$type];

        $this->entity = $class::find($id);
    }

    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'in:product,bundle'],
            'entity_id' => ['required', 'integer'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->entity) {
                $validator->errors()->add('entity_id', 'Invalid entity.');

                return;
            }

            $variantId = $this->input('product_variant_id');
            if (
                $this->entity instanceof Product &&
                $this->entity->variants()->exists() &&
                !$variantId
            ) {
                $validator->errors()->add(
                    'product_variant_id',
                    'This product requires selecting a variant.'
                );
            }
        });
    }
}
