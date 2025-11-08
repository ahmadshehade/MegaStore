<?php

namespace Modules\OrderManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\OrderItem;
use Modules\ProductManagment\Models\Product;

class OrderService extends BaseService
{

    protected $order;

    use CacheTrait;
    /**
     * Summary of __construct
     * @param \Modules\OrderManagement\Models\Order $order
     */
    public function __construct(Order $order, OrderItem $orderItem)
    {
        parent::__construct($order);

    }


    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user ? $user->id . '_' . implode(',', $user->roles->pluck('name')->toArray()) : 'guest';
        $cacheKey = "orders_{$userKey}" . (empty($filters) ? "" : "_" . md5(json_encode($filters)));

        return Cache::tags(['orders'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
            return parent::getAll($filters);
        });
    }


    /**
     * Summary of store
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            $orderPayload = [
                'user_id' => Auth::user()->id,
                'payment_method_id' => $data['payment_method_id'],
                'shipping_id' => $data['shipping_id'],
                'notes' => $data['notes'],
                'tot_amount' => 0,
                'status' => $data['status'] ?? 'pending',
            ];

            if (empty($orderPayload['user_id']) || empty($orderPayload['payment_method_id']) || empty($orderPayload['shipping_id'])) {
                throw ValidationException::withMessages(['order' => 'Missing required order fields.']);
            }

            $order = parent::store($orderPayload);

            if (!empty($data['items']) && is_array($data['items'])) {
                $items = $data['items'];
            } elseif (!empty($data['product_id']) && isset($data['quantity'])) {
                $items = [
                    ['product_id' => $data['product_id'], 'quantity' => (int) $data['quantity']]
                ];
            } else {
                throw ValidationException::withMessages(['items' => 'No order items provided.']);
            }

            $orderTotal = '0.00';

            foreach ($items as $index => $it) {
                $productId = $it['product_id'] ?? null;
                $qty = isset($it['quantity']) ? (int) $it['quantity'] : 0;
                if (!$productId || $qty <= 0) {
                    throw ValidationException::withMessages(["items.$index" => 'Invalid product_id or quantity.']);
                }
                $product = Product::where('id', $productId)->lockForUpdate()->first();
                if (!$product) {
                    throw (new ModelNotFoundException)->setModel(Product::class, $productId);
                }
                $available = $product->stock ?? $product->stack ?? 0;

                if ($available < $qty) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" => "Not enough stock for product {$productId}. Available: {$available}"
                    ]);
                }
                $price = $product->price;
                $lineTotal = bcmul((string) $price, (string) $qty, 2);
                $order->items()->create([
                    'product_id' => $product->id,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $lineTotal,
                ]);
                if (property_exists($product, 'stock') || isset($product->stock)) {
                    $product->decrement('stock', $qty);
                } else {
                    $product->decrement('stack', $qty);
                }

                $orderTotal = bcadd((string) $orderTotal, (string) $lineTotal, 2);
            }

            $order->update(['tot_amount' => $orderTotal]);

            $this->cacheFlush('orders');

            return $order->load('items.product');
        }, 5);
    }
    /**
     * Summary of get
     * @param mixed $order
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function get(Model $order): Model
    {
        return $order->load(['items', 'shipping', 'paymetMethod']);
    }


    /**
     * Summary of update
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $order
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $data, Model $order): Model
    {
        return DB::transaction(function () use ($data, $order) {
            $existingItems = $order->items()->get();
            $orderPayload = [
                'payment_method_id' => $data['payment_method_id'] ?? $order->payment_method_id,
                'shipping_id' => $data['shipping_id'] ?? $order->shipping_id,
                'notes' => $data['notes'] ?? $order->notes,
                'status' => $data['status'] ?? $order->status,
            ];
            $order = parent::update($orderPayload, $order);
            if (!array_key_exists('items', $data)) {
                $orderTotal = '0.00';
                foreach ($existingItems as $ei) {
                    $orderTotal = bcadd((string) $orderTotal, (string) $ei->total, 2);
                }
                $order->update(['tot_amount' => $orderTotal]);
                return $order->load('items.product');
            }
            $items = $data['items'];
            if (!is_array($items)) {
                throw ValidationException::withMessages(['items' => 'Items must be an array.']);
            }
            $existingProductIds = $existingItems->pluck('product_id')->unique()->values()->all();
            if (!empty($existingProductIds)) {
                $productsMap = Product::whereIn('id', $existingProductIds)->lockForUpdate()->get()->keyBy('id');
                foreach ($existingItems as $ei) {
                    $prod = $productsMap->get($ei->product_id);
                    if ($prod) {
                        if (isset($prod->stock) || property_exists($prod, 'stock')) {
                            $prod->increment('stock', $ei->quantity);
                        } else {
                            $prod->increment('stack', $ei->quantity);
                        }
                    }
                }
            }
            $order->items()->delete();
            if (empty($items)) {
                $order->update(['tot_amount' => '0.00']);
                return $order->load('items.product');
            }
            $newProductIds = collect($items)->pluck('product_id')->unique()->values()->all();
            $products = Product::whereIn('id', $newProductIds)->lockForUpdate()->get()->keyBy('id');
            $orderTotal = '0.00';
            foreach ($items as $index => $it) {
                $productId = $it['product_id'] ?? null;
                $qty = isset($it['quantity']) ? (int) $it['quantity'] : 0;

                if (!$productId || $qty <= 0) {
                    throw ValidationException::withMessages(["items.$index" => 'Invalid product_id or quantity.']);
                }
                $product = $products->get($productId);
                if (!$product) {
                    throw (new ModelNotFoundException)->setModel(Product::class, $productId);
                }
                $available = $product->stock ?? $product->stack ?? 0;
                if ($available < $qty) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" => "Not enough stock for product {$productId}. Available: {$available}"
                    ]);
                }
                $price = $product->price;
                $lineTotal = bcmul((string) $price, (string) $qty, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $lineTotal,
                ]);

                if (isset($product->stock) || property_exists($product, 'stock')) {
                    $product->decrement('stock', $qty);
                } else {
                    $product->decrement('stack', $qty);
                }

                $orderTotal = bcadd((string) $orderTotal, (string) $lineTotal, 2);
            }


            $order->update(['tot_amount' => $orderTotal]);

            $this->cacheFlush('orders');
            return $order->load('items.product');
        }, 5);
    }


    /**
     * Summary of destroy
     * @param \Illuminate\Database\Eloquent\Model $order
     * @return bool
     */
    public function destroy(Model $order): bool
    {
        return DB::transaction(function () use ($order) {
            $items = $order->items()->get();
            if ($items->isNotEmpty()) {
                $productIds = $items->pluck('product_id')->unique()->values()->all();
                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                foreach ($items as $item) {
                    $product = $products->get($item->product_id);
                    if (!$product) {
                        continue;
                    }
                    if (isset($product->stock) || property_exists($product, 'stock')) {
                        $product->increment('stock', (int) $item->quantity);
                    } else {
                        $product->increment('stack', (int) $item->quantity);
                    }
                }
                $order->items()->delete();
            }

            $deleted = $order->delete();
            if (!$deleted) {
                throw new Exception('Failed to delete order.');
            }
           $this->cacheFlush('orders');
            return true;
        }, 5);

    }


}
