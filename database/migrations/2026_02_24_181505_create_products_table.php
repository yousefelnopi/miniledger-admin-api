<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Link to the category. Prevent category deletion if it has products.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            /**
             * Link to the supplier.
             * nullable: Allows adding old products that have no specific supplier.
             * nullOnDelete: If the supplier is deleted, keep the product but set supplier_id to null.
             */
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            // The product's display name (e.g., Front Brake Pads)
            $table->string('name');

            // Barcode (SKU) used by the cashier scanner for fast selling
            $table->string('sku')->unique();

            // Original Factory Part Number (OEM).
            // Crucial field for the AI model to fetch compatible car models.
            $table->string('part_number')->index();

            // Manufacturer or brand of the part (e.g., Bosch, Toyota Genuine)
            $table->string('brand')->nullable();

            // Note: 'compatible_cars' was removed due to database normalization.
            // It will be handled via a many-to-many relationship (pivot table).

            // Physical storage location in the warehouse/store (e.g., Shelf A-Row 3)
            $table->string('location_code')->nullable();

            // Additional details and product picture
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Required image

            // Financials: Purchase cost and selling price to calculate profit
            $table->decimal('buy_price', 10, 2)->default(0);
            $table->decimal('sell_price', 10, 2)->default(0);

            // Inventory: Current available quantity and the low-stock warning limit
            $table->integer('stock_qty')->default(0);
            $table->integer('alert_stock')->default(5);

            // Product status: (true = active/sellable, false = disabled/hidden from POS)
            $table->boolean('is_active')->default(true);

            // Searchable fields
            $table->fullText(['name','brand','description','part_number']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
