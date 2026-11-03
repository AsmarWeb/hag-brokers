<?php

return [
    'title' => 'Email notification settings',
    'heading' => 'Stay close to your business opportunities.',
    'intro' => 'Shared company settings. Alerts go only to current owners and managers with verified email addresses, not members or platform administrators.',
    'not_ready' => 'Automatic email is disabled or not configured on the server. You can save preferences; in-platform notifications continue to work.',
    'fields' => [
        'enabled' => 'Enable company email alerts',
        'offers' => 'Commercial offers and decisions',
        'messages' => 'Negotiation messages',
        'locale' => 'Email language',
    ],
    'off' => 'Disabled',
    'on' => 'Enabled',
    'privacy' => 'Settings apply to the entire company. Only new events after enabling are eligible; the notification archive is not replayed. Emails contain no conversation text, prices or attachments. Opening an offer requires authorized sign-in. Disable alerts here at any time.',
    'save' => 'Save preferences',
    'saved' => 'Company email preferences saved.',
    'delivery' => 'Company delivery status',
    'delivery_help' => 'Sent means accepted by the email transport, not guaranteed inbox delivery. A long-running processing entry needs operational review before any resend.',
    'status' => [
        'pending' => 'Pending or awaiting retry',
        'processing' => 'Processing / review if prolonged',
        'sent' => 'Sent to mail transport',
        'skipped' => 'Skipped after eligibility check',
        'failed' => 'Stopped after retry limit',
    ],
    'subjects' => [
        'rfq' => [
            'submitted' => 'New commercial offer on HAG Brokers',
            'selected' => 'Commercial offer selection update',
            'declined' => 'Commercial offer decision update',
            'withdrawn' => 'Commercial offer withdrawn',
            'message' => 'New negotiation message on HAG Brokers',
        ],
    ],
    'mail_body' => 'Your company has a new update linked to a commercial offer. Sign in with your authorized account to review the details and respond.',
    'open' => 'Open update on the platform',
    'mail_privacy' => 'This is an operational alert, not contract acceptance or payment authorization. Do not reply with passwords or banking details.',
];
