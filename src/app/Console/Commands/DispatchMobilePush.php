<?php

namespace App\Console\Commands;

use App\Services\Platform\MobilePushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DispatchMobilePush extends Command
{
    protected $signature = 'platform:mobile-push {--check}';

    protected $description = 'Collect generic mobile push updates and check provider receipts.';

    public function handle(MobilePushService $s): int
    {
        if ($this->option('check')) {
            $tables = Schema::hasTable('mobile_push_devices') && Schema::hasTable('mobile_push_deliveries');
            $this->line('Mobile push: '.($s->ready() && $tables ? 'READY' : 'DISABLED OR NOT CONFIGURED'));
            $this->line('Read-only check. No requests, notifications or tokens sent.');

            return $tables ? 0 : 1;
        }
        if (! $s->ready()) {
            $this->line('Mobile push disabled.');

            return 0;
        }
        $lock = Cache::lock('hag-mobile-push-dispatch', 120);
        if (! $lock->get()) {
            return 0;
        }
        try {
            $s->collect();
            foreach (DB::table('mobile_push_deliveries')->where('status', 'pending')->orderBy('id')->limit(5)->pluck('id') as $id) {
                $s->deliver($id);
            }$s->receipts();
        } finally {
            $lock->release();
        }
        $this->line('Mobile push cycle complete.');

        return 0;
    }
}
