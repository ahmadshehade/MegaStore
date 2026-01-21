<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice Updated</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

    @php
        $order = optional($invoice)->order;
        $customer = optional($order)->customer;
        $items = collect(optional($order)->items ?? []);
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:30px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#0d6efd;color:#ffffff;padding:20px 30px;">
                            <h2 style="margin:0;font-size:22px;">🧾 Invoice Updated</h2>
                            <p style="margin:6px 0 0;font-size:14px;opacity:.9;">
                                Invoice #{{ $invoice->invoice_number }}
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">

                            <p style="font-size:15px;color:#333;margin-bottom:20px;">
                                Hello <strong>{{ $customer->name ?? 'Customer' }}</strong>,
                                your invoice has been updated. Please review the details below.
                            </p>

                            <!-- Invoice Info -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse:collapse;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:8px;font-weight:bold;">Order ID</td>
                                    <td style="padding:8px;">#{{ $order->id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;font-weight:bold;">Status</td>
                                    <td style="padding:8px;">{{ strtoupper($invoice->status) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;font-weight:bold;">Issued At</td>
                                    <td style="padding:8px;">
                                        {{ optional($invoice->issued_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;font-weight:bold;">Paid At</td>
                                    <td style="padding:8px;">
                                        {{ optional($invoice->paid_at)->format('Y-m-d H:i') ?? 'Not Paid Yet' }}</td>
                                </tr>
                            </table>

                            <!-- Items -->
                            <h3 style="margin:20px 0 10px;font-size:16px;color:#0d6efd;">Order Items</h3>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse:collapse;font-size:14px;">
                                <thead>
                                    <tr style="background:#f1f5f9;">
                                        <th style="padding:8px;border:1px solid #eee;">Product</th>
                                        <th style="padding:8px;border:1px solid #eee;">Variant</th>
                                        <th style="padding:8px;border:1px solid #eee;">Price</th>
                                        <th style="padding:8px;border:1px solid #eee;">Qty</th>
                                        <th style="padding:8px;border:1px solid #eee;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @if($items->isEmpty())
                                        <tr>
                                            <td colspan="5" style="padding:12px;text-align:center;color:#777;">
                                                No items found
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($items as $item)
                                             @php
        $variant = $item->productVariant;
        $product = $variant?->product;

        $price = ($variant?->price ?? 0);
        $qty   = (int) ($item->quantity ?? 1);
        $line  = $item->subtotal;

        $calcSubtotal += $line;
    @endphp
                                            <tr>
                                                <td style="padding:8px;border:1px solid #eee;">
                                                    {{ $product->name ?? $item->product_name ?? '-' }}
                                                </td>
                                                <td style="padding:8px;border:1px solid #eee;">
                                                    {{ $variant->sku ?? '-' }}
                                                </td>
                                                <td style="padding:8px;border:1px solid #eee;">
                                                    {{ number_format($price?? 0, 2) }}
                                                </td>
                                                <td style="padding:8px;border:1px solid #eee;">
                                                    {{ $qty ?? 1 }}
                                                </td>
                                                <td style="padding:8px;border:1px solid #eee;">
                                                    {{ number_format($line, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                </tbody>
                            </table>

                            <!-- Totals -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                                <tr>
                                    <td align="right" style="padding:6px;font-weight:bold;">Subtotal:</td>
                                    <td align="right" style="padding:6px;">
                                        {{ number_format($invoice->subtotal ?? 0, 2) }} {{ $invoice->currency ?? 'USD' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding:6px;font-weight:bold;">Discount:</td>
                                    <td align="right" style="padding:6px;">
                                        {{ number_format($invoice->discount_amount ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding:6px;font-weight:bold;">Tax:</td>
                                    <td align="right" style="padding:6px;">
                                        {{ number_format($invoice->tax_amount ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding:6px;font-weight:bold;">Shipping:</td>
                                    <td align="right" style="padding:6px;">
                                        {{ number_format($invoice->shipping_amount ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding:10px;font-size:16px;font-weight:bold;">
                                        Total:
                                    </td>
                                    <td align="right"
                                        style="padding:10px;font-size:16px;font-weight:bold;color:#0d6efd;">
                                        {{ number_format($invoice->tot_amount, 2) }} {{ $invoice->currency ?? 'USD' }}
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <div style="text-align:center;margin-top:30px;">
                                <a href="{{ url('/invoices/' . $invoice->id) }}" style="background:#0d6efd;color:#fff;text-decoration:none;
          padding:12px 25px;border-radius:6px;font-weight:bold;">
                                    View Invoice
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8f9fa;padding:20px;text-align:center;font-size:12px;color:#6c757d;">
                            © {{ date('Y') }} MegaStore — This is an automated email
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
