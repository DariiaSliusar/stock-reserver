<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'sku', 'qty', 'type'])]
class InventoryMovement extends Model
{
    protected $casts = [
        'type' => MovementType::class,
        'order_id' => 'integer',
        'qty' => 'integer',
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'sku', 'sku');
    }
}
