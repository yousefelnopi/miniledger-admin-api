<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::firstOrCreate([
            'part_number' => '95430-BX010'
        ], [
            'category_id' => 1,
            'supplier_id' => 1,
            'name' => 'Remote Key – Hyundai Creta 2022-24',
            'sku' => 'HYU-CRE-KEY-01',
            'brand' => 'Hyundai',
            'location_code' => 'A-01',
            'description' => 'Original Hyundai remote key with foldable 3+1 buttons.',
            'image' => 'remote_hyundai_creta.jpg',
            'buy_price' => 25.00,
            'sell_price' => 50.00,
            'stock_qty' => 10,
            'alert_stock' => 2,
            'is_active' => true,
        ]);

        Product::firstOrCreate([
            'part_number' => '8990H-48180'
        ], [
            'category_id' => 1,
            'supplier_id' => 2,
            'name' => 'Remote Key – Lexus RX 2024',
            'sku' => 'LEX-RX24-KEY-01',
            'brand' => 'Lexus',
            'location_code' => 'A-02',
            'description' => 'Genuine Lexus RX 2024 smart key with 3 buttons.',
            'image' => 'remote_lexus_rx.jpg',
            'buy_price' => 400.00,
            'sell_price' => 650.00,
            'stock_qty' => 5,
            'alert_stock' => 1,
            'is_active' => true,
        ]);

        Product::firstOrCreate([
            'part_number' => '28920-BU010'
        ], [
            'category_id' => 2,
            'supplier_id' => 3,
            'name' => 'Windshield Washer Pump – Nissan',
            'sku' => 'NIS-WASH-PUMP-01',
            'brand' => 'Nissan',
            'location_code' => 'B-01',
            'description' => 'OEM front windshield washer pump for Nissan models.',
            'image' => 'washer_pump_nissan.jpg',
            'buy_price' => 20.00,
            'sell_price' => 35.00,
            'stock_qty' => 12,
            'alert_stock' => 3,
            'is_active' => true,
        ]);

        Product::firstOrCreate([
            'part_number' => 'A0012306211'
        ], [
            'category_id' => 3,
            'supplier_id' => 2,
            'name' => 'Front Brake Pads – Mercedes Benz',
            'sku' => 'MBZ-FR-BRAKE-01',
            'brand' => 'Mercedes Benz',
            'location_code' => 'C-01',
            'description' => 'OEM Mercedes front brake pads for safe braking.',
            'image' => 'brake_pads_mb.jpg',
            'buy_price' => 80.00,
            'sell_price' => 120.00,
            'stock_qty' => 8,
            'alert_stock' => 2,
            'is_active' => true,
        ]);

        Product::firstOrCreate([
            'part_number' => '7SBU17C-6PK'
        ], [
            'category_id' => 3,
            'supplier_id' => 2,
            'name' => 'Auxiliary AC Compressor – Mercedes',
            'sku' => 'MBZ-AC-COMP-01',
            'brand' => 'Mercedes Benz',
            'location_code' => 'C-02',
            'description' => 'Genuine auxiliary AC compressor.',
            'image' => 'ac_compressor_mb.jpg',
            'buy_price' => 85.00,
            'sell_price' => 140.00,
            'stock_qty' => 6,
            'alert_stock' => 1,
            'is_active' => true,
        ]);
    }
}
