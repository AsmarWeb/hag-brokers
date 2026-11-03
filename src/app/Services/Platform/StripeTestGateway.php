<?php

namespace App\Services\Platform;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class StripeTestGateway
{
    // Historical class name retained for backwards compatibility; mode is explicit.
    public function live(): bool
    {
        return config('platform_billing.mode', 'test') === 'live';
    }

    public function strictProof(): bool
    {
        return $this->live() || (bool) config('platform_billing.strict_proof_test', false);
    }

    public function ready(): bool
    {
        if (! in_array(config('platform_billing.mode', 'test'), ['test', 'live'], true) || ! config('platform_billing.enabled')) {
            return false;
        }
        $prefix = $this->live() ? 'sk_live_' : 'sk_test_';
        $restricted = $this->live() ? 'rk_live_' : 'rk_test_';
        $key = (string) config('platform_billing.secret');
        if (! str_starts_with($key, $prefix) && ! str_starts_with($key, $restricted)) {
            return false;
        }
        if (! $this->live()) {
            return true;
        }
        $url = parse_url((string) config('app.url'));

        return app()->environment(['production', 'testing']) && ! config('app.debug') && ($url['scheme'] ?? '') === 'https' && ! empty($url['host']) && ! isset($url['user']) && ! isset($url['pass']) && ! isset($url['query']) && ! isset($url['fragment'])
            && str_starts_with((string) config('platform_billing.webhook_secret'), 'whsec_')
            && str_starts_with((string) config('platform_billing.prices.business'), 'price_')
            && str_starts_with((string) config('platform_billing.prices.professional'), 'price_')
            && config('platform_billing.prices.business') !== config('platform_billing.prices.professional');
    }

    public function sessionPrefix(): string
    {
        return $this->live() ? 'cs_live_' : 'cs_test_';
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
            throw new \RuntimeException('Stripe API connection failed.', 0, $exception);
        }
        if (! $r->successful() || ! is_array($r->json())) {
            throw new \RuntimeException('Stripe API request failed.');
        }

        return $r->json();
    }

    public function testObject(array $o): void
    {
        if (($o['livemode'] ?? null) !== $this->live()) {
            throw new \RuntimeException('Stripe object mode mismatch.');
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
        abort_unless(is_array($event) && ($event['livemode'] ?? null) === $this->live() && is_string($event['id'] ?? null) && is_string($event['type'] ?? null), 400);
        $this->id($event['id'], 'evt_');

        return $event;
    }
}
