<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

final class OrderController extends Controller
{
    /**
     * POST /api/orders
     *
     * Create a new order and trigger inventory reservation.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::query()->create([
            'sku'    => $request->string('sku')->toString(),
            'qty'    => $request->integer('qty'),
            'status' => OrderStatus::PENDING,
        ]);

        event(new OrderCreated($order));

        return response()->json([
            'data'    => $this->formatOrder($order),
            'message' => 'Замовлення прийнято в обробку.',
        ], 201);
    }

    /**
     * GET /api/orders/{id}
     *
     * Retrieve a single order with its inventory movements.
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::query()->with('movements')->findOrFail($id);

        return response()->json([
            'data' => $this->formatOrder($order, withMovements: true),
        ]);
    }

    /** @return array<string, mixed> */
    private function formatOrder(Order $order, bool $withMovements = false): array
    {
        $result = [
            'id' => $order->id,
            'sku' => $order->sku,
            'qty' => $order->qty,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'supplier_ref' => $order->supplier_ref,
            'created_at' => $order->created_at->toIso8601String(),
            'updated_at' => $order->updated_at->toIso8601String(),
        ];

        if ($withMovements) {
            $result['movements'] = $order->movements->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type->value,
                'type_label' => $m->type->label(),
                'qty' => $m->qty,
                'created_at' => $m->created_at->toIso8601String(),
            ])->all();
        }

        return $result;
    }
}
