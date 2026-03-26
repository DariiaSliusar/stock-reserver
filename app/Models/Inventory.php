<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sku', 'qty_available', 'qty_reserved'])]
class Inventory extends Model
{
        public function hasEnough(int $qty): bool
        {
            return $this->qty_available >= $qty;
        }
}
