<!doctype html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="font-family:Arial,sans-serif;line-height:1.7;color:#172033">
@if($language === 'ar')
    <h2>تأكيد طلب إغلاق مساحة الشركة</h2>
    <p>تم طلب إغلاق مساحة الشركة <strong>{{ $companyName }}</strong>.</p>
    <p>اضغط الرابط أدناه لتأكيد ملكية البريد الإلكتروني. بعد ذلك ستحتاج إلى تسجيل الدخول وتأكيد الإغلاق بشكل منفصل.</p>
    <p><a href="{{ $verificationUrl }}">تأكيد البريد الإلكتروني</a></p>
    <p>الرابط صالح لمدة 24 ساعة. إذا لم تطلب ذلك، فتجاهل الرسالة.</p>
@else
    <h2>Verify company workspace closure</h2>
    <p>A closure request was started for <strong>{{ $companyName }}</strong>.</p>
    <p>Verify this email address below. You must then sign in and explicitly confirm the closure as a separate step.</p>
    <p><a href="{{ $verificationUrl }}">Verify email address</a></p>
    <p>This link expires in 24 hours. Ignore this email if you did not request the closure.</p>
@endif
</body></html>
