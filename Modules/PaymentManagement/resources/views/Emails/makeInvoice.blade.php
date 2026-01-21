<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>New Invoice</title>
  <style>
    body { margin:0; padding:0; background:#f4f6f8; font-family:"Helvetica Neue", Arial, sans-serif; color:#222; -webkit-text-size-adjust:none; }
    .wrap { width:100%; padding:30px 12px; box-sizing:border-box; }
    .card { max-width:720px; margin:0 auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,0.06); }
    .header { background:linear-gradient(90deg,#0d6efd,#2563eb); color:#fff; padding:22px 28px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .brand { display:flex; align-items:center; gap:12px; }
    .logo { width:48px; height:48px; border-radius:8px; background:#fff; color:#0d6efd; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; }
    .title { font-size:18px; margin:0; }
    .subtitle { margin:0; opacity:.92; font-size:13px; }
    .body { padding:28px; }
    .lead { color:#333; margin:0 0 18px 0; font-size:15px; line-height:1.5; }
    .grid { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
    .box { flex:1 1 220px; background:#fbfbff; border:1px solid #eef2ff; padding:12px 14px; border-radius:8px; }
    .box h6 { margin:0 0 6px 0; font-size:13px; color:#555; }
    .box p { margin:0; font-weight:700; font-size:15px; color:#0d6efd; }
    .items-table { width:100%; border-collapse:collapse; font-size:14px; margin-top:12px; }
    .items-table th, .items-table td { padding:10px 12px; border-bottom:1px solid #eef2f7; text-align:right; }
    .items-table thead th { background:#fbfdff; color:#445; font-weight:700; text-align:right; }
    .muted { color:#6b7280; font-size:13px; }
    .totals { width:100%; margin-top:18px; border-top:1px dashed #e6eefc; padding-top:16px; }
    .totals td { padding:6px 8px; font-size:14px; }
    .totals .label { color:#6b7280; text-align:right; }
    .totals .value { text-align:left; font-weight:800; color:#0d6efd; }
    .cta { text-align:center; margin-top:20px; }
    .btn { display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; font-weight:700; background:linear-gradient(90deg,#0d6efd,#2563eb); color:#fff; box-shadow:0 8px 18px rgba(13,110,253,0.15); }
    .footer { background:#fbfdff; padding:16px 20px; text-align:center; font-size:13px; color:#6b7280; border-top:1px solid #eef2f7; }
    @media screen and (max-width:520px){ .grid { flex-direction:column; } .header { flex-direction:column; align-items:flex-start; gap:8px; } }
  </style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <!-- Header -->
    <div class="header">
      <div class="brand" aria-hidden="true">
        <div class="logo">MS</div>
        <div>
          <h1 class="title" style="margin:0;">MegaStore</h1>
          <p class="subtitle" style="margin:2px 0 0 0;">New Invoice Created</p>
        </div>
      </div>

      <div style="text-align:left;">
        <p style="margin:0;font-weight:700;font-size:14px;">Invoice #</p>
        <p style="margin:6px 0 0 0;font-size:16px;font-weight:900;color:#fff;">
          {{ $invoice->invoice_number ?? '-' }}
        </p>
      </div>
    </div>

    <!-- Body -->
    <div class="body">
      <p class="lead">
        Hello {{ optional(optional($invoice)->order)->customer->name ?? 'Customer' }},
        a new invoice has been created for your order. Here are the details:
      </p>

      <!-- Summary boxes -->
      <div class="grid">
        <div class="box">
          <h6>Status</h6>
          <p style="color:
              @switch($invoice->status ?? '')
                  @case('paid') #16a34a @break
                  @case('issued') #0d6efd @break
                  @case('partial') #f59e0b @break
                  @case('cancelled') #ef4444 @break
                  @case('revised') #7c3aed @break
                  @default #6b7280
              @endswitch;">
            {{ strtoupper($invoice->status ?? '-') }}
          </p>
        </div>

        <div class="box">
          <h6>Total Amount</h6>
          <p>{{ number_format($invoice->tot_amount ?? 0,2) }} {{ $invoice->currency ?? 'USD' }}</p>
        </div>

        <div class="box">
          <h6>Issued At</h6>
          <p class="muted">{{ optional($invoice->issued_at)->format('Y-m-d H:i') ?? '-' }}</p>
        </div>
      </div>

      <!-- Order & Customer -->
      @php
        $order = optional($invoice)->order;
        $customer = optional($order)->customer;
        $discounts = collect(optional($order)->discounts ?? []);
        $items = collect(optional($order)->items ?? []);
      @endphp


      <div class="grid">
        <div class="box">
          <h6>Customer</h6>
          <p>{{ $customer->name ?? '-' }}</p>
          <p class="muted">{{ $customer->email ?? '-' }}</p>
        </div>
        <div class="box">
          <h6>Shipping Address</h6>
          <p>{{ $order->shipping_address ?? '-' }}</p>
        </div>
        <div class="box">
          <h6>Discounts Applied</h6>
          @if($discounts->isNotEmpty())
            @foreach($discounts as $discount)
              <p>{{ $discount->name ?? 'Discount' }}: {{ number_format($discount->amount ?? 0,2) }}</p>
            @endforeach
          @else
            <p class="muted">No discounts</p>
          @endif
        </div>
      </div>

      <!-- Items Table -->
      <div style="overflow:auto;">
        <table class="items-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Product</th>
              <th>Variant</th>
              <th>Attributes</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Line Total</th>
            </tr>
          </thead>
          <tbody>
            @php $calcSubtotal = 0; @endphp

            @if($items->isEmpty())
              <tr>
                <td colspan="7" style="text-align:center;color:#6b7280;">No items found</td>
              </tr>
            @else
          @foreach($invoice->order->items as $item)
    @php
        $variant = $item->productVariant;
        $product = $variant?->product;

        $price = ($variant?->price ?? 0);
        $qty   = (int) ($item->quantity ?? 1);
        $line  = $item->subtotal;

        $calcSubtotal += $line;
    @endphp

    <tr>
        <td>{{ $variant->sku ?? '-' }}</td>

        <td>{{ $product->name ?? '-' }}</td>

        <td>{{ $variant->sku ?? '-' }}</td>

        <td>
            @forelse($variant->attributes as $attr)
                <strong>{{ $attr->name }}</strong>
                @if(!empty($attr->pivot?->value))
                    : {{ $attr->pivot->value }}
                @endif
                @if(!$loop->last), @endif
            @empty
                —
            @endforelse
        </td>

        <td>{{ number_format($price, 2) }}</td>

        <td>{{ $qty }}</td>

        <td>{{ number_format($line, 2) }}</td>
    </tr>
@endforeach

            @endif
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="totals">
        <table>
          @php
            $subtotal = $invoice->subtotal ?? $calcSubtotal;
            $discount = $invoice->discount_amount ?? $discounts->sum('amount') ?? 0;
            $tax = $invoice->tax_amount ?? 0;
            $shipping = $invoice->shipping_amount ?? 0;
            $grand = $invoice->tot_amount ?? ($subtotal - $discount + $tax + $shipping);
          @endphp
          <tr><td class="label">Subtotal</td><td class="value">{{ number_format($subtotal,2) }} {{ $invoice->currency ?? 'USD' }}</td></tr>
          <tr><td class="label">Discount</td><td class="value">{{ number_format($discount,2) }}</td></tr>
          <tr><td class="label">Tax</td><td class="value">{{ number_format($tax,2) }}</td></tr>
          <tr><td class="label">Shipping</td><td class="value">{{ number_format($shipping,2) }}</td></tr>
          <tr style="border-top:8px solid transparent;"><td class="label">Total Paid</td><td class="value">{{ number_format($grand,2) }} {{ $invoice->currency ?? 'USD' }}</td></tr>
        </table>
      </div>

      <!-- CTA -->
      <div class="cta">
        <a class="btn" href="{{ url('/invoices/'.($invoice->id ?? $invoiceId ?? '')) }}" target="_blank">View Full Invoice</a>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      Thank you for using MegaStore — For assistance, contact support@megastore.example
    </div>

  </div>
</div>
</body>
</html>
