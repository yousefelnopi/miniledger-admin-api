<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Customer;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $cashier   = User::where('email', 'yousefelnopi48@gmail.com')->first();
        $customer1 = Customer::where('name', 'Ahmed Hassan')->first();
        $customer2 = Customer::where('name', 'Mohamed Ali')->first();
        $customer3 = Customer::where('name', 'Sara Khaled')->first();

        // ================= Invoice 1 =================
        Invoice::firstOrCreate([
            'uuid' => 'INV-001'
        ], [
            'user_id'      => $cashier->id,
            'customer_id'  => $customer1->id,
            'total_amount' => 120.00,
            'discount'     => 0,
            'tax'          => 12.00,
            'type'         => 'sale', // ✅
            'is_synced'    => true
        ]);

        // ================= Invoice 2 =================
        Invoice::firstOrCreate([
            'uuid' => 'INV-002'
        ], [
            'user_id'      => $cashier->id,
            'customer_id'  => $customer2->id,
            'total_amount' => 185.00,
            'discount'     => 10.00,
            'tax'          => 17.50,
            'type'         => 'sale', // ✅
            'is_synced'    => true
        ]);

        // ================= Invoice 3 =================
        Invoice::firstOrCreate([
            'uuid' => 'INV-003'
        ], [
            'user_id'      => $cashier->id,
            'customer_id'  => $customer3->id,
            'total_amount' => 650.00,
            'discount'     => 0,
            'tax'          => 65.00,
            'type'         => 'return', // ✅ مثال على إرجاع
            'is_synced'    => false
        ]);
    }
}
