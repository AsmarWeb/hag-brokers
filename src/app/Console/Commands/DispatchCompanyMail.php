<?php

namespace App\Console\Commands;

use App\Models\CompanyMailDelivery;
use App\Services\Platform\CompanyMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DispatchCompanyMail extends Command
{
    protected $signature = 'platform:company-mail {--check : Read-only readiness and delivery counts}';

    protected $description = 'Collect eligible company notifications and queue private email alerts';

    public function handle(CompanyMailService $service): int
    {
        $this->line('Company email: '.($service->ready() ? 'READY' : 'DISABLED OR NOT CONFIGURED'));
        $this->line('Queue: database / company-mail');
        if ($this->option('check')) {
            if (! Schema::hasTable('company_mail_deliveries') || ! Schema::hasTable('company_mail_preferences') || ! Schema::hasTable('jobs')) {
                $this->error('Delivery tables: MISSING');

                return self::FAILURE;
            }
            foreach (CompanyMailDelivery::selectRaw('status, COUNT(*) as total')->groupBy('status')->get() as $row) {
                $this->line($row->status.': '.$row->total);
            }
            $this->line('Read-only check. No emails sent or queued.');

            return self::SUCCESS;
        }
        if (! $service->ready()) {
            return self::SUCCESS;
        }
        $this->info('New deliveries: '.$service->collect().'; queue requests: '.$service->enqueue());

        return self::SUCCESS;
    }
}
