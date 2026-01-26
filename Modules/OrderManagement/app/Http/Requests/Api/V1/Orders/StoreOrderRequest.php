<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Orders;

use App\Enum\UserRoles;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\OrderManagement\Models\Order;
use Modules\ProductManagement\Models\ProductVariant as ModelsProductVariant;

class StoreOrderRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Expected payload shape (example):
     * {
     *   "payment_method_id": 1,
     *   "shipping_address": "address...",
     *   "variants": {
     *       "12": 2,
     *       "15": 1
     *   },
     *   "meta": {
     *       "12": { "color": "red" }
     *   }
     * }
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'shipping_address'  => ['nullable', 'string'],
            'variants'          => ['required', 'array', 'min:1'],
            'variants.*'        => ['required', 'integer', 'min:1'],
            'meta'              => ['nullable', 'array'],
            'meta.*'            => ['array'],
            'description' => ['sometimes', 'string', 'min:2', 'max:255'],
        ];
        if ($user && $user->hasRole(UserRoles::SuperAdmin->value)) {
            $rules['discounts']     = ['sometimes', 'array'];
            $rules['discounts.*']   = ['required', 'integer', 'min:1', 'exists:discounts,id'];
            $rules['status'] = [
                'sometimes',
                'string',
                Rule::in(['pending', 'processing', 'completed', 'cancelled']),
            ];
        }

        return $rules;
    }


    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [

            'variants.required' => 'At least one variant must be provided.',
            'variants.array' => 'Variants must be provided as an object/array of variant_id => quantity.',
            'variants.*.integer' => 'Variant quantities must be integers.',
            'variants.*.min' => 'Variant quantity must be at least 1.',
            'meta.*.array' => 'Each variant meta must be an object/array.',

            'discounts.array' => 'The discounts field must be a valid array.',


            'discounts.*.required' => 'Each discount entry is required.',
            'discounts.*.integer'  => 'Each discount ID must be an integer.',
            'discounts.*.min'      => 'Each discount ID must be at least 1.',
            'discounts.*.exists'   => 'One or more selected discounts do not exist.',

            'status.string' => 'The order status must be a valid string.',
            'status.in'     => 'The selected order status is invalid. Allowed values are: pending, processing, completed, or cancelled.',

            'description.string' => 'The Description Must Be String .',
            'description.min' => 'The Description Must Be Over 2 Cahracter .',
            'description.max' => 'The Description Must Be Under  255 .'
        ];
    }

    /**
     * Additional validation that depends on DB / business rules.
     * - ensure all variant ids exist
     * - ensure variants are active and have valid price
     * - optionally check current stock (note: final stock must be checked and decremented in transaction)
     */
    public function withValidator(Validator $validator): void
    {
        $user = Auth::user();
        $validator->after(function (Validator $v) use ($user) {
            // -----------------------------
            // VALIDATE VARIANTS
            // -----------------------------
            $variantsInput = $this->input('variants', []);
            $variantIds = array_map('intval', array_keys($variantsInput));
            if (empty($variantIds)) {
                $v->errors()->add('variants', 'No variant IDs provided.');
                return;
            }
            $dbVariants = ModelsProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
            foreach ($variantIds as $vid) {
                if (!isset($dbVariants[$vid])) {
                    $v->errors()->add('variants.' . $vid, "Variant with id {$vid} not found.");
                    continue;
                }
                $quantity = (int) ($variantsInput[$vid] ?? $variantsInput[(string)$vid] ?? 0);
                $variant = $dbVariants[$vid];

                if (!$variant->is_active) {
                    $v->errors()->add('variants.' . $vid, "Variant {$vid} is not active.");
                }
                if (!isset($variant->price) || $variant->price <= 0) {
                    $v->errors()->add('variants.' . $vid, "Variant {$vid} has invalid price.");
                }
                if ($variant->stock_quantity < $quantity) {
                    $v->errors()->add('variants.' . $vid, "Not enough stock for variant {$vid}. Available: {$variant->stock_quantity}, Requested: {$quantity}.");
                }
            }

            // -----------------------------
            // VALIDATE DISCOUNTS
            // -----------------------------
            $discountsInput = $this->input('discounts', null);

            if (!$user->hasRole(UserRoles::SuperAdmin->value) && !empty($discountsInput)) {
                $v->errors()->add('discounts', 'Discounts can be applied only by Admin.');
            }

            if ($this->has('discounts')) {
                $discountIds = $this->input('discounts', []);
                if (!is_array($discountIds)) {
                    $v->errors()->add('discounts', 'Discounts must be an array of IDs.');
                    return;
                }

                $discounts = \Modules\OrderManagement\Models\Discount::whereIn('id', $discountIds)->get();

                foreach ($discounts as $discount) {
                    if ($discount->start_date > now()) {
                        $v->errors()->add('discounts', "Discount {$discount->id} is not started yet.");
                    }
                    if ($discount->end_date < now()) {
                        $v->errors()->add('discounts', "Discount {$discount->id} is expired.");
                    }
                    if ($discount->status == 0) {
                        $v->errors()->add('discounts', "Discount {$discount->id} is inactive.");
                    }
                }
            }
        });
    }


    /**
     * Normalize / prepare data before validation if needed.
     * (We don't expect customer_id from client — controller sets it.)
     */
    protected function prepareForValidation(): void
    {

        $variants = $this->input('variants');
        if (is_object($variants)) {
            $this->merge(['variants' => (array) $variants]);
        }
    }
}
