<?php

namespace App\Console\Commands;

use App\Models\PlatformPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstallPlatformPlans extends Command
{
    protected $signature = 'platform:install-plans';

    protected $description = 'Create missing platform plan prototypes; never overwrite existing plans or bill customers.';

    public function handle(): int
    {
        DB::transaction(function (): void {
            foreach ([['free', 'مجانية', 'Free', 0, 1, 1, 3], ['business', 'أعمال', 'Business', 19900, 3, 5, 20], ['professional', 'احترافية', 'Professional', 49900, 10, 20, 100]] as [$code,$ar,$en,$amount,$seats,$opportunities,$applications]) {
                PlatformPlan::firstOrCreate(['code' => $code], ['name_ar' => $ar, 'name_en' => $en, 'monthly_amount_minor' => $amount, 'currency' => 'AED', 'seats' => $seats, 'active_opportunities' => $opportunities, 'monthly_applications' => $applications, 'is_available' => true]);
            }
        });
        $this->info('Plan prototypes ready. Existing plans preserved. No subscriptions activated or money collected.');

        return self::SUCCESS;
    }
}
