<?php

    namespace App\Jobs;

    use App\Enums\MovementType;
    use App\Enums\OrderStatus;
    use App\Enums\SupplierStatus;
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

    class CheckSupplierStatusJob implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        private const MAX_DELAY_RETRIES = 2;

        public function __construct(
            private readonly Order $order,
            private readonly int $delayAttempt
        ) {}

        public function handle(SupplierService $supplierService): void
        {
            $freshOrder = $this->order->fresh();

            if ($freshOrder === null || $freshOrder->status->isTerminal()) {
                Log::info('CheckSupplierStatusJob skipped: order already terminal.', [
                    'order_id' => $this->order->id,
                ]);
                return;
            }

            $supplierStatus = $supplierService->checkStatus((string) $this->order->supplier_ref);

            Log::info('Supplier status received', [
                'order_id' => $this->order->id,
                'supplier_ref' => $this->order->supplier_ref,
                'status' => $supplierStatus->value,
                'delay_attempt' => $this->delayAttempt,
            ]);

            match ($supplierStatus) {
                SupplierStatus::OK => $this->confirmRestock(),
                SupplierStatus::FAIL => $this->markFailed(),
                SupplierStatus::DELAYED => $this->handleDelayed(),
            };
        }

        private function confirmRestock(): void
        {
            DB::transaction(function (): void {
                /** @var Inventory $inventory */
                $inventory = Inventory::query()->where('sku', $this->order->sku)
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['sku' => $this->order->sku],
                        ['qty_available' => 0, 'qty_reserved' => 0],
                    );

                $inventory->increment('qty_reserved', $this->order->qty);

                $this->order->transitionTo(OrderStatus::RESERVED);

                InventoryMovement::query()->create([
                    'sku' => $this->order->sku,
                    'order_id' => $this->order->id,
                    'type' => MovementType::RESTOCKED,
                    'qty' => $this->order->qty,
                ]);
            });

            Log::info('Order confirmed after supplier restock', ['order_id' => $this->order->id]);
        }

        private function markFailed(): void
        {
            $this->order->transitionTo(OrderStatus::FAILED);

            Log::warning('Order failed after supplier check', [
                'order_id' => $this->order->id,
                'delay_attempt' => $this->delayAttempt,
            ]);
        }

        private function handleDelayed(): void
        {
            $nextAttempt = $this->delayAttempt + 1;

            if ($nextAttempt >= self::MAX_DELAY_RETRIES) {
                $this->markFailed();

                Log::warning('Order failed: max delay retries reached', [
                    'order_id' => $this->order->id,
                    'max_retries' => self::MAX_DELAY_RETRIES,
                ]);

                return;
            }

            Log::info('Supplier delayed, scheduling retry', [
                'order_id' => $this->order->id,
                'next_attempt' => $nextAttempt,
                'max' => self::MAX_DELAY_RETRIES,
            ]);

            self::dispatch($this->order, $nextAttempt)
                ->delay(now()->addSeconds(15));
        }
    }
