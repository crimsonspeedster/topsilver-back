<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductsBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->ids;

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        $this->merge([
            'ids' => $ids,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            validator($this->all(), [
                'ids' => ['required', 'array', 'max:50'],
                'ids.*' => ['integer', 'exists:products,id'],
            ])->validate();
        });
    }
}
