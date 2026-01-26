<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Overpayment Notification</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 640px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1f2937;
            color: #ffffff;
            padding: 16px 24px;
            font-size: 18px;
            font-weight: bold;
        }
        .content {
            padding: 24px;
            color: #374151;
            line-height: 1.6;
            font-size: 14px;
        }
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 16px;
            margin: 20px 0;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-row strong {
            display: inline-block;
            min-width: 130px;
            color: #111827;
        }
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #dc2626;
        }
        .footer {
            padding: 16px 24px;
            background-color: #f9fafb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        Overpayment Notification
    </div>

    <div class="content">
        <p>
            Dear {{ $order->customer->name ?? 'Customer' }},
        </p>

        <p>
            We would like to inform you that an <strong>overpayment</strong> has been detected
            on your order after applying discounts.
        </p>

        <div class="info-box">
            <div class="info-row">
                <strong>Order ID:</strong> #{{ $order->id }}
            </div>

            <div class="info-row">
                <strong>Invoice ID:</strong>
                #{{ $order->invoice->id ?? 'N/A' }}
            </div>

            <div class="info-row">
                <strong>Overpaid Amount:</strong>
                <span class="amount">
                    {{ number_format($overPayment, 2) }}
                </span>
            </div>
        </div>

        <p>
            This amount will be handled according to our refund or balance policy.
            If you have any questions, feel free to contact our support team.
        </p>

        <p>
            Thank you for your trust.
        </p>
    </div>

    <div class="footer">
        © {{ now()->year }} {{ config('app.name') }} — All rights reserved.
    </div>
</div>

</body>
</html>
