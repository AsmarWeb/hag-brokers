<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $language === 'ar' ? 'تأكيد حذف الحساب' : 'Confirm account deletion' }}</title>
</head>
<body style="font-family:Arial,sans-serif;line-height:1.8;color:#243044;background:#f5f7fa;padding:24px">
    <main style="max-width:620px;margin:auto;background:#fff;padding:32px;border-radius:12px">
        <h2 style="color:#173c62">HAG Brokers</h2>

        @if($language === 'ar')
            <h3>تأكيد طلب حذف الحساب</h3>
            <p>هل تؤكد رغبتك في حذف حسابك والبيانات المرتبطة به وفق سياسة الخصوصية؟</p>
            <p>هذا الإجراء يؤكد طلبك فقط. ستتم مراجعته قبل تنفيذ الحذف، وقد تُحتفظ ببعض البيانات التي يلزم الاحتفاظ بها لأسباب قانونية أو أمنية مشروعة وفق سياسة الاحتفاظ.</p>
            <form method="POST" action="{{ route('account-deletion.confirm', ['token' => $token]) }}">
                @csrf
                <input type="hidden" name="lang" value="ar">
                <button type="submit" style="background:#173c62;color:#fff;padding:12px 22px;border:0;border-radius:8px;cursor:pointer">
                    أؤكد طلب حذف حسابي
                </button>
            </form>
        @else
            <h3>Confirm your account deletion</h3>
            <p>Do you confirm that you want to delete your account and associated data in accordance with our Privacy Policy?</p>
            <p>This confirms your request only. It will be reviewed before deletion is carried out. Certain information may be retained where there is a legitimate legal or security reason, as explained in our retention policy.</p>
            <form method="POST" action="{{ route('account-deletion.confirm', ['token' => $token]) }}">
                @csrf
                <input type="hidden" name="lang" value="en">
                <button type="submit" style="background:#173c62;color:#fff;padding:12px 22px;border:0;border-radius:8px;cursor:pointer">
                    Confirm my deletion request
                </button>
            </form>
        @endif
    </main>
</body>
</html>
