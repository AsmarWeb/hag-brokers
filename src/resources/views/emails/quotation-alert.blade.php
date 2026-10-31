<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $alertTitle }}</title>
</head>
<body style="font-family:Arial,sans-serif;background:#f1f5f9;padding:30px">
    <div style="max-width:600px;margin:auto;background:white;padding:30px;border-radius:14px">
        <h2 style="color:#0891b2">HAG Brokers</h2>
        <h3>{{ $alertTitle }}</h3>
        <p>{{ $alertMessage }}</p>
        @if ($alertUrl)
            <p><a href="{{ $alertUrl }}">View quotation details</a></p>
        @endif
        <hr>
        <small>Automated notification from HAG Brokers.</small>
    </div>
</body>
</html>
