<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sku', 'qty_available', 'qty_reserved'])]
class Inventory extends Model
{
    protected $casts = [
        'qty_available' => 'integer',
        'qty_reserved' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'sku', 'sku');
    }
}
