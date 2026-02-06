<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\ProductReviews;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\OrderManagement\Models\ProductReview;

class UpdateProductReviewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'nullable',
                'integer',
                'between:1,5',
            ],
            'comment' => [
                'nullable',
                'string',
                'min:5',
                'max:255',
            ],
        ];
    }


    public function messages()
    {
        return [

            'rating.integer'      => 'Rating must be a number.',
            'rating.between'      => 'Rating must be between :min and :max.',

            'comment.string'      => 'Comment must be a string.',
            'comment.min'         => 'Comment must be at least :min characters.',
            'comment.max'         => 'Comment may not be greater than :max characters.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $productReview=$this->route('productReview');
        return Gate::allows('update', $productReview);
    }

    /**
     * Summary of attributes
     * @return array{comment: string, order_id: string, product_id: string, rating: string}
     */
    public function attributes(): array
    {
        return [

            'rating'     => 'rating',
            'comment'    => 'comment',
        ];
    }
}
