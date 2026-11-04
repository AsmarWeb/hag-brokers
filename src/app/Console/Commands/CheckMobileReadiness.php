<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckMobileReadiness extends Command
{
    protected $signature = 'platform:mobile-check';

    protected $description = 'Read-only mobile API database readiness check; prints no credentials.';

    public function handle(): int
    {
        try {
            $this->line('Database connection: '.DB::getDriverName());
            $ready = true;
            foreach (['users', 'personal_access_tokens', 'company_workspaces', 'company_members', 'opportunities', 'company_trade_offers', 'trade_offer_messages', 'trade_offer_reads', 'notifications', 'mobile_app_settings', 'mobile_app_setting_audits', 'freight_request_keys', 'account_deletion_requests', 'platform_legal_documents', 'platform_legal_audits', 'mobile_push_devices', 'mobile_push_deliveries'] as $table) {
                $ok = Schema::hasTable($table);
                $ready = $ready && $ok;
                $this->line($table.': '.($ok ? 'READY' : 'MISSING'));
            }
            $this->line('Read-only check. No records changed, emails sent or tokens created.');

            return $ready ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Database readiness check failed. Inspect the private Laravel log on the server.');

            return self::FAILURE;
        }
    }
}
