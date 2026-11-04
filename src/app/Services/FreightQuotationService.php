<?php

namespace App\Services;

use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class FreightQuotationService
{
    public function customer(User $user): Customer
    {
        abort_unless($user->hasVerifiedEmail() && ! $user->hasRole(['admin', 'super-admin']), 403);
        abort_unless($user->customer_id, 403);
        $customer = Customer::whereKey($user->customer_id)->where('is_active', true)->first();
        abort_unless($customer, 403);
        // Existing legacy customers remain supported. Workspace customers need current leadership.
        if (CompanyWorkspace::where('customer_id', $customer->id)->exists() || CompanyMember::where('user_id', $user->id)->exists()) {
            app(CompanyMarketplaceService::class)->actor($user, true);
        }

        return $customer;
    }

    public static function rules(): array
    {
        return [
            'origin_port_id' => ['required', 'integer', Rule::exists('ports', 'id')->where('is_active', true)],
            'destination_port_id' => ['required', 'integer', 'different:origin_port_id', Rule::exists('ports', 'id')->where('is_active', true)],
            'cargo_type' => ['required', 'string', 'max:255'],
            'container_type' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'preferred_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function submit(User $actor, array $data, string $key): array
    {
        return DB::transaction(function () use ($actor, $data, $key) {
            // Serializes retries across devices belonging to the same company.
            $customer = $this->customer($actor);
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $user = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $customer = $this->customer($user);
            $payload = [
                'origin_port_id' => (int) $data['origin_port_id'], 'destination_port_id' => (int) $data['destination_port_id'],
                'cargo_type' => trim($data['cargo_type']), 'container_type' => trim($data['container_type']),
                'quantity' => (int) $data['quantity'], 'weight' => isset($data['weight']) ? number_format((float) $data['weight'], 2, '.', '') : null,
                'preferred_date' => $data['preferred_date'] ?? null,
            ];
            $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
            $old = DB::table('freight_request_keys')->where('customer_id', $customer->id)->where('request_key', $key)->first();
            if ($old) {
                if (! hash_equals($old->payload_hash, $hash)) {
                    throw ValidationException::withMessages(['request_key' => ['This request key was already used with different shipment details.']]);
                }

                return [Quotation::where('customer_id', $customer->id)->findOrFail($old->quotation_id), false];
            }
            $q = Quotation::create($payload + ['customer_id' => $customer->id,
                'reference_no' => 'QT-'.now()->format('Y').'-'.strtoupper(str_replace('-', '', (string) Str::uuid()))]);
            DB::table('freight_request_keys')->insert(['customer_id' => $customer->id, 'request_key' => $key,
                'payload_hash' => $hash, 'quotation_id' => $q->id, 'created_at' => now()]);
            // Notification failure rolls back both the request and its retry receipt.
            NotificationService::admins('quotation.created', 'New quotation request',
                'New quotation '.$q->reference_no.' requires review.', $q, ['url' => route('admin.quotations.show', $q)]);

            return [$q, true];
        });
    }
}
