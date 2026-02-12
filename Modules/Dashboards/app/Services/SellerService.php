<?php

namespace Modules\Dashboards\Services;

use Illuminate\Support\Facades\Auth;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\ProductReview;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

class SellerService
{
    public function getDashboard($seller)
    {
        return [
            'otificatins'=>$this->getNotifications(),
            'summary'          => $this->getSummary($seller->id),
            'recent_orders'    => $this->getRecentOrders($seller->id),
            'top_products'     => $this->getTopProducts($seller->id),
            'low_stock'        => $this->getLowStock($seller->id),
            'recent_reviews'   => $this->getRecentReviews($seller->id),
        ];
    }

    // =============================
    // SUMMARY CARDS
    // =============================

    private function getSummary($sellerId)
    {
        $totalRevenue = Payment::where('status', 'completed')
            ->whereHas('invoice.order.items.productVariant.product', function ($q) use ($sellerId) {
                $q->where('created_by', $sellerId);
            })
            ->sum('amount');

        $totalOrders = Order::whereHas('items.productVariant.product', function ($q) use ($sellerId) {
            $q->where('created_by', $sellerId);
        })->count();

        $pendingOrders = Order::where('status', 'pending')
            ->whereHas('items.productVariant.product', function ($q) use ($sellerId) {
                $q->where('created_by', $sellerId);
            })->count();

        $lowStockCount = ProductVariant::whereHas('product', function ($q) use ($sellerId) {
            $q->where('created_by', $sellerId);
        })->where('stock_quantity', '<=', 5)->count();

        $avgRating = ProductReview::whereHas('product', function ($q) use ($sellerId) {
            $q->where('created_by', $sellerId);
        })->avg('rating');

        return [
            'total_revenue'   => $totalRevenue,
            'total_orders'    => $totalOrders,
            'pending_orders'  => $pendingOrders,
            'low_stock_count' => $lowStockCount,
            'average_rating'  => round($avgRating, 2),
        ];
    }

    // =============================
    // RECENT ORDERS
    // =============================

    private function getRecentOrders($sellerId)
    {
        return Order::whereHas('items.productVariant.product', function ($q) use ($sellerId) {
            $q->where('created_by', $sellerId);
        })
            ->with(['customer', 'invoice'])
            ->latest()
            ->take(10)
            ->get();
    }

    // =============================
    // TOP PRODUCTS
    // =============================

    private function getTopProducts($sellerId)
    {
        return DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->where('products.created_by', $sellerId)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
    }

    // =============================
    // LOW STOCK
    // =============================

    private function getLowStock($sellerId)
    {
        return ProductVariant::whereHas('product', function ($q) use ($sellerId) {
            $q->where('created_by', $sellerId);
        })
            ->where('stock_quantity', '<=', 5)
            ->with('product')
            ->get();
    }

    // =============================
    // RECENT REVIEWS
    // =============================

    private function getRecentReviews($sellerId)
    {
        return ProductReview::whereHas('product', function ($q) use ($sellerId) {
            $q->where('created_by', $sellerId);
        })
            ->with('user', 'product')
            ->latest()
            ->take(5)
            ->get();
    }


    /**
     * Summary of getNotifications
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    public function getNotifications()
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get();
    }
}
