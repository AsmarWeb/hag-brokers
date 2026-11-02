#!/usr/bin/env python3
"""Run actual billing service in concurrent PHP processes, using only a disposable DB.
Usage from src: python3 tests/Support/billing_concurrency.py
Optional PHP_BINARY and PHP_INI environment variables select the PHP executable/config.
No Stripe calls, development DB access, or credentials required.
"""
import os
from pathlib import Path
import sqlite3
import subprocess
import tempfile
import time

SRC = Path(__file__).resolve().parents[2]
PHP = [os.environ.get('PHP_BINARY', 'php')]
if os.environ.get('PHP_INI'):
    PHP += ['-c', os.environ['PHP_INI']]
WORKER = r'''<?php
$root = $argv[1];
$src = $argv[2];
$mode = $argv[3];
if (!is_file($root.'/test-only-marker') || !is_file($root.'/database.sqlite')) {
    throw new RuntimeException('Disposable fixture required.');
}
foreach (['APP_ENV'=>'testing', 'DB_CONNECTION'=>'sqlite', 'DB_DATABASE'=>':memory:',
    'APP_CONFIG_CACHE'=>$root.'/no-config-cache.php',
    'APP_KEY'=>'base64:MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY='] as $key=>$value) {
    putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value;
}
require $src.'/vendor/autoload.php';
$app = require $src.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>$root.'/database.sqlite',
    'database.connections.sqlite.busy_timeout'=>0,
    'platform_billing.enabled'=>true, 'platform_billing.secret'=>'sk_test_concurrency_fake']);
Illuminate\Support\Facades\DB::purge('sqlite');
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Models\CompanyBillingAccount;
use App\Services\Platform\CompanyBillingService;
if ($mode === 'seed') {
    Schema::create('company_workspaces', fn($t)=>$t->id());
    (require $src.'/database/migrations/2026_11_02_180000_create_company_billing_tables.php')->up();
    DB::table('company_workspaces')->insert(['id'=>1]);
    CompanyBillingAccount::create(['company_workspace_id'=>1, 'customer_key'=>'concurrency-test',
        'stripe_customer_id'=>'cus_test', 'price_id'=>'price_test', 'plan_code'=>'business',
        'plan_snapshot'=>['code'=>'business','currency'=>'AED']]);
    exit(0);
}
if ($mode === 'check') {
    $a = CompanyBillingAccount::firstOrFail();
    if ($a->status !== 'active' || $a->paid_until->timestamp !== 1800000000
        || DB::table('company_billing_events')->count() !== 3
        || DB::table('company_billing_receipts')->count() !== 1) {
        throw new RuntimeException('Concurrency invariant failed.');
    }
    echo "PASS: 3 parallel events with SQLite writer contention, one receipt, correct paid access.\n";
    exit(0);
}
touch($root.'/ready-'.$mode);
$deadline = microtime(true)+20;
while (!is_file($root.'/go')) {
    if (microtime(true)>$deadline) { throw new RuntimeException('Barrier timed out.'); }
    usleep(10000);
}
Http::preventStrayRequests();
Http::fake(function($request) {
    if (DB::transactionLevel() !== 0) {
        throw new RuntimeException('Provider request inside database transaction.');
    }
    usleep(150000);
    if (str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/subscriptions')) {
        return Http::response(['has_more'=>false,'data'=>[[
            'id'=>'sub_test','livemode'=>false,'customer'=>'cus_test','status'=>'active',
            'latest_invoice'=>'in_test','items'=>['data'=>[[
                'price'=>['id'=>'price_test'],'quantity'=>1,
                'current_period_start'=>1790000000,'current_period_end'=>1800000000,
            ]]],
        ]]]);
    }
    return Http::response(['id'=>'in_test','livemode'=>false,'customer'=>'cus_test',
        'subscription'=>'sub_test','status'=>'paid','amount_paid'=>20895,'currency'=>'aed',
        'billing_reason'=>'subscription_create','lines'=>['has_more'=>false,'data'=>[[
            'period'=>['start'=>1790000000,'end'=>1800000000],
        ]]]]);
});
$types = ['checkout.session.completed','invoice.paid','customer.subscription.created'];
$event = ['id'=>'evt_concurrency_'.$mode,'type'=>$types[(int)$mode],
    'data'=>['object'=>['customer'=>'cus_test']]];
app(CompanyBillingService::class)->webhook($event, json_encode($event));
// Replay each event too: retries must not create a second receipt/event.
app(CompanyBillingService::class)->webhook($event, json_encode($event));
echo "OK\n";
'''

def main():
    with tempfile.TemporaryDirectory(prefix='hag-billing-concurrency-') as directory:
        root = Path(directory)
        (root / 'test-only-marker').touch()
        (root / 'database.sqlite').touch()
        worker = root / 'worker.php'
        worker.write_text(WORKER)
        def command(mode):
            return PHP + [str(worker), str(root), str(SRC), mode]
        seeded = subprocess.run(command('seed'), check=True, capture_output=True, text=True)
        if seeded.stdout.strip() or seeded.stderr.strip():
            raise RuntimeError(seeded.stdout + seeded.stderr)
        processes = []
        try:
            for i in range(3):
                processes.append(subprocess.Popen(command(str(i)), stdout=subprocess.PIPE,
                    stderr=subprocess.PIPE, text=True))
            deadline = time.monotonic() + 20
            while len(list(root.glob('ready-*'))) != 3:
                if time.monotonic() > deadline or any(p.poll() is not None for p in processes):
                    raise RuntimeError('Workers failed to reach the test barrier.')
                time.sleep(.01)
            # A separate real SQLite writer overlaps the workers' first writes.
            # busy_timeout=0 in the workers forces the bounded retry path.
            with sqlite3.connect(root / 'database.sqlite') as blocker:
                blocker.execute('BEGIN IMMEDIATE')
                (root / 'go').touch()
                time.sleep(.6)
                blocker.commit()
            for process in processes:
                out, err = process.communicate(timeout=30)
                if process.returncode or out.strip() != 'OK' or err.strip():
                    raise RuntimeError(out + err)
            result = subprocess.run(command('check'), check=True, capture_output=True, text=True)
            if not result.stdout.startswith('PASS: 3 parallel events') or result.stderr.strip():
                raise RuntimeError(result.stdout + result.stderr)
            print(result.stdout.strip())
        finally:
            for process in processes:
                if process.poll() is None:
                    process.kill()
                process.wait()

if __name__ == '__main__':
    main()
