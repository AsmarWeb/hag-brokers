<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TwilioSmsGateway
{
    public function send(string $phone, string $message): bool
    {
        $sid = (string) config('services.twilio.account_sid');
        $token = (string) config('services.twilio.auth_token');
        $from = (string) config('services.twilio.from');

        if ($sid === '' || $token === '' || $from === '') {
            Log::warning('SMS delivery is not configured.', ['provider' => 'twilio']);

            return false;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->timeout(10)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $phone,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('SMS provider rejected a verification message.', [
                'provider' => 'twilio',
                'status' => $response->status(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('SMS provider request failed.', [
                'provider' => 'twilio',
                'exception' => $exception::class,
            ]);
        }

        return false;
    }
}
