<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\ProductReviews;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\ProductReview;
use Modules\ProductManagement\Models\Product;

class StoreProductReviewRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', ProductReview::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],
            'rating' => [
                'required',
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

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.integer'  => 'Product ID must be a valid integer.',
            'product_id.exists'   => 'The selected product does not exist.',

            'order_id.required'   => 'Order is required.',
            'order_id.integer'    => 'Order ID must be a valid integer.',
            'order_id.exists'     => 'The selected order does not exist.',

            'rating.required'     => 'Rating is required.',
            'rating.integer'      => 'Rating must be a number.',
            'rating.between'      => 'Rating must be between :min and :max.',

            'comment.string'      => 'Comment must be a string.',
            'comment.min'         => 'Comment must be at least :min characters.',
            'comment.max'         => 'Comment may not be greater than :max characters.',
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'order_id'   => 'order',
            'rating'     => 'rating',
            'comment'    => 'comment',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $order = Order::with('items.productVariant')->find($this->input('order_id'));
            if (!$order) {
                $v->errors()->add('order_id', 'Order not found.');
                return;
            }

            if ($order->customer_id != $this->user()->id) {
                $v->errors()->add('order_id', 'This order does not belong to you.');
            }

            $product = Product::find($this->input('product_id'));
            if (!$product) {
                $v->errors()->add('product_id', 'Product not found.');
                return;
            }
            $productInOrder = $order->items()
                ->whereHas('productVariant', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->exists();


            if (!$productInOrder) {
                $v->errors()->add('product_id', 'This product is not part of the order.');
            }
        });
    }
}
