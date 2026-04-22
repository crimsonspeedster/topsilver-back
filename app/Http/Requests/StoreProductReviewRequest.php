<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:product_reviews,id'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $rating = $this->input('rating');

            if (!$parentId && !$rating) {
                $validator->errors()->add('rating', 'Rating is required for review');
            }

            if ($parentId && $rating !== null) {
                $validator->errors()->add('rating', 'Rating is not allowed in replies');
            }
        });
    }
}
