<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventories = [
            ['sku' => 'SKU001', 'qty_available' => 100, 'qty_reserved' => 0],
            ['sku' => 'SKU002', 'qty_available' => 150, 'qty_reserved' => 0],
            ['sku' => 'SKU003', 'qty_available' => 200, 'qty_reserved' => 0],
        ];

        foreach ($inventories as $inventory) {
            Inventory::query()->updateOrCreate(
                ['sku' => $inventory['sku']],
                $inventory
            );
        }
    }
}
