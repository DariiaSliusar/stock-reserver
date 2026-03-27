<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\ReserveInventoryJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReserveInventoryListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(OrderCreated $event): void
    {
        ReserveInventoryJob::dispatch($event->order);
    }
}
