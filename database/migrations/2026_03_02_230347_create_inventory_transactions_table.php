<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            // Link to the product
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // الأدمن الى وافق على التعديل
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // الكاشير الى طلب التعديل (nullable لو التعديل من الأدمن مباشرة)
            $table->foreignId('cashier_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Transaction type: 'add' (increase stock) or 'sub' (decrease stock)
            $table->enum('type', ['add', 'sub']);

            // The amount added or removed
            $table->integer('quantity');

            // The reason for the adjustment
            $table->string('reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
