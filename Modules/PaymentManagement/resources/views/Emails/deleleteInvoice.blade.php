<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Deleted</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding: 40px 0;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">

                <!-- Header -->
                <tr>
                    <td style="background:#dc3545; padding:20px; text-align:center; color:#ffffff;">
                        <h1 style="margin:0; font-size:22px;">Invoice Deleted</h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333333;">
                        <p style="font-size:16px; margin-bottom:20px;">
                            Hello,
                        </p>

                        <p style="font-size:15px; line-height:1.6;">
                            This email is to inform you that the following invoice has been
                            <strong style="color:#dc3545;">deleted</strong> from the system.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px 0; border-collapse:collapse;">
                            <tr>
                                <td style="padding:10px; background:#f8f9fa; font-weight:bold; width:40%;">
                                    Invoice ID
                                </td>
                                <td style="padding:10px; background:#f8f9fa;">
                                    #{{ $inovice_id }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                    Order ID
                                </td>
                                <td style="padding:10px; border-top:1px solid #e9ecef;">
                                    #{{ $order_id }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px; border-top:1px solid #e9ecef; font-weight:bold;">
                                    Status
                                </td>
                                <td style="padding:10px; border-top:1px solid #e9ecef;">
                                    <span style="color:#dc3545; font-weight:bold;">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <p style="font-size:14px; line-height:1.6; color:#555;">
                            If this action was not intended or you believe this was a mistake,
                            please contact the system administrator immediately.
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
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
