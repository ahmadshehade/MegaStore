<?php

namespace Modules\OrderManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\OrderManagement\Http\Requests\Api\V1\Order\StoreOrderRequest;
use Modules\OrderManagement\Http\Requests\Api\V1\Order\UpdateOrderRequest;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Services\OrderService;

class OrderController extends Controller
{
    protected $order;

    use AuthorizesRequests;
    /**
     * Summary of __construct
     * @param \Modules\OrderManagement\Services\OrderService $order
     */
    public function __construct(OrderService $order)
    {
        $this->order = $order;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny',Order::class);
        $filters = $request->only(['payment_method_id', 'user_id', 'status', 'notes']);
        $data = $this->order->getAll($filters);
        return $this->SuccessMessage(['data' => $data], 'Successfully Get All Orders.', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $this->authorize('create',Order::class);
        $order = $this->order->store($request->validated());
        return $this->SuccessMessage(['data' => $order], 'Successfully Make New Order .', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order = $this->order->get($order);
        return $this->SuccessMessage(
            ['data' => $order]
            ,
            'Successfully Get Order .',
            200
        );
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);
        $data = $this->order->update($request->validated(), $order);
        return $this->SuccessMessage(['data' => $data], 'SuccessFully Update Order .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        $data = $this->order->destroy($order);
        return $this->SuccessMessage(['data' => true], 'Successfully Delete Order.', 200);
    }
}
