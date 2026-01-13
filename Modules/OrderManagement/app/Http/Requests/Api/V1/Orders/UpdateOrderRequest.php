<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Orders;

use App\Enum\UserRoles;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\OrderManagement\Models\Discount;
use Modules\ProductManagement\Models\ProductVariant as ModelsProductVariant;
use Modules\OrderManagement\Models\Order;

class UpdateOrderRequest extends BaseRequest
{
    public function authorize(): bool
    {

        return Gate::allows('update', $this->route('order'));
    }

    public function rules(): array
    {
        $user = $this->user();
        $rules = [
            'shipping_address' => ['sometimes', 'nullable', 'string'],
            'variants' => ['sometimes', 'array', 'min:1'],
            'variants.*' => ['required_with:variants', 'integer', 'min:1'],
            'meta' => ['sometimes', 'array'],
            'meta.*' => ['array'],
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

    public function messages(): array
    {
        return [

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
        ];
    }

    /**
     * Additional validation depending on DB/business rules.
     * For updates we consider the current quantity of the variant in the order
     * so that the client can replace quantities without being blocked by the current stock reservation.
     */
    public function withValidator(ValidatorContract $validator): void
    {
        $user=Auth::user();
        $validator->after(function (ValidatorContract $v)use($user) {

            /**
             * -----------------------------
             *   VALIDATE VARIANTS
             * -----------------------------
             */
            // if (!$this->has('variants')) {
            //     return;
            // }

            $variantsInput = $this->input('variants', []);


            // if (!is_array($variantsInput) || empty($variantsInput)) {
            //     $v->errors()->add('variants', 'No variant IDs provided.');
            //     return;
            // }

            // Limit variants count
            $maxVariants = 200;
            if (count($variantsInput) > $maxVariants) {
                $v->errors()->add('variants', "Too many variants. Maximum is {$maxVariants}.");
                return;
            }

            // Extract variant IDs
            $variantIds = array_map('intval', array_keys($variantsInput));

            // Fetch from DB
            $dbVariants = ModelsProductVariant::whereIn('id', $variantIds)
                ->select(['id', 'price', 'is_active', 'stock_quantity'])
                ->get()
                ->keyBy('id');

            // In case of update order
            $order = $this->route('order') instanceof Order ? $this->route('order') : null;

            foreach ($variantIds as $vid) {

                // Validate existence
                if (!isset($dbVariants[$vid])) {
                    $v->errors()->add("variants.{$vid}", "Variant with id {$vid} not found.");
                    continue;
                }

                // Quantity
                $quantity = (int) ($variantsInput[$vid] ?? 0);
                if ($quantity < 1) {
                    $v->errors()->add("variants.{$vid}", "Quantity must be at least 1.");
                    continue;
                }

                $variant = $dbVariants[$vid];

                // Active check
                if (!$variant->is_active) {
                    $v->errors()->add("variants.{$vid}", "Variant {$vid} is not active.");
                }

                // Price check
                if ($variant->price <= 0) {
                    $v->errors()->add("variants.{$vid}", "Variant {$vid} has invalid price.");
                }

                // Stock check (considering reserved items in same order)
                $currentQty = $order
                    ? (int)$order->items()->where('product_variant_id', $vid)->sum('quantity')
                    : 0;

                $available = $variant->stock_quantity + $currentQty;

                if ($available < $quantity) {
                    $v->errors()->add(
                        "variants.{$vid}",
                        "Not enough stock for variant {$vid}. Available: {$available}, Requested: {$quantity}."
                    );
                }
            }

            /**
             * -----------------------------
             *   VALIDATE DISCOUNTS
             * -----------------------------
             */
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

                $discounts = Discount::whereIn('id', $discountIds)->get();

                foreach ($discounts as $discount) {

                    if ($discount->start_date > now()) {
                        $v->errors()->add(
                            'discounts',
                            "Discount {$discount->id} is not started yet."
                        );
                    }

                    if ($discount->end_date < now()) {
                        $v->errors()->add(
                            'discounts',
                            "Discount {$discount->id} is expired."
                        );
                    }

                    if ($discount->status == 0) {
                        $v->errors()->add(
                            'discounts',
                            "Discount {$discount->id} is inactive."
                        );
                    }
                }
            }
        });
    }


    /**
     * Prepare/normalize input before validation.
     * - accept object { "5": 2 } or array-of-objects [{variant_id:5, quantity:2}, ...]
     * and convert to standard associative array 'variant_id' => quantity
     */
    protected function prepareForValidation(): void
    {
        $variants = $this->input('variants');


        if (is_array($variants) && !Arr::isAssoc($variants)) {
            $mapped = [];
            foreach ($variants as $item) {
                if (is_array($item) && isset($item['variant_id'])) {
                    $mapped[(int)$item['variant_id']] = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                }
            }
            if (!empty($mapped)) {
                $this->merge(['variants' => $mapped]);
                $variants = $mapped;
            }
        }


        if (is_object($variants)) {
            $this->merge(['variants' => (array) $variants]);
        }


        if (is_array($this->input('variants'))) {
            $normalized = [];
            foreach ($this->input('variants') as $k => $v) {
                $normalized[$k] = is_numeric($v) ? (int)$v : $v;
            }
            $this->merge(['variants' => $normalized]);
        }
    }
}
