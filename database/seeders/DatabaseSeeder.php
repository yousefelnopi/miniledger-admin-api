<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  المستخدمين
        $this->call([
            UserSeeder::class,
        ]);

        //  الموردين
        $this->call([
            SupplierSeeder::class,
        ]);

        // 3️⃣ الأقسام (Categories)
        $this->call([
            CategorySeeder::class,
        ]);

        // 4️⃣ العملاء
        $this->call([
            CustomerSeeder::class,
        ]);

        // 5️⃣ المنتجات
        $this->call([
            ProductSeeder::class,
        ]);

        // 6️⃣ الفواتير
        $this->call([
            InvoiceSeeder::class,
        ]);

        // 7️⃣ سطور الفواتير
        $this->call([
            InvoiceItemSeeder::class,
        ]);

        // 8️⃣ حركات المخزون (Adjustments)
        $this->call([
            InventoryTransactionSeeder::class,
        ]);
    }
}
