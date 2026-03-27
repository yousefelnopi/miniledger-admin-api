<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\InvoiceItem;

class InvoiceItemSeeder extends Seeder
{
    public function run(): void
    {
        // ================ Invoice 1 =================
        $invoice1 = Invoice::where('uuid', 'INV-001')->first();
        $product1 = Product::where('part_number', '95430-BX010')->first(); // Hyundai Key
        InvoiceItem::firstOrCreate([
            'invoice_id' => $invoice1->id,
            'product_id' => $product1->id
        ], [
            'product_name' => $product1->name,
            'quantity' => 2,
            'buy_price' => $product1->buy_price,
            'unit_price' => $product1->sell_price,
            'total_row' => 2 * $product1->sell_price
        ]);

        // ================ Invoice 2 =================
        $invoice2 = Invoice::where('uuid', 'INV-002')->first();
        $product2 = Product::where('part_number', '28920-BU010')->first(); // Washer Pump
        $product3 = Product::where('part_number', 'A0012306211')->first(); // Brake Pads
        InvoiceItem::firstOrCreate([
            'invoice_id' => $invoice2->id,
            'product_id' => $product2->id
        ], [
            'product_name' => $product2->name,
            'quantity' => 1,
            'buy_price' => $product2->buy_price,
            'unit_price' => $product2->sell_price,
            'total_row' => 1 * $product2->sell_price
        ]);
        InvoiceItem::firstOrCreate([
            'invoice_id' => $invoice2->id,
            'product_id' => $product3->id
        ], [
            'product_name' => $product3->name,
            'quantity' => 1,
            'buy_price' => $product3->buy_price,
            'unit_price' => $product3->sell_price,
            'total_row' => 1 * $product3->sell_price
        ]);

        // ================ Invoice 3 =================
        $invoice3 = Invoice::where('uuid', 'INV-003')->first();
        $product4 = Product::where('part_number', '8990H-48180')->first(); // Lexus Key
        InvoiceItem::firstOrCreate([
            'invoice_id' => $invoice3->id,
            'product_id' => $product4->id
        ], [
            'product_name' => $product4->name,
            'quantity' => 1,
            'buy_price' => $product4->buy_price,
            'unit_price' => $product4->sell_price,
            'total_row' => 1 * $product4->sell_price
        ]);
    }
}
