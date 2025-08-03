    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PressHub</title>
    <style>
        /* Simple styles that Gmail is less likely to modify */
        body {
            font-family: Arial, sans-serif;
            background-color: #FFF;
            margin: 0;
            padding: 0;
        }
    </style>
    </head>

    <body style="font-family: Arial, sans-serif; background-color: #FFF; margin: 0; padding: 0;">
        <div style="max-width: 600px; margin: auto; padding: 20px; background-color: #FFF; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <div style="text-align: center; margin-bottom: 20px;">
                <span style="font-size: 24px; font-weight: bold; color: #0d6efd;">PressHub</span>
            </div>
            <div style="padding: 0 20px;">
                <h3 style="color: #0d6efd; margin-top: 0;">@yield('subject','PressHub')</h3>
                @yield('content')
                <p style="margin: 16px 0 0 0;">
                    Best regards,<br>
                    PressHub Team
                </p>
            </div>
            <div style="text-align: center; font-size: 14px; padding: 10px 20px; border-top: 1px solid #ddd; margin-top: 20px;">
                &copy; {{ date('Y') }} PressHub. All rights reserved.<br>
                <a href="#" style="color: #0d6efd; text-decoration: none;">Unsubscribe</a> |
                <a href="#" style="color: #0d6efd; text-decoration: none;">Contact Us</a>
            </div>
        </div>
    </body>

    </html>