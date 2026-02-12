<?php

namespace Modules\Dashboards\Services;

use Illuminate\Support\Facades\Auth;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\ProductReview as ModelsProductReview;
use Modules\PaymentManagement\Models\Payment;
use Modules\ProductManagement\Models\ProductReview;

class CustomerService
{
    protected $user;

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Summary of getDashboard
     * @return array{latest_orders: \Illuminate\Support\Collection<int, \stdClass>, notifications: \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Notifications\DatabaseNotification>, payments: \Illuminate\Database\Eloquent\Collection<int, TModel>|\Illuminate\Support\Collection<int, \stdClass>, reviews: \Illuminate\Support\Collection<int, \stdClass>, summary: array{pending_orders: int, total_orders: int, total_spent: mixed}}
     */
    public function getDashboard()
    {
        return [
            'summary' => $this->getSummary(),
            'latest_orders' => $this->getLatestOrders(),
            'payments' => $this->getLatestPayments(),
            'reviews' => $this->getLatestReviews(),
            'notifications' => $this->getNotifications(),
        ];
    }

    /**
     * Summary of getSummary
     * @return array{pending_orders: int, total_orders: int, total_spent: mixed}
     */
    protected function getSummary()
    {
        return [
            'total_orders' => Order::where('customer_id', $this->user->id)->count(),

            'total_spent' => Order::where('customer_id', $this->user->id)
                ->where('status', 'completed')
                ->sum('tot_amount'),

            'pending_orders' => Order::where('customer_id', $this->user->id)
                ->where('status', 'pending')
                ->count(),
        ];
    }


    /**
     * Summary of getLatestOrders
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    protected function getLatestOrders()
    {
        return Order::where('customer_id', $this->user->id)
            ->with(['items.productVariant.product'])
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Summary of getLatestPayments
     * @return \Illuminate\Database\Eloquent\Collection<int, TModel>|\Illuminate\Support\Collection<int, \stdClass>
     */
    protected function getLatestPayments()
    {
        return Payment::whereHas('invoice.order', function ($q) {
            $q->where('customer_id', $this->user->id);
        })
            ->latest()
            ->take(5)
            ->get();
    }
    /**
     * Summary of getLatestReviews
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    protected function getLatestReviews()
    {
        return ModelsProductReview::where('user_id', $this->user->id)
            ->with('product')
            ->latest()
            ->take(5)
            ->get();
    }
    /**
     * Summary of getNotifications
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    protected function getNotifications()
    {
        return $this->user->notifications()
            ->latest()
            ->take(5)
            ->get();
    }
}
