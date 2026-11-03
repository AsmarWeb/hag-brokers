<?php

namespace App\Services\Platform;

use App\Models\CompanyBillingAccount;

final class StripeLiveInvoiceProof
{
    public function __construct(private StripeTestGateway $gateway) {}

    public function verify(array $invoice, CompanyBillingAccount $account): bool
    {
        $this->gateway->testObject($invoice);
        $total = $invoice['total'] ?? null;
        $base = $account->plan_snapshot['monthly_amount_minor'] ?? null;
        $line = data_get($invoice, 'lines.data.0', []);
        $priceId = data_get($line, 'pricing.price_details.price');
        if (! is_int($total) || ! is_int($base) || $total < $base || $base <= 0 || ($invoice['status'] ?? null) !== 'paid'
            || ($invoice['customer'] ?? null) !== $account->stripe_customer_id
            || ($invoice['amount_paid'] ?? null) !== $total || ($invoice['amount_due'] ?? null) !== $total || ($invoice['amount_remaining'] ?? null) !== 0
            || ($invoice['subtotal'] ?? null) !== $base || ($line['amount'] ?? null) !== $base || ($line['quantity'] ?? null) !== 1
            || $priceId !== $account->price_id || data_get($invoice, 'automatic_tax.enabled') !== true || data_get($invoice, 'automatic_tax.status') !== 'complete' || ($invoice['amount_shipping'] ?? null) !== 0 || ($invoice['amount_overpaid'] ?? null) !== 0
            || ($invoice['collection_method'] ?? null) !== 'charge_automatically'
            || ($invoice['starting_balance'] ?? null) !== 0 || ($invoice['pre_payment_credit_notes_amount'] ?? null) !== 0 || ($invoice['post_payment_credit_notes_amount'] ?? null) !== 0
            || ! empty($invoice['discounts']) || ! empty($invoice['total_discount_amounts'])) {
            return false;
        }
        $price = $this->gateway->call('GET', 'prices/'.$this->gateway->id($priceId, 'price_'));
        $this->gateway->testObject($price);
        if (($price['id'] ?? null) !== $priceId || ($price['unit_amount'] ?? null) !== $base || ($price['currency'] ?? null) !== strtolower($account->plan_snapshot['currency'])
            || ($price['tax_behavior'] ?? null) !== 'exclusive' || data_get($price, 'recurring.interval') !== 'month' || data_get($price, 'recurring.interval_count') !== 1) {
            return false;
        }
        $payments = $this->gateway->call('GET', 'invoice_payments', ['invoice' => $invoice['id'], 'status' => 'paid', 'limit' => 100]);
        if (($payments['has_more'] ?? true) !== false || ! is_array($payments['data'] ?? null) || count($payments['data']) !== 1) {
            return false;
        }
        $payment = $payments['data'][0];
        $this->gateway->testObject($payment);
        if (($payment['invoice'] ?? null) !== $invoice['id'] || ($payment['status'] ?? null) !== 'paid' || ($payment['amount_paid'] ?? null) !== $total
            || ($payment['currency'] ?? null) !== $invoice['currency'] || data_get($payment, 'payment.type') !== 'payment_intent') {
            return false;
        }
        $piId = $this->gateway->id(data_get($payment, 'payment.payment_intent'), 'pi_');
        $pi = $this->gateway->call('GET', 'payment_intents/'.$piId);
        $this->gateway->testObject($pi);
        if (($pi['id'] ?? null) !== $piId || ($pi['customer'] ?? null) !== $account->stripe_customer_id || ($pi['status'] ?? null) !== 'succeeded' || ($pi['currency'] ?? null) !== $invoice['currency'] || ($pi['amount_received'] ?? null) !== $total) {
            return false;
        }
        $chargeId = $this->gateway->id($pi['latest_charge'] ?? null, 'ch_');
        $charge = $this->gateway->call('GET', 'charges/'.$chargeId);
        $this->gateway->testObject($charge);

        return ($charge['id'] ?? null) === $chargeId && ($charge['customer'] ?? null) === $account->stripe_customer_id && ($charge['payment_intent'] ?? null) === ($pi['id'] ?? null)
            && ($charge['currency'] ?? null) === $invoice['currency'] && ($charge['amount_captured'] ?? null) === $total
            && ($charge['paid'] ?? null) === true && ($charge['captured'] ?? null) === true && ($charge['refunded'] ?? null) === false
            && ($charge['amount_refunded'] ?? null) === 0 && ($charge['disputed'] ?? null) === false;
    }
}
