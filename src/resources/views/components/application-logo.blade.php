<svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="hag-bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#67f5ff" />
            <stop offset="45%" stop-color="#19dbe8" />
            <stop offset="100%" stop-color="#009fe8" />
        </linearGradient>

        <linearGradient id="hag-border" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#b9fbff" />
            <stop offset="45%" stop-color="#00e5ff" />
            <stop offset="100%" stop-color="#0878e8" />
        </linearGradient>

        <linearGradient id="hag-shine" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity=".55" />
            <stop offset="45%" stop-color="#ffffff" stop-opacity=".08" />
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
        </linearGradient>

        <filter id="hag-shadow" x="-30%" y="-30%" width="160%" height="160%">
            <feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#00d9ff" flood-opacity=".35" />
        </filter>
    </defs>

    <!-- Main rounded square -->
    <rect x="27" y="27" width="262" height="262" rx="58" fill="url(#hag-bg)" filter="url(#hag-shadow)" />

    <!-- Outer illuminated border -->
    <rect x="30" y="30" width="256" height="256" rx="55" fill="none" stroke="url(#hag-border)"
        stroke-width="8" />

    <!-- Inner glass border -->
    <rect x="40" y="40" width="236" height="236" rx="47" fill="none" stroke="#ffffff"
        stroke-opacity=".18" stroke-width="3" />

    <!-- Upper-left glass highlight -->
    <path d="M72 48
           C53 58 42 78 40 103
           C38 125 42 143 50 157
           C62 126 82 96 111 75
           C135 58 164 48 195 43
           L91 43
           C84 43 77 45 72 48Z" fill="url(#hag-shine)" />

    <!-- HAG -->
    <text x="158" y="184" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="78"
        font-weight="900" letter-spacing="-5" fill="#02182c">
        HAG
    </text>

    <!-- Subtle bottom reflection -->
    <path d="M58 245
           C92 269 124 276 158 276
           C192 276 226 269 258 246" fill="none" stroke="#ffffff" stroke-opacity=".10" stroke-width="4"
        stroke-linecap="round" />
</svg>
