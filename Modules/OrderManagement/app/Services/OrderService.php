<?php

namespace Modules\OrderManagement\Services;

use App\Enum\UserRoles;
use App\Services\BaseService;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Auth\AuthenticationException;
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
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $data): Model
    {
        try {
            /**
             * @var /App/Models/User as $user
             */
            $user = Auth::user();
            if (isset($data['status'])) {

                if ($user->hasRole(UserRoles::SuperAdmin->value)) {
                    $payloadOrder['status'] = $data['status'];
                } else {
                    throw new AuthenticationException('Un Authorize To Add Status To Order .');
                }

            }
            $payloadOrder = [
                'user_id' => $user->id,
                'payment_method_id' => $data['payment_method_id'],
                'shipping_id' => $data['shipping_id'],
                'notes' => $data['notes'],
                'tot_amount' => 0,
                'address_id'=>$data['address_id'],
            ];
            return DB::transaction(function () use ($payloadOrder, $data) {
                $order = parent::store($payloadOrder);
                $items = $data['items'];
                if (empty($items)) {
                    throw new Exception('No order items provided.');
                }
                $orderTotal = 0.00;

                foreach ($items as $index => $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    if (!$productId || $quantity <= 0) {
                        throw new Exception("Invalid product_id or quantity at item index {$index}");
                    }
                    $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
                    if ($product->stock < $quantity) {
                        throw new Exception("Insufficient stock for product {$productId}");
                    }
                    $product->decrement("stock", $quantity);
                    $lineTotal = bcmul((string) $product->price, (string) $quantity, 2);
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'total' => $lineTotal,
                    ]);
                    $orderTotal = bcadd((string) $orderTotal, (string) $lineTotal, 2);
                }
                $order->update(['tot_amount' => $orderTotal]);
                 $this->cacheFlush('orders');
                return $order->load(['items.product','address']);
            }, 5);
        } catch (Exception $e) {
            Log::error("Fail To Make Order " . $e->getMessage());
            throw new Exception("Fail To Make Order " . $e->getMessage());
        }
    }



    /**
     * Summary of get
     * @param mixed $order
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function get(Model $order): Model
    {
        return $order->load(['items', 'shipping', 'paymetMethod', 'user','address']);
    }


    /**
     * Summary of update
     * @param array $data
     * @param mixed $order
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $data, $order): Model
    {
        try {
            $orderPayload = [
                'payment_method_id' => $data['payment_method_id'] ?? $order->payment_method_id,
                'shipping_id' => $data['shipping_id'] ?? $order->shipping_id,
                'notes' => $data['notes'] ?? $order->notes,
                'tot_amount'=>$order->tot_amount,
            ];

            if (!empty($data['status'])) {
                /**
                 * @var /App/Models/User as $user
                 */
                $user = Auth::user();
                if ($user->hasRole(UserRoles::SuperAdmin->value)) {
                    $orderPayload['status'] = $data['status'];
                } else {
                    throw new HttpResponseException(response()
                        ->json([
                            'message' => 'Not Authorize .'
                        ], 403));
                }
            }
            return DB::transaction(function () use ($data, $orderPayload, $order) {
                $newOrder = parent::update($data, $order);
                if (isset($data['items'])) {
                    $oldItems = $order->items()->get();
                    foreach ($oldItems as $item) {
                        $product = Product::where('id', $item->product_id)->LockForUpdate()->firstOrFail();
                        $oldLineQuiantitiy = $item->quantity;
                        $product->increment('stock', $oldLineQuiantitiy);
                        $item->delete();
                    }

                    $newItems = $data['items'];
                    $tot_amount = 0.00;
                    foreach ($newItems as $index => $item) {
                        $productId = $item['product_id'];
                        $quantity = $item['quantity'];
                        if (!$productId || $quantity <= 0) {
                            throw new Exception("Invalid product_id or quantity at item index {$index}");
                        }
                        $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
                        if ($product->stock < $quantity) {
                            throw new Exception("Insufficient stock for product {$productId}");
                        }
                        $product->decrement("stock", $quantity);
                        $lineTotal = bcmul((string) $product->price, (string) $quantity, 2);
                        $newOrder->items()->create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'price' => $product->price,
                            'total' => $lineTotal,
                        ]);
                        $tot_amount = bcadd((string) $tot_amount, (string) $lineTotal, 2);
                    }
                    $order->update(['tot_amount' => $tot_amount]);
                }
                $this->cacheFlush('orders');
                return $order->load('items.product','address');
            }, 5);
        } catch (Exception $e) {
            Log::error("Fail To Update Order " . $e->getMessage());
            throw new Exception("Fail To Update Order " . $e->getMessage());
        }
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
                        $product->increment('stock', (int) $item->quantity);
                    }
                }
                $order->items()->delete();
            }

            $deleted = parent::destroy($order);
            if (!$deleted) {
                throw new Exception('Failed to delete order.');
            }
            $this->cacheFlush('orders');
            return true;
        }, 5);
    }
}
