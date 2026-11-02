<?php

namespace App\Services\Platform;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class StripeTestGateway
{
    public function ready(): bool
    {
        return config('platform_billing.enabled') && str_starts_with((string) config('platform_billing.secret'), 'sk_test_');
    }

    public function call(string $method, string $path, array $params = [], ?string $key = null): array
    {
        abort_unless($this->ready(), 503, __('billing.unavailable'));
        $http = Http::withBasicAuth(config('platform_billing.secret'), '')->withHeaders(['Stripe-Version' => config('platform_billing.api_version')])->acceptJson()->asForm()->connectTimeout(3)->timeout(8);
        if ($key) {
            $http = $http->withHeaders(['Idempotency-Key' => $key]);
        }
        try {
            $r = $method === 'GET' ? $http->get('https://api.stripe.com/v1/'.$path, $params) : $http->post('https://api.stripe.com/v1/'.$path, $params);
        } catch (ConnectionException $exception) {
            throw new \RuntimeException('Stripe test API connection failed.', 0, $exception);
        }
        if (! $r->successful() || ! is_array($r->json())) {
            throw new \RuntimeException('Stripe test API request failed.');
        }

        return $r->json();
    }

    public function testObject(array $o): void
    {
        if (($o['livemode'] ?? null) !== false) {
            throw new \RuntimeException('Only Stripe test objects are accepted.');
        }
    }

    public function id(mixed $id, string $prefix): string
    {
        if (! is_string($id) || ! preg_match('/^'.preg_quote($prefix, '/').'[A-Za-z0-9_]+$/D', $id)) {
            throw new \RuntimeException('Invalid Stripe object identifier.');
        }

        return $id;
    }

    public function checkoutUrl(string $url, string $host = 'checkout.stripe.com'): string
    {
        if (parse_url($url, PHP_URL_SCHEME) !== 'https' || parse_url($url, PHP_URL_HOST) !== $host || parse_url($url, PHP_URL_USER) !== null) {
            throw new \RuntimeException('Invalid Stripe hosted URL.');
        }

        return $url;
    }

    public function event(string $raw, string $signature): array
    {
        abort_unless($this->ready(), 503);
        $secret = (string) config('platform_billing.webhook_secret');
        abort_unless(str_starts_with($secret, 'whsec_') && strlen($raw) <= 1048576, 400);
        $values = [];
        foreach (explode(',', $signature) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $values[$pair[0]][] = $pair[1];
            }
        }
        $t = $values['t'][0] ?? '';
        abort_unless(ctype_digit($t) && strlen($t) <= 12 && abs(time() - (int) $t) <= 300, 400);
        $expected = hash_hmac('sha256', $t.'.'.$raw, $secret);
        $ok = false;
        foreach ($values['v1'] ?? [] as $sig) {
            $ok = $ok || hash_equals($expected, $sig);
        }
        abort_unless($ok, 400);
        try {
            $event = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            abort(400);
        }
        abort_unless(is_array($event) && ($event['livemode'] ?? null) === false && is_string($event['id'] ?? null) && is_string($event['type'] ?? null), 400);
        $this->id($event['id'], 'evt_');

        return $event;
    }
}
