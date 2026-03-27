<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    /**
     * Get inventory movements history
     *
     * GET /api/inventory/{sku}/movements
     */
    public function movements(string $sku): JsonResponse
    {
        $inventory = Inventory::query()->where('sku', $sku)->first();

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory not found',
            ], 404);
        }

        $movements = InventoryMovement::query()->where('sku', $sku)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sku' => $inventory->sku,
                'qty_available' => $inventory->qty_available,
                'qty_reserved' => $inventory->qty_reserved,
                'movements' => $movements->map(fn($movement) => [
                    'id' => $movement->id,
                    'order_id' => $movement->order_id,
                    'qty' => $movement->qty,
                    'type' => $movement->type->value,
                    'created_at' => $movement->created_at,
                    'order' => $movement->order ? [
                        'id' => $movement->order->id,
                        'status' => $movement->order->status->value,
                    ] : null,
                ]),
            ],
            'meta' => [
                'sku'   => $sku,
                'total' => $movements->count(),
            ]
        ]);
    }
}

