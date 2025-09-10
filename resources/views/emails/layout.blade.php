<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meatday.shop</title>
</head>

<body style="margin:0; padding:0; background-color:#ffffff;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse; background-color:#ffffff;">
        <tr>
            <td align="center" style="padding:16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="620" style="width:620px; max-width:620px; border-collapse:collapse;">
                    <tr>
                        <td style="border:1px solid #eeeeee; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden;">

                            <!-- Header -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; background:#f44336; background-image: linear-gradient(135deg, #f44336, #c62828);">
                                <tr>
                                    <td align="center" style="padding:28px 24px;">
                                        <div style="font-family:Arial, sans-serif; font-size:26px; font-weight:800; color:#ffffff; letter-spacing:0.5px; text-transform:uppercase;">Meatday.shop</div>
                                        <div style="font-family:Arial, sans-serif; font-size:18px; font-weight:600; color:#ffffff; margin-top:8px; opacity:0.95;">@yield('subject','Meatday.shop')</div>
                                        <div style="margin-top:10px;">
                                            <span style="display:inline-block; background:#fdecea; color:#b71c1c; padding:6px 10px; border-radius:999px; font-family:Arial, sans-serif; font-size:11px;">Fresh Cuts • Fast Delivery</span>
                                            <span style="display:inline-block; background:#ffffff; color:#b71c1c; border:1px solid rgba(255,255,255,0.6); padding:6px 10px; border-radius:999px; font-family:Arial, sans-serif; font-size:11px; margin-left:8px;">100% Quality</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:24px; font-family:Arial, sans-serif; font-size:14px; line-height:1.6; color:#1a1a1a;">
                                        @yield('content')
                                        <p style="margin:18px 0 0 0; color:#374151;">Warm regards,<br><strong>Team Meatday.shop</strong></p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="height:1px; background:#f1f1f1; margin:0; padding:0;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Footer -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td align="center" style="padding:16px 24px 22px; font-family:Arial, sans-serif; font-size:12px; color:#6b7280;">
                                        &copy; {{ date('Y') }} Meatday.shop. All rights reserved.<br>
                                        <a href="#" style="color:#ef5350; text-decoration:none;">Privacy</a> •
                                        <a href="#" style="color:#ef5350; text-decoration:none;">Terms</a> •
                                        <a href="#" style="color:#ef5350; text-decoration:none;">Contact</a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>