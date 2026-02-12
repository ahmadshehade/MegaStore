# 🛍️ MegaStore — Modular Multi-Vendor E-Commerce Platform

<div align="center">
<img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white">
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white">
<img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white">
<img src="https://img.shields.io/badge/REST_API-009688?style=for-the-badge&logo=fastapi&logoColor=white">
</div>

**A production-grade, modular multi-vendor eCommerce platform built with Laravel.**

---

## 📸 Project Preview
<p align="center"><i>✨ Elegant multi-vendor architecture with complete separation of concerns</i></p>



┌─────────────────────────────────────────────┐
│ MegaStore Platform │
├─────────────┬─────────────┬───────────────┬────────────────┤
│ Core │ Product │ Order │ Payment │
│ Module │ Management │ Management │ Management │
├─────────────┼─────────────┼───────────────┼────────────────┤
│ • Auth/2FA │ • Products │ • Orders │ • Invoices │
│ • Roles │ • Variants │ • Items │ • Payments │
│ • Users │ • Attributes│ • Discounts │ • Ledger │
│ • Policies │ • Categories│ • Reviews │ • Refunds │
└─────────────┴─────────────┴───────────────┴────────────────┘


---

## ✨ Features

| 🛒 Multi-Vendor Catalog | 📦 Smart Order Management |
|------------------------|--------------------------|
| Products with variants & attributes | Multi-seller order splitting |
| Category management | Discount engine & history |
| SKU-based inventory | Order lifecycle tracking |
| Media & gallery support | Product reviews & approval |

| 💰 Financial Suite | 🎯 Role-Based Dashboards |
|-------------------|------------------------|
| Complete invoice lifecycle | SuperAdmin analytics |
| Payment processing & refunds | Seller KPIs & revenue |
| Double-entry ledger system | Customer order history |
| Seller payout calculations | Real-time metrics |

---

## 📦 Modules Overview

### 🎯 Core Module
Authentication (Laravel Fortify), 2FA, role & permission management (Spatie), base models, global requests, and centralized policies.

### 📱 Product Management
Complete product catalog with variants, attributes, categories, and media. Designed for scalability and multi-vendor isolation.

### 📋 Order Management
Advanced order processing with discounts, multi-item handling, and a full review approval system.

### 💳 Payment Management
Invoices, payments, refunds, and a robust double-entry ledger system for accurate financial tracking.

### 📊 Dashboards
Role-specific dashboards with cached metrics and real-time KPIs for Admin, Seller, and Customer.

---

## 🚀 Quick Start

### Prerequisites
```bash
PHP ≥ 8.1 | Composer | MySQL ≥ 5.7 | Redis (optional)

Installation
# Clone the repository
git clone https://github.com/yourusername/megastore.git
cd megastore

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Run migrations with seeders
php artisan migrate --seed

# Link storage
php artisan storage:link

# Optional: Start queue worker
php artisan queue:work

# Run the application
php artisan serve

📊 Database Schema
📁 Products & Variants
products
├── id, category_id, sku, name, slug
├── status, is_featured, created_by
└── meta (JSON)

product_variants
├── id, product_id, sku, price
├── stock_quantity, low_stock_threshold
└── weight, is_active

📁 Orders & Payments
orders
├── id, customer_id, total_amount
└── status, shipping_address

order_items
├── id, order_id, product_variant_id
├── unit_price, quantity, subtotal
└── meta (snapshot data)

invoices & payments
└── Full lifecycle with soft deletes

📁 Ledger System
ledger_entries
├── id, entry_type, amount
├── reference_type, reference_id
├── user_id, description
└── created_at (indexed)

🔌 API Endpoints
Method	Endpoint	Description	Auth
GET	/api/products	List all products	Public
POST	/api/products	Create product	Seller/Admin
GET	/api/orders	User orders	Customer
POST	/api/orders	Place order	Customer
GET	/api/seller/kpi	Seller dashboard	Seller
POST	/api/payments	Process payment	Customer
📈 Seller Dashboard KPIs
// Total Revenue (Seller Share)
$revenue = Payment::where('status', 'completed')
    ->whereHas('invoice.order.items.productVariant.product', fn($q) => $q->where('created_by', $sellerId))
    ->sum('amount');

// Recent Orders
$orders = Order::whereHas('items.productVariant.product', fn($q) => $q->where('created_by', $sellerId))
    ->with(['items.productVariant.product', 'customer', 'invoice'])
    ->latest()
    ->take(10)
    ->get();

// Low Stock Alerts
$lowStock = ProductVariant::whereHas('product', fn($q) => $q->where('created_by', $sellerId))
    ->where(function($q) {
        $q->whereNotNull('low_stock_threshold')
          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    })
    ->orWhere('stock_quantity', '<=', 5)
    ->with('product')
    ->get();

// Top Selling Products
$topProducts = DB::table('order_items')
    ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
    ->join('products', 'product_variants.product_id', '=', 'products.id')
    ->select(
        'products.id',
        'products.name',
        DB::raw('SUM(order_items.quantity) as total_quantity'),
        DB::raw('SUM(order_items.subtotal) as total_revenue')
    )
    ->where('products.created_by', $sellerId)
    ->groupBy('products.id', 'products.name')
    ->orderByDesc('total_quantity')
    ->limit(5)
    ->get();

🏗️ Architecture Best Practices

Security & Scoping

protected static function booted()
{
    static::addGlobalScope(new SellerScope);
}

public function view(User $user, Product $product)
{
    return $user->id === $product->created_by || $user->hasRole('admin');
}


Performance Optimization

Index frequently queried columns (created_by, status, created_at)

Eager load relations to prevent N+1

Cache KPIs & settings (TTL 5-15 mins)

Use chunk() for large datasets

Data Integrity

Store product snapshots in order_items.meta

Soft deletes for audit

Unique constraints (product_id + user_id for reviews)

🧪 Testing
# Run all tests
php artisan test

# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# Specific module
php artisan test --filter=ProductManagement

🤝 Contributing

Fork the repository

Create your feature branch (git checkout -b feature/amazing)

Commit your changes (git commit -m 'Add some amazing feature')

Push to the branch (git push origin feature/amazing)

Open a Pull Request

📄 License

This project is licensed under the MIT License — see the LICENSE file for details.

🙏 Acknowledgments

Laravel — The PHP framework for web artisans

Spatie — Permission & role management

Laravel Fortify — Authentication backend

<div align="center"><sub>Built with ❤️ for the Laravel community</sub><br><sub>© 2024 MegaStore. All rights reserved.</sub></div> ```
