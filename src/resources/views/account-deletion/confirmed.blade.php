<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $language === 'ar' ? 'تم تأكيد الطلب' : 'Request confirmed' }}</title>
</head>
<body style="font-family:Arial,sans-serif;line-height:1.8;color:#243044;background:#f5f7fa;padding:24px">
    <main style="max-width:620px;margin:auto;background:#fff;padding:32px;border-radius:12px">
        <h2 style="color:#173c62">HAG Brokers</h2>

        @if($language === 'ar')
            <h3>تم تأكيد طلبك بنجاح</h3>
            <p>تم تأكيد رغبتك في حذف الحساب. سيخضع الطلب للمراجعة واستكمال إجراءات الحذف.</p>
            <p>لن يتم حذف الحساب بمجرد تأكيد البريد الإلكتروني.</p>
        @else
            <h3>Your request has been confirmed</h3>
            <p>Your intention to delete your account has been confirmed. The request will now proceed through review and the required deletion process.</p>
            <p>Your account is not deleted merely by confirming this email.</p>
        @endif
    </main>
</body>
</html>
