<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $language === 'ar' ? 'التحقق من طلب حذف الحساب' : 'Verify account deletion request' }}</title>
</head>
<body style="margin:0;padding:24px;background:#f1f5f9;color:#1e293b;font-family:Arial,sans-serif;line-height:1.8">
    <main style="max-width:600px;margin:0 auto;padding:32px;background:#fff;border:1px solid #e2e8f0;border-radius:16px">
        <h2 style="margin-top:0;color:#0e7490">HAG Brokers</h2>

        @if ($language === 'ar')
            <h3>تأكيد طلب حذف حسابك</h3>
            <p>تلقينا طلبًا للتحقق من رغبتك في حذف حساب HAG Brokers المرتبط بهذا البريد الإلكتروني.</p>
            <p>اضغط على الزر أدناه لتأكيد ملكيتك للبريد الإلكتروني وتقديم طلب الحذف.</p>
            <p style="margin:28px 0">
                <a href="{{ $verificationUrl }}"
                   style="display:inline-block;padding:13px 24px;background:#0891b2;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold">
                    التحقق وتقديم طلب الحذف
                </a>
            </p>
            <p>هذا الرابط صالح حتى: {{ $expiresAt }}</p>
            <p>لن يُحذف حسابك بمجرد الضغط على الرابط؛ سيُسجل طلبك للمراجعة قبل اتخاذ أي إجراء.</p>
            <p>إذا لم تطلب ذلك، فتجاهل هذه الرسالة. لن يتم تقديم طلب حذف من خلال هذا البريد وحده.</p>
        @else
            <h3>Verify your account deletion request</h3>
            <p>We received a request to verify your intention to delete the HAG Brokers account associated with this email address.</p>
            <p>Use the button below to verify ownership of this email address and submit the deletion request.</p>
            <p style="margin:28px 0">
                <a href="{{ $verificationUrl }}"
                   style="display:inline-block;padding:13px 24px;background:#0891b2;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold">
                    Verify and submit deletion request
                </a>
            </p>
            <p>This link expires at: {{ $expiresAt }}</p>
            <p>Your account will not be deleted by clicking this link. It only submits a request for review.</p>
            <p>If you did not make this request, ignore this email. No deletion request will be submitted through this email alone.</p>
        @endif
    </main>
</body>
</html>
