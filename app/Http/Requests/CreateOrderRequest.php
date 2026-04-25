<?php
namespace App\Http\Requests;

use App\Enums\ShippingMethods;
use App\Models\ShippingMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
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
            'phone' => $phone ? preg_replace('/\D/', '', $phone) : null,
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
            'phone' => ['required', 'regex:/^380\d{9}$/'],
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
            'shop_id' => [
                Rule::requiredIf(function () {
                    $shippingMethodId = $this->input('shipping_method_id');
                    $shippingMethod = ShippingMethod::find($shippingMethodId);

                    return $shippingMethod?->type === ShippingMethods::LOCAL_PICKUP;
                }),
                'integer',
                'exists:shops,id'
            ],
            'np_warehouse_ref' => [
                Rule::requiredIf(function () {
                    $shippingMethodId = $this->input('shipping_method_id');
                    $shippingMethod = ShippingMethod::find($shippingMethodId);

                    return $shippingMethod?->type === ShippingMethods::NOVA_POSHTA_WAREHOUSE;
                }),
                'string',
                'exists:np_warehouses,ref'
            ],
            'np_city' => [
                Rule::requiredIf(function () {
                    $shippingMethodId = $this->input('shipping_method_id');
                    $shippingMethod = ShippingMethod::find($shippingMethodId);

                    return $shippingMethod?->type === ShippingMethods::NOVA_POSHTA_COURIER;
                }),
                'string',
            ],
            'np_street' => [
                Rule::requiredIf(function () {
                    $shippingMethodId = $this->input('shipping_method_id');
                    $shippingMethod = ShippingMethod::find($shippingMethodId);

                    return $shippingMethod?->type === ShippingMethods::NOVA_POSHTA_COURIER;
                }),
                'string',
            ],
            'np_house_number' => [
                Rule::requiredIf(function () {
                    $shippingMethodId = $this->input('shipping_method_id');
                    $shippingMethod = ShippingMethod::find($shippingMethodId);

                    return $shippingMethod?->type === ShippingMethods::NOVA_POSHTA_COURIER;
                }),
                'integer',
            ],
            'np_apartment_number' => [
                'nullable',
                'string',
            ]
        ];
    }
}
