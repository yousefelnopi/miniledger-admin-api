<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;

class InventoryTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('email', 'yousefelnopi48@gmail.com')->first();
        $admin   = User::where('email', 'yousefelnopi2@gmail.com')->first();

        $product1 = Product::where('part_number', '95430-BX010')->first(); // Hyundai Key
        $product2 = Product::where('part_number', '8990H-48180')->first(); // Lexus Key
        $product3 = Product::where('part_number', '28920-BU010')->first(); // Washer Pump
        $product4 = Product::where('part_number', 'A0012306211')->first(); // Brake Pads
        $product5 = Product::where('part_number', '7SBU17C-6PK')->first(); // AC Compressor

        // ================== خصم بسبب بيع ==================
        InventoryTransaction::firstOrCreate([
            'product_id' => $product1->id,
            'user_id' => $cashier->id,
            'type' => 'sub',
            'quantity' => 2,
            'reason' => 'Sale to customer'
        ]);

        InventoryTransaction::firstOrCreate([
            'product_id' => $product2->id,
            'user_id' => $cashier->id,
            'type' => 'sub',
            'quantity' => 1,
            'reason' => 'Sale to customer'
        ]);

        InventoryTransaction::firstOrCreate([
            'product_id' => $product3->id,
            'user_id' => $cashier->id,
            'type' => 'sub',
            'quantity' => 1,
            'reason' => 'Sale to customer'
        ]);

        // ================== إضافة مخزون ==================
        InventoryTransaction::firstOrCreate([
            'product_id' => $product4->id,
            'user_id' => $admin->id,
            'type' => 'add',
            'quantity' => 5,
            'reason' => 'Supplier restock'
        ]);

        InventoryTransaction::firstOrCreate([
            'product_id' => $product5->id,
            'user_id' => $admin->id,
            'type' => 'add',
            'quantity' => 3,
            'reason' => 'Supplier restock'
        ]);
    }
}
