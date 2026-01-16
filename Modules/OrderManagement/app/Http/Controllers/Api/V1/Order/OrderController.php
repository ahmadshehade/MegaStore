<?php

namespace Modules\OrderManagement\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Modules\OrderManagement\Http\Requests\Api\V1\Orders\StoreOrderRequest;
use Modules\OrderManagement\Http\Requests\Api\V1\Orders\UpdateOrderRequest;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Notifications\OrderCreatedNotification;
use Modules\OrderManagement\Notifications\OrderDeleteNotification;
use Modules\OrderManagement\Notifications\OrderUpdateNotification;
use Modules\OrderManagement\Services\OrderService;

class OrderController extends Controller
{
    use AuthorizesRequests;

    protected    $orderService;

    /**
     * Summary of __construct
     * @param OrderService $orderService
     */
    public  function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);
        $filters = $request->only([
            'customer_id',
            'status',
            'shipping_address',
            'payment_method_id'
        ]);

        $orders = $this->orderService->getAll($filters);
        return  $this->SuccessMessage(['orders' => $orders], 'Successfully Get All  Orders .', 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $this->authorize('create', Order::class);
        $order = $this->orderService->store($request->validated());
        $admins = User::admins()->get();
        Notification::send($admins, new OrderCreatedNotification($order));
        return $this->SuccessMessage(
            ['order' => $order],
            'Successfully Make New Order',
            201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order = $this->orderService->get($order);
        return $this->SuccessMessage([
            'order' => $order,
        ], 'SuccessFully Get Order', 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);
        $order = $this->orderService->update($request->validated(), $order);
        $recipients = User::admins()->get()->push(Auth::user());
        Notification::send($recipients, new OrderUpdateNotification($order));
        return  $this->SuccessMessage(['order' => $order], 'Successfully Update Order', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        $orderId = $order->id;
        $status  = $order->status;
        $success = $this->orderService->destroy($order);
        if ($success) {
            $recipients = User::admins()->get()->push(Auth::user());

            Notification::send(
                $recipients,
                new OrderDeleteNotification($orderId, $status)
            );
        }
        return $this->SuccessMessage(
            ['success' => $success],
            'Successfully Deleted Order',
            200
        );
    }
}
