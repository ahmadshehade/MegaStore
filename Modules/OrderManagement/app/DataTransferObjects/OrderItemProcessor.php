<?php

namespace Modules\OrderManagement\DataTransferObjects;

use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\OrderManagement\Models\Discount;
use Modules\OrderManagement\Models\OrderDiscountHistory;
use Modules\OrderManagement\Models\OrderItem;
use Modules\ProductManagement\Models\ProductVariant;

class OrderItemProcessor
{


    /**
     * Summary of processOrderItems
     * @param array $data
     * @param mixed $order
     * @throws HttpResponseException
     * @return float
     */
    public function processOrderItems(array $data, $order): float
    {
        if (!$order) {
            throw new HttpResponseException(response()->json(['message' => 'Order is required'], 422));
        }
        if ($order->items()->exists()) {
            foreach ($order->items as $oldItem) {
                ProductVariant::where('id', $oldItem->product_variant_id)
                    ->increment('stock_quantity', $oldItem->quantity);
            }
            $order->items()->delete();
        }

        if (empty($data['variants']) || !is_array($data['variants'])) {
            throw new HttpResponseException(response()->json(['message' => 'Variants are required'], 422));
        }

        $variantIds = array_map('intval', array_keys($data['variants']));
        $variants = ProductVariant::whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $itemsSubtotal = 0;
        $orderItems = [];

        foreach ($data['variants'] as $variantId => $quantity) {
            $variant = $variants[(int)$variantId] ?? null;
            if (!$variant) {
                throw new HttpResponseException(response()->json(['message' => "Variant {$variantId} not found"], 404));
            }

            $quantity = (int)$quantity;
            if ($variant->stock_quantity < $quantity) {
                throw new HttpResponseException(response()->json(['message' => "Not enough stock for variant {$variantId}"], 400));
            }

            $variant->decrement('stock_quantity', $quantity);

            $subtotal = bcmul((string)$variant->price, (string)$quantity, 2);
            $itemsSubtotal = bcadd((string)$itemsSubtotal, (string)$subtotal, 2);

            $snapshot = [
                'product_name' => $variant->product->name ?? null,
                'product_id' => $variant->product_id ?? null,
                'variant_id' => $variant->id,
                'variant_sku' => $variant->sku,
                'price_at_purchase' => $variant->price,
                'created_at' => now()->toDateTimeString()
            ];

            $orderItems[] = [
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'unit_price' => $variant->price,
                'quantity' => $quantity,
                'subtotal' => (float)$subtotal,
                'meta' => json_encode($snapshot),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        OrderItem::insert($orderItems);

        return (float)round($itemsSubtotal, 2);
    }



    /**
     * Summary of makeTotAmount
     * @param array $data
     * @throws HttpResponseException
     * @return float
     */
    public function makeTotAmount(array $data): float
    {
        if (empty($data['variants']) || !is_array($data['variants'])) {
            throw new HttpResponseException(response()->json(['message' => 'Variants are required'], 422));
        }

        $variantIds = array_keys($data['variants']);
        $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        $total = '0';

        foreach ($data['variants'] as $variantId => $quantity) {
            $variant = $variants[$variantId] ?? null;
            if (!$variant) {
                throw new HttpResponseException(response()->json(['message' => "Variant {$variantId} not found"], 404));
            }

            $subtotal = bcmul((string)$variant->price, (string)$quantity, 2);
            $total = bcadd($total, $subtotal, 2);
        }

        return (float)$total;
    }


    /**
     * Summary of syncDiscountsAndHistory
     * @param array $data
     * @param mixed $order
     * @param mixed $itemsSubtotal
     * @return void
     */
    public function syncDiscountsAndHistory(array $data, $order, $itemsSubtotal): void
    {
        $discountIds = $data['discounts'] ?? [];
        $order->discounts()->sync($discountIds);

        $discounts = Discount::whereIn('id', $discountIds)->get();
        foreach ($discounts as $discount) {
            OrderDiscountHistory::create([
                'order_id' => $order->id,
                'discount_id' => $discount->id,
                'discount_value' => $discount->value,
                'discount_type' => $discount->type,
                'total_amount_before_discount' => $itemsSubtotal,
                'applied_at' => now()
            ]);
        }
    }


    /**
     * Summary of operateToTAmount
     * @param mixed $order
     * @param array $data
     * @param mixed $itemsSubtotalOverride
     * @return float|int
     */
    public function processDiscount($order, array $data = [], $itemsSubtotalOverride = null): float
    {
        $itemsSubTotal = $itemsSubtotalOverride ?? $order->tot_amount;
        $discountIds = $data['discounts'] ?? $order->discounts()->pluck('discounts.id')->toArray();

        if (empty($discountIds)) {
            return round($itemsSubTotal, 2);
        }

        $discounts = Discount::whereIn('id', $discountIds)
            ->where('status', true)
            ->where(function ($q) {
                $now = now();
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) {
                $now = now();
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })->get();

        $currentTotal = (float)$itemsSubTotal;

        foreach ($discounts->where('type', 'percentage') as $discount) {
            $discountAmount = bcmul((string)$currentTotal, bcdiv((string)$discount->value, '100', 4), 2);
            $currentTotal -= (float)$discountAmount;
        }

        foreach ($discounts->where('type', 'fixed') as $discount) {
            $currentTotal -= (float)$discount->value;
        }

        return max(round($currentTotal, 2), 0);
    }
}
