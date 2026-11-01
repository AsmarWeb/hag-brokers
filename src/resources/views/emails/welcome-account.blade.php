<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to HAG Brokers</title>
</head>
<body style="font-family:Arial,sans-serif;line-height:1.7;color:#243044;background:#f5f7fa;padding:24px">
    <div style="max-width:620px;margin:auto;background:white;padding:32px;border-radius:12px">
        <h2 style="color:#173c62">HAG Brokers</h2>

        <h3>Welcome, {{ $recipientName }}!</h3>

        <p>
            Thank you for registering with HAG Brokers.
            Your account has been created successfully.
        </p>

        <p>
            You can now explore commercial opportunities,
            request shipping quotations, and manage your account.
        </p>

        <p>
            <a href="{{ url('/dashboard') }}"
               style="color:#1762a5;font-weight:bold">
                Open your dashboard
            </a>
        </p>

        <hr style="border:0;border-top:1px solid #eee;margin:28px 0">

        <div dir="rtl" lang="ar" style="text-align:right">
            <h3>مرحباً بك، {{ $recipientName }}!</h3>

            <p>
                نشكرك على التسجيل في HAG Brokers.
                تم إنشاء حسابك بنجاح.
            </p>

            <p>
                يمكنك الآن استكشاف الفرص التجارية، وطلب عروض
                أسعار الشحن، وإدارة حسابك من لوحة التحكم.
            </p>

            <p>
                <a href="{{ url('/dashboard') }}">
                    الانتقال إلى لوحة التحكم
                </a>
            </p>
        </div>

        <hr style="border:0;border-top:1px solid #eee;margin:28px 0">

        <p style="font-size:12px;color:#777">
            HAG Brokers — Commercial Brokerage & Sea Freight Solutions
        </p>
    </div>
</body>
</html>
