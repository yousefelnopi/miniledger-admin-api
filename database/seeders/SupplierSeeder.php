<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // ================= Supplier 1 =================
        Supplier::firstOrCreate(
            ['email' => 'supplier1@example.com'],
            [
                'name' => 'AutoParts Co.',
                'phone' => '+201234567890',
                'address' => '123 Main Street, Cairo, Egypt',
                'notes' => 'Reliable supplier for engine parts',
                'image' => 'supplier1.jpg',
                'is_active' => true,
            ]
        );

        // ================= Supplier 2 =================
        Supplier::firstOrCreate(
            ['email' => 'supplier2@example.com'],
            [
                'name' => 'Speedy Motors',
                'phone' => '+201112223334',
                'address' => '45 Industrial Zone, Alexandria, Egypt',
                'notes' => 'Specializes in suspension and brakes',
                'image' => 'supplier2.jpg',
                'is_active' => true,
            ]
        );

        // ================= Supplier 3 =================
        Supplier::firstOrCreate(
            ['email' => 'supplier3@example.com'],
            [
                'name' => 'Premium Auto Supply',
                'phone' => '+201998877665',
                'address' => '78 Market Street, Giza, Egypt',
                'notes' => 'High quality accessories and filters',
                'image' => 'supplier3.jpg',
                'is_active' => true,
            ]
        );
    }
}
