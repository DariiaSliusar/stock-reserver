<?php

namespace App\Jobs;

use App\Enums\MovementType;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Services\SupplierService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReserveInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Order $order) {}

    public function handle(SupplierService $supplierService): void
    {
        DB::transaction(function () use ($supplierService) {
            $inventory = Inventory::query()
                ->where('sku', $this->order->sku)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = Inventory::query()->create([
                    'sku' => $this->order->sku,
                    'qty_available' => 0,
                    'qty_reserved' => 0,
                ]);
            }

            if ($inventory->hasEnoughStock($this->order->qty)) {
                $this->reserveStock($inventory);
            } else {
                $this->requestRestock($supplierService);
            }
        });
    }

    private function reserveStock(Inventory $inventory): void
    {
        $inventory->decrement('qty_available', $this->order->qty);
        $inventory->increment('qty_reserved', $this->order->qty);

        $this->order->update(['status' => 'reserved']);

        InventoryMovement::query()->create([
            'sku' => $this->order->sku,
            'order_id' => $this->order->id,
            'type' => MovementType::RESERVED,
            'qty' => $this->order->qty,
        ]);

        Log::info("Order #{$this->order->id} reserved successfully.");
    }

    private function requestRestock(SupplierService $supplierService): void
    {
        $result = $supplierService->reserve($this->order->sku, $this->order->qty);

        if ($result['accepted']) {
            $this->order->update([
                'status' => 'awaiting_restock',
                'supplier_ref' => $result['ref'],
            ]);

            Log::info("Order #{$this->order->id} sent to supplier, ref: {$result['ref']}");

            CheckSupplierStatusJob::dispatch($this->order, 0)
                ->delay(now()->addSeconds(15));
        } else {
            $this->order->update(['status' => 'failed']);
            Log::warning("Order #{$this->order->id} rejected by supplier.");
        }
    }
}
