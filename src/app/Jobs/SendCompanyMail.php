<?php

namespace App\Jobs;

use App\Services\Platform\CompanyMailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCompanyMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 40;

    public function __construct(public int $deliveryId) {}

    public function handle(CompanyMailService $service): void
    {
        $service->deliver($this->deliveryId);
    }
}
