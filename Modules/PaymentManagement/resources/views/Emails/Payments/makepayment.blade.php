<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Payment Created</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:40px 0;">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#198754; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:22px;">
                                New Payment Received
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333;">
                            <p style="font-size:16px;">Hello,</p>

                            <p style="font-size:15px; line-height:1.6;">
                                A new payment has been successfully recorded in the system.
                                Below are the payment details:
                            </p>

                            <!-- Payment Info -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="margin:25px 0; border-collapse:collapse;">

                                <tr>
                                    <td style="padding:10px; background:#f8f9fa; font-weight:bold; width:40%;">
                                        Payment ID
                                    </td>
                                    <td style="padding:10px; background:#f8f9fa;">
                                        #{{ $payment->id }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Invoice ID
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        #{{ optional($payment->invoice)->id }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Order ID
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        #{{ optional($payment->invoice?->order)->id }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Amount
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        {{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        SubPament
                                    </td>
                                    @php
                                        $sub = $payment->invoice->payments->sum('amount'); // مجموع المدفوعات
                                        $totalInvoice = $payment->invoice->tot_amount;     // المبلغ الكلي للفاتورة
                                        $remaining = $totalInvoice - $sub;                // المبلغ المتبقي
                                    @endphp

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Invoice Total
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        {{ number_format($totalInvoice, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Total Paid
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        {{ number_format($sub, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Remaining Amount
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        {{ number_format($remaining, 2) }}
                                    </td>
                                </tr>


                                @php
                                    $status = optional($payment->invoice)->status;
                                    $statusColor = $status === 'paid' ? '#198754' : '#fd7e14';
                                @endphp

                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                        Invoice Status
                                    </td>
                                    <td style="padding:10px; border-top:1px solid #e9ecef;">
                                        <strong style="color:{{ $statusColor }};">
                                            {{ ucfirst($status ?? 'N/A') }}
                                        </strong>
                                    </td>
                                </tr>

                            </table>

                            <p style="font-size:14px; color:#555; line-height:1.6;">
                                This email was generated automatically after the payment was created.
                            </p>

                            <p style="margin-top:30px; font-size:14px;">
                                Regards,<br>
                                <strong>{{ config('app.name') }}</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f1f3f5; padding:15px; text-align:center; font-size:12px; color:#777;">
                            © {{ date('Y') }} {{ config('app.name') }} — Financial System Notification
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
