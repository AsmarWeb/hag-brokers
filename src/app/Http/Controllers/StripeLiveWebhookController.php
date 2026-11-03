<?php

namespace App\Http\Controllers;

use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\StripeTestGateway;
use Illuminate\Http\Request;

class StripeLiveWebhookController extends Controller
{
    public function __invoke(Request $request, StripeTestGateway $gateway, CompanyBillingService $service)
    {
        abort_unless($gateway->live(), 400);
        $raw = $request->getContent();
        $event = $gateway->event($raw, (string) $request->header('Stripe-Signature'));
        $service->webhook($event, $raw);

        return response()->json(['received' => true]);
    }
}
