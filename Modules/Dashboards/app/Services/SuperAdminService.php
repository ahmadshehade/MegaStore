<?php

namespace Modules\Dashboards\Services;

use Modules\OrderManagement\Models\Discount;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\ProductReview;
use Modules\PaymentManagement\Models\LedgerEntry;
use Modules\PaymentManagement\Models\Payment;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

class SuperAdminService
{
    /**
     * Summary of getDashboard
     * @return array{expiring_discounts: \Illuminate\Database\Eloquent\Collection<int, TModel>|\Illuminate\Support\Collection<int, \stdClass>, featured_products: \Illuminate\Support\Collection<int, \stdClass>, inactive_products_count: int, ledger_balance: float|int, low_stock_variants: \Illuminate\Support\Collection<int, \stdClass>, pending_reviews: \Illuminate\Database\Eloquent\Collection<int, ProductReview>|\Illuminate\Support\Collection<int, \stdClass>, recent_inactive_products: \Illuminate\Database\Eloquent\Collection<int, TModel>|\Illuminate\Support\Collection<int, \stdClass>, recent_orders: \Illuminate\Support\Collection<int, \stdClass>, recent_payments: \Illuminate\Support\Collection<int, \stdClass>, total_products: int}
     */
    public function getDashboard(): array
    {
        return [
            'total_products' => Product::count(),
            'inactive_products_count' => Product::where('status', '!=', 'active')->count(),
            'recent_inactive_products' => $this->getInActiveProducts(),
            'featured_products' => $this->getProducts(),
            'low_stock_variants' => $this->getLowStockVariants(),
            'recent_orders' => $this->getRecentOrders(),
            'recent_payments' => $this->getRecentPayments(),
            'pending_reviews' => $this->getPendingReviews(),
            'expiring_discounts' => $this->getExpiringDiscounts(),
            'ledger_balance' => $this->getLedgerBalance(),
        ];
    }

    /**
     * Summary of getProducts
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getProducts()
    {
        return Product::with(['productVariants', 'reviews'])
            ->where('is_featured', true)
            ->take(10)
            ->get();
    }

    /**
     * Summary of getInActiveProducts
     * @return \Illuminate\Database\Eloquent\Collection<int, TModel>|\Illuminate\Support\Collection<int, \stdClass>
     */
    public function getInActiveProducts()
    {
        return Product::where('status', '!=', 'active')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Summary of getLowStockVariants
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getLowStockVariants()
    {
        return ProductVariant::where(function($q){
            $q->whereNotNull('low_stock_threshold')
              ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        })->orWhere('stock_quantity', '<=', 5)
          ->with('product')
          ->take(10)
          ->get();
    }

    /**
     * Summary of getRecentOrders
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getRecentOrders()
    {
        return Order::with('customer')
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Summary of getRecentPayments
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getRecentPayments()
    {
        return Payment::with(['invoice', 'customer'])
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Summary of getPendingReviews
     * @return \Illuminate\Database\Eloquent\Collection<int, ProductReview>|\Illuminate\Support\Collection<int, \stdClass>
     */
    public function getPendingReviews()
    {
        return ProductReview::where('is_approved', false)
            ->latest()
            ->take(10)
            ->with('product', 'user')
            ->get();
    }

    /**
     * Summary of getExpiringDiscounts
     * @return \Illuminate\Database\Eloquent\Collection<int, TModel>|\Illuminate\Support\Collection<int, \stdClass>
     */
    public function getExpiringDiscounts()
    {
        return Discount::whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->take(10)
            ->get();
    }

    /**
     * Summary of getLedgerBalance
     * @return float|int
     */
    public function getLedgerBalance()
    {
        $debits = LedgerEntry::sum('debit');
        $credits = LedgerEntry::sum('credit');
        return $debits - $credits;
    }
}
