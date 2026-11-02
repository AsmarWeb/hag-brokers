<?php

namespace App\Console\Commands;

use App\Models\CompanyBillingAccount;
use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\StripeTestGateway;
use Illuminate\Console\Command;

class ReconcileCompanyBilling extends Command
{
    protected $signature = 'platform:billing-reconcile';

    protected $description = 'Reconcile test company billing from Stripe without extending unproven access.';

    public function handle(CompanyBillingService $service, StripeTestGateway $gateway): int
    {
        if (! $gateway->ready()) {
            $this->info('Stripe test billing is disabled.');

            return self::SUCCESS;
        }
        $failed = 0;
        CompanyBillingAccount::whereNotNull('stripe_customer_id')->orderBy('id')->chunkById(100, function ($accounts) use ($service, &$failed): void {
            foreach ($accounts as $a) {
                try {
                    $service->sync($a);
                } catch (\Throwable $e) {
                    $failed++;
                    report($e);
                    $this->error('Reconciliation failed for account #'.$a->id);
                }
            }
        });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
