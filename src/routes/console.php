<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('platform:billing-reconcile')->everyFiveMinutes()->withoutOverlapping();

\Illuminate\Support\Facades\Schedule::command('platform:company-mail')->everyMinute()->withoutOverlapping();

\Illuminate\Support\Facades\Schedule::command('platform:mobile-push')->everyMinute()->withoutOverlapping();
