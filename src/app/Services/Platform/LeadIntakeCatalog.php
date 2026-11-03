<?php

namespace App\Services\Platform;

final class LeadIntakeCatalog
{
    public const TYPES = ['sell', 'buy', 'service_offer', 'service_request', 'partnership', 'distribution', 'trade', 'import', 'export', 'investment', 'procurement', 'business', 'logistics', 'vessel_brokerage', 'other'];

    public const CONTACTS = ['whatsapp', 'phone', 'email'];
}
