<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // ================= Customer 1 =================
        Customer::firstOrCreate(
            ['phone' => '+201234567890'],
            [
                'name' => 'Ahmed Hassan',
                'address' => '12 Street 1, Cairo, Egypt',
                'notes' => 'Regular customer, prefers engine parts',
            ]
        );

        // ================= Customer 2 =================
        Customer::firstOrCreate(
            ['phone' => '+201112223334'],
            [
                'name' => 'Mohamed Ali',
                'address' => '45 Industrial Zone, Alexandria, Egypt',
                'notes' => 'Buys brakes and suspension frequently',
            ]
        );

        // ================= Customer 3 =================
        Customer::firstOrCreate(
            ['phone' => '+201998877665'],
            [
                'name' => 'Sara Khaled',
                'address' => '78 Market Street, Giza, Egypt',
                'notes' => 'Interested in filters and fluids',
            ]
        );
    }
}
