MegaStore — Modular Multi-Vendor Commerce Platform
Short description
MegaStore is a production-grade, modular multi-vendor eCommerce platform built with Laravel. It is organized into clear modules (ProductManagement, OrderManagement, PaymentManagement, Core) and provides features for product cataloging (variants & attributes), multi-seller order handling, invoicing and payments, ledger accounting, discounts, and role-aware dashboards (SuperAdmin, Seller, Customer).

Key Features
• Multi-vendor product catalog (Products, ProductVariants, Attributes, AttributeValues)
• Order lifecycle supporting items from multiple sellers
• Invoice & Payment lifecycle with Ledger entries (financial trail)
• Discount engine and history tracking
• Product reviews & approval workflow
• Role based visibility via Scopes & Policies (Seller, Customer, SuperAdmin)
• RESTful API (auth via Sanctum) and modular Service layer

Modules
• Core — Auth (Fortify), Users, Roles, 2FA, Base models, Global requests
• ProductManagement — Products, Variants, Attributes, Categories, Media
• OrderManagement — Orders, OrderItems, Discounts, OrderDiscountHistory, ProductReviews
• PaymentManagement — Invoices, Payments, Refunds, LedgerEntries, PaymentMethods
• Dashboards — Seller/Customer/Admin summary services & controllers

Quick Install (development)
git clone <repo>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# optional

php artisan queue:work

Database (Short schema summary)
Main tables (core fields):
• products: id, category_id, sku, name, slug, status, is_featured, meta, created_by, timestamps
• product_variants: id, product_id, sku, price, stock_quantity, low_stock_threshold, weight, is_active, timestamps
• attributes, attribute_values, variant_values (pivot)
• categories
• discounts, order_discounts, order_discount_history
• orders: id, customer_id, tot_amount, status, shipping_address, timestamps
• order_items: id, order_id, product_variant_id, unit_price, quantity, subtotal, meta, timestamps
• invoices, payments (softDeletes), ledger_entries
• product_reviews: with unique(product_id, user_id)

Eloquent relations (summary)
• User → hasMany Product (created_by), has roles via Spatie.
• Product → hasMany ProductVariant, belongsTo Category, hasMany ProductReview.
• ProductVariant → belongsTo Product, belongsToMany Attribute via variant_values.
• Order → belongsTo User (customer), hasMany OrderItem, hasOne Invoice, belongsToMany Discount.
• OrderItem → belongsTo ProductVariant, belongsTo Order.
• Invoice → belongsTo Order, hasMany Payment and LedgerEntry.
• Payment → belongsTo Invoice, hasMany LedgerEntry.
• LedgerEntry → belongsTo Order|Invoice|Payment|Refund and User (customer).

Seller Dashboard — recommended content & queries
KPIs
• total_revenue (seller share)
• total_orders
• pending_orders
• low_stock_count
• avg_rating
Example queries (Eloquent)
Last 10 orders containing seller products:
$orders = Order::whereHas('items.productVariant.product', function($q) use ($sellerId) {
    $q->where('created_by', $sellerId);
})
->with(['items.productVariant.product','customer','invoice'])
->latest()
->take(10)
->get();
Low stock variants:
$lowStock = ProductVariant::whereHas('product', fn($q) => $q->where('created_by', $sellerId))
    ->where(function($q){
$q->whereNotNull('low_stock_threshold')
          ->whereColumn('stock_quantity','<=','low_stock_threshold');
    })->orWhere('stock_quantity','<=',5)
    ->with('product')
    ->get();
Seller revenue (payments completed):
$revenue = Payment::where('status', 'completed')
->whereHas('invoice.order.items.productVariant.product', fn($q) => $q->where('created_by', $sellerId))
    ->sum('amount');
Top selling products (DB query):
$top = DB::table('order_items')
->join('product_variants','order_items.product_variant_id','product_variants.id')
->join('products','product_variants.product_id','products.id')
->select('products.id','products.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_sales'))
->where('products.created_by', $sellerId)
->groupBy('products.id','products.name')
->orderByDesc('total_qty')
->limit(5)
->get();

Financial model & ledger
• Every invoice/payment/refund should create ledger entries with entry_type (EntryType enum).
• Use ledger_entries as the source of truth for accounting reports (debit/credit).
• For seller payouts, either compute from order_items.subtotal (faster) or reconcile via ledger_entries (more precise).

Best Practices & recommendations
• Use scopeVisibleFor in queries to enforce data visibility for roles.
• Snapshot product data (name, sku, price) inside order_items.meta when order is created.
• Add DB indexes on frequently filtered columns (products.created_by, product_variants.product_id, order_items.product_variant_id, orders.customer_id, payments.invoice_id).
• Eager load relations to avoid N+1 queries (e.g. with('items.productVariant.product','invoice','customer')).
• Cache heavy aggregates for short durations (1-5 minutes).
• Use soft deletes for invoices/payments when needed; maintain audit trail.
• Tests: Feature tests for Dashboard endpoints, Unit tests for Scopes and Services.
• Consider adding seller_id or target_user_id to ledger entries if you want direct seller accounting.

MegaStore — منصة تجارة إلكترونية متعددة البائعين (مبنية على Laravel)
نبذة مختصرة
MegaStore هو نظام تجارة إلكترونية متكامل ومنظم بموديولات: إدارة المنتجات، الطلبات، المدفوعات، ونظام لوحات التحكم. يدعم النظام منتجات بعدة متغيرات (variants)، نظام خصومات، فواتير ومدفوعات، وسجل محاسبي (ledger) لتتبع العمليات المالية. يوفر تحكم صلاحيات متقدم ويعزل بيانات كل بائع بحسب النطاق (scopes & policies).

الميزات الأساسية
• كتالوج مرن: Products + ProductVariants + Attributes + AttributeValues
• معالجة الطلبات والتي قد تحتوي عناصر من عدة بائعين
• دورة فواتير (Invoice) ومدفوعات (Payment) مع قيود دفترية (LedgerEntries)
• محرك خصومات وتسجيل تاريخ تطبيق الخصم
• مراجعات المنتجات ونظام الموافقة
• صلاحيات ومجالات رؤية مبنية على الأدوار (SuperAdmin, Seller, Customer)
• API RESTful محمية (Sanctum) وطبقة Services للمنطق التجاري

الوحدات (Modules)
• Core: المصادقة (Fortify)، المستخدمون، الأدوار، المصادقة الثنائية، BaseModel، Requests موحّدة
• ProductManagement: المنتجات، المتغيرات، السمات، الأقسام، الوسائط
• OrderManagement: الطلبات، عناصر الطلب، الخصومات، سجل خصم الطلب
• PaymentManagement: الفواتير، المدفوعات، المرتجعات، قيود الدفتر، طرق الدفع
• Dashboards: خدمات ومتحكمات لعرض KPIs لكل دور

تركيب سريع (بيئة التطوير)
git clone <repo>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# اختياري

php artisan queue:work

ملخص الجداول الأساسية
• products: الحقول الأساسية (id, category_id, sku, name, slug, status, is_featured, meta, created_by, timestamps)
• product_variants: (id, product_id, sku, price, stock_quantity, low_stock_threshold, weight, is_active)
• attributes, attribute_values, variant_values
• categories
• discounts, order_discounts, order_discount_history
• orders: (id, customer_id, tot_amount, status, shipping_address)
• order_items: (id, order_id, product_variant_id, unit_price, quantity, subtotal, meta)
• invoices, payments (مع softDeletes)، ledger_entries
• product_reviews مع قيد فريد (product_id, user_id)

علاقات Eloquent المهمة
• المستخدم (User) يملك منتجات (created_by)؛ ويملك أدوار عبر Spatie.
• المنتج (Product) له Variants والتقييمات وينتمي للقسم.
• المتغير (ProductVariant) ينتمي إلى المنتج ويرتبط بالسمات عبر pivot variant_values.
• الطلب (Order) يملك عناصر (OrderItem) ويملك فاتورة (Invoice) ويمكنه الربط مع خصومات.
• عنصر الطلب (OrderItem) يشير إلى ProductVariant.
• الفاتورة (Invoice) مرتبطة بالأمر (Order) وتملك مدفوعات وسجلات دفترية.
• القيود الدفترية (LedgerEntry) تربط الأحداث المالية بالـ order/invoice/payment/refund و المستخدم.
