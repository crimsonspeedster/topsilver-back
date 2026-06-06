<?php
namespace App\Http\Requests;

use App\Enums\ShippingMethods;
use App\Models\ShippingMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    protected ?ShippingMethod $shippingMethod = null;

    protected function shippingMethod(): ?ShippingMethod
    {
        if ($this->shippingMethod) {
            return $this->shippingMethod;
        }

        return $this->shippingMethod = ShippingMethod::find(
            $this->input('shipping_method_id')
        );
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $first_name = $this->input('first_name');
        $last_name = $this->input('last_name');
        $middle_name = $this->input('middle_name');
        $phone = $this->input('phone');
        $email = $this->input('email');
        $notes = $this->input('notes');

        $this->merge([
            'first_name' => $first_name ? trim($first_name) : null,
            'last_name' => $last_name ? trim($last_name) : null,
            'middle_name' => $middle_name ? trim($middle_name) : null,
            'phone' => $phone ? $this->normalize_phone($phone) : null,
            'email' => $email ? strtolower(trim($email)) : null,
            'notes' => $notes ? trim($notes) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'phone' => ['required', 'regex:/^(\+?380)\d{9}$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')
                    ->where('active', true),
            ],
            'shipping_method_id' => [
                'required',
                'integer',
                Rule::exists('shipping_methods', 'id')
                    ->where('active', true),
            ],
            'shop_id' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::LOCAL_PICKUP,
                ['required', 'integer', 'exists:shops,id'],
                ['nullable']
            ),
            'np_warehouse_ref' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_WAREHOUSE,
                ['required', 'string', 'exists:np_warehouses,ref'],
                ['nullable']
            ),
            'np_city_ref' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_WAREHOUSE,
                ['required', 'string', 'exists:np_cities,ref'],
                ['nullable']
            ),
            'np_street_ref' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_COURIER,
                ['required', 'string'],
                ['nullable']
            ),
            'np_street_name' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_COURIER,
                ['required', 'string'],
                ['nullable']
            ),
            'np_locality_ref' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_COURIER,
                ['required', 'string'],
                ['nullable']
            ),
            'np_locality_name' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_COURIER,
                ['required', 'string'],
                ['nullable']
            ),
            'np_house_number' => Rule::when(
                fn () => $this->shippingMethod()?->type === ShippingMethods::NOVA_POSHTA_COURIER,
                ['required', 'integer'],
                ['nullable']
            ),
            'np_apartment_number' => [
                'nullable',
                'string',
            ]
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
}
