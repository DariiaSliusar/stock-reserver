<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sku', 'qty', 'status', 'supplier_ref', 'supplier_attempts',])]
class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class,
    ];
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
