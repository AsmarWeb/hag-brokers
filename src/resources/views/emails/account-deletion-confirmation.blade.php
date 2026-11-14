<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $language === 'ar' ? 'تأكيد حذف الحساب' : 'Confirm account deletion' }}</title>
</head>
<body style="font-family:Arial,sans-serif;line-height:1.8;color:#243044;background:#f5f7fa;padding:24px">
    <div style="max-width:620px;margin:auto;background:#fff;padding:32px;border-radius:12px">
        <h2 style="color:#173c62">HAG Brokers</h2>

        @if($language === 'ar')
            <h3>تأكيد طلب حذف حسابك</h3>
            <p>تلقينا طلباً لحذف حسابك في HAG Brokers.</p>
            <p>إذا كنت أنت من قدم الطلب، اضغط على الرابط أدناه لتأكيد رغبتك.</p>
            <p>
                <a href="{{ $confirmationUrl }}"
                   style="display:inline-block;background:#173c62;color:#fff;padding:12px 22px;border-radius:8px;text-decoration:none">
                    تأكيد طلب الحذف
                </a>
            </p>
            <p>صلاحية الرابط حتى: {{ $expiresAt }}</p>
            <p>هذا التأكيد لا يحذف الحساب فوراً؛ سيخضع الطلب للمراجعة والإجراءات اللازمة.</p>
            <p>إذا لم تطلب حذف حسابك، فتجاهل هذه الرسالة.</p>
        @else
            <h3>Confirm your account deletion request</h3>
            <p>We received a request to delete your HAG Brokers account.</p>
            <p>If you made this request, use the link below to confirm your intention.</p>
            <p>
                <a href="{{ $confirmationUrl }}"
                   style="display:inline-block;background:#173c62;color:#fff;padding:12px 22px;border-radius:8px;text-decoration:none">
                    Confirm deletion request
                </a>
            </p>
            <p>Link expires at: {{ $expiresAt }}</p>
            <p>Confirmation does not immediately delete your account. The request will go through review and the required procedures.</p>
            <p>If you did not request deletion, ignore this email.</p>
        @endif

        <hr style="border:0;border-top:1px solid #eee;margin:28px 0">
        <p style="font-size:12px;color:#777">
            HAG Brokers — Commercial Brokerage & Sea Freight Solutions
        </p>
    </div>
</body>
</html>
