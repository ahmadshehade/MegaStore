# MegaStore — Modular Multi-Vendor Commerce Platform | منصة MegaStore — منصة تجارة إلكترونية متعددة البائعين

---

## English Version

### Short Description

**MegaStore** is a production-grade, modular multi-vendor eCommerce platform built with **Laravel**. It is organized into clear modules (ProductManagement, OrderManagement, PaymentManagement, Core) and provides features for product cataloging (variants & attributes), multi-seller order handling, invoicing & payments, ledger accounting, discounts, and role-aware dashboards (SuperAdmin, Seller, Customer).

### Key Features

- Multi-vendor product catalog (Products, ProductVariants, Attributes, AttributeValues)
- Order lifecycle supporting items from multiple sellers
- Invoice & Payment lifecycle with Ledger entries (financial trail)
- Discount engine and history tracking
- Product reviews & approval workflow
- Role-based visibility via Scopes & Policies (Seller, Customer, SuperAdmin)
- RESTful API (auth via Sanctum) and modular Service layer

### Modules

- **Core** — Auth (Fortify), Users, Roles, 2FA, Base Models, Global Requests
- **ProductManagement** — Products, Variants, Attributes, Categories, Media
- **OrderManagement** — Orders, OrderItems, Discounts, OrderDiscountHistory, ProductReviews
- **PaymentManagement** — Invoices, Payments, Refunds, LedgerEntries, PaymentMethods
- **Dashboards** — Seller/Customer/Admin summary services & controllers

### Quick Install (Development)

```bash
git clone <repo>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# optional
php artisan queue:work


Database (Short Schema Summary)

Main Tables (Core Fields):

products: id, category_id, sku, name, slug, status, is_featured, meta, created_by, timestamps

product_variants: id, product_id, sku, price, stock_quantity, low_stock_threshold, weight, is_active, timestamps

attributes, attribute_values, variant_values (pivot)

categories

discounts, order_discounts, order_discount_history

orders: id, customer_id, tot_amount, status, shipping_address, timestamps

order_items: id, order_id, product_variant_id, unit_price, quantity, subtotal, meta, timestamps

invoices, payments (softDeletes), ledger_entries

product_reviews (unique constraint: product_id + user_id)

Eloquent Relations (Summary)

User → hasMany Products (created_by), roles via Spatie

Product → hasMany ProductVariants, belongsTo Category, hasMany ProductReviews

ProductVariant → belongsTo Product, belongsToMany Attribute via variant_values

Order → belongsTo User (customer), hasMany OrderItems, hasOne Invoice, belongsToMany Discounts

OrderItem → belongsTo ProductVariant, belongsTo Order

Invoice → belongsTo Order, hasMany Payments & LedgerEntries

Payment → belongsTo Invoice, hasMany LedgerEntries

LedgerEntry → belongsTo Order|Invoice|Payment|Refund and User (customer)

Seller Dashboard — Recommended KPIs & Queries

KPIs:

Total Revenue (seller share)

Total Orders

Pending Orders

Low Stock Count

Average Rating

Example Queries (Eloquent):

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


Seller revenue (completed payments):

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

Financial Model & Ledger

Every invoice/payment/refund creates ledger entries (entry_type enum)

Ledger entries are the source of truth for accounting reports

Seller payouts can be computed via order_items.subtotal or reconciled via ledger_entries for precision

Best Practices & Recommendations

Use scopeVisibleFor in queries to enforce role-based data visibility

Snapshot product data (name, sku, price) inside order_items.meta

Add DB indexes: products.created_by, product_variants.product_id, order_items.product_variant_id, orders.customer_id, payments.invoice_id

Eager load relations (with('items.productVariant.product','invoice','customer')) to prevent N+1 queries

Cache heavy aggregates (1–5 min)

Soft delete invoices/payments when needed; maintain audit trail

Tests: Feature tests for dashboards, Unit tests for Scopes & Services

Consider adding seller_id or target_user_id to ledger entries for direct seller accounting





نبذة مختصرة

MegaStore هو نظام تجارة إلكترونية متكامل ومنظم بموديولات: إدارة المنتجات، الطلبات، المدفوعات، ونظام لوحات التحكم. يدعم النظام منتجات بعدة متغيرات (Variants)، خصومات، فواتير ومدفوعات، وسجل محاسبي لتتبع العمليات المالية. يوفر تحكم صلاحيات متقدم وعزل بيانات كل بائع بحسب الدور (Scopes & Policies).

الميزات الأساسية

كتالوج منتجات متعدد البائعين (Products, ProductVariants, Attributes, AttributeValues)

معالجة الطلبات التي قد تحتوي عناصر من عدة بائعين

دورة فواتير ومدفوعات مع قيود دفترية (LedgerEntries)

محرك خصومات وتسجيل تاريخ التطبيق

مراجعات المنتجات ونظام الموافقة

صلاحيات ومجالات رؤية مبنية على الأدوار (SuperAdmin, Seller, Customer)

API RESTful محمية (Sanctum) وطبقة Services للمنطق التجاري

الوحدات (Modules)

Core: المصادقة (Fortify)، المستخدمون، الأدوار، المصادقة الثنائية، BaseModel، Requests موحّدة

ProductManagement: المنتجات، المتغيرات، السمات، الأقسام، الوسائط

OrderManagement: الطلبات، عناصر الطلب، الخصومات، سجل خصم الطلب

PaymentManagement: الفواتير، المدفوعات، المرتجعات، قيود الدفتر، طرق الدفع

Dashboards: خدمات ومتحكمات لعرض KPIs لكل دور

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

products: id, category_id, sku, name, slug, status, is_featured, meta, created_by, timestamps

product_variants: id, product_id, sku, price, stock_quantity, low_stock_threshold, weight, is_active

attributes, attribute_values, variant_values (pivot)

categories

discounts, order_discounts, order_discount_history

orders: id, customer_id, tot_amount, status, shipping_address, timestamps

order_items: id, order_id, product_variant_id, unit_price, quantity, subtotal, meta, timestamps

invoices, payments (مع softDeletes), ledger_entries

product_reviews مع قيد فريد (product_id + user_id)

العلاقات المهمة (Eloquent)

User: يملك منتجات (created_by)، ويملك أدوار عبر Spatie

Product: له Variants، ينتمي لقسم، ويملك مراجعات

ProductVariant: ينتمي إلى المنتج، يرتبط بالسمات عبر pivot variant_values

Order: يملك عناصر (OrderItems)، فاتورة (Invoice)، ويمكن ربط خصومات

OrderItem: يشير إلى ProductVariant

Invoice: مرتبط بالطلب، يملك مدفوعات وسجلات دفترية

LedgerEntry: يربط الأحداث المالية بالطلب / الفاتورة / المدفوعات / المرتجعات والمستخدم

لوحة تحكم البائع — KPIs واستعلامات

KPIs:

إجمالي الإيرادات (حصته من الطلبات)

عدد الطلبات الكلي

الطلبات المعلقة

عدد المنتجات منخفضة المخزون

متوسط التقييمات

مثال استعلامات:

// آخر 10 طلبات للبائع
$orders = Order::whereHas('items.productVariant.product', fn($q) => $q->where('created_by', $sellerId))
->with(['items.productVariant.product','customer','invoice'])
->latest()->take(10)->get();

// المنتجات منخفضة المخزون
$lowStock = ProductVariant::whereHas('product', fn($q) => $q->where('created_by', $sellerId))
->where(fn($q) => $q->whereNotNull('low_stock_threshold')->whereColumn('stock_quantity','<=','low_stock_threshold'))
->orWhere('stock_quantity','<=',5)->with('product')->get();

// إيرادات البائع (المدفوعات المكتملة)
$revenue = Payment::where('status', 'completed')
->whereHas('invoice.order.items.productVariant.product', fn($q) => $q->where('created_by', $sellerId))->sum('amount');

// أفضل المنتجات مبيعًا
$top = DB::table('order_items')
->join('product_variants','order_items.product_variant_id','product_variants.id')
->join('products','product_variants.product_id','products.id')
->select('products.id','products.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_sales'))
->where('products.created_by', $sellerId)
->groupBy('products.id','products.name')->orderByDesc('total_qty')->limit(5)->get();

الملاحظات المالية

كل فاتورة/مدفوعات/مرتجعات تولّد قيود دفترية

قيود الدفتر هي المصدر الرئيسي للتقارير المالية

يمكن حساب مستحقات البائع عبر order_items.subtotal أو عبر ledger_entries لمزيد من الدقة

أفضل الممارسات

استخدام scopeVisibleFor لحماية استعلامات كل دور

حفظ snapshot للمنتج عند إنشاء الطلب (order_items.meta)

إضافة مؤشرات (Indexes) للأعمدة المستخدمة بكثرة

استخدام Eager Loading لتجنب N+1

تخزين مؤقت للعمليات الحسابية الثقيلة

soft delete للفواتير والمدفوعات مع الحفاظ على سجل التدقيق

كتابة اختبارات Feature وUnit للتحقق من البيانات والخدمات

التفكير في إضافة seller_id أو target_user_id في ledger entries للحساب المباشر للبائع
```
