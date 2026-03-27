<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_requests', function (Blueprint $table) {
            $table->id();

            // المنتج الى الكاشير عايز يعدل كميته
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // الكاشير الى بعت الطلب
            $table->foreignId('cashier_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // الأدمن الى راجع الطلب (nullable لحد ما يتراجع)
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // نوع التعديل
            $table->enum('type', ['add', 'sub']);

            // الكمية المطلوب تعديلها
            $table->integer('quantity');

            // سبب الطلب
            $table->string('reason');

            // حالة الطلب
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->timestamps();
        });
    }

/*************  ✨ Windsurf Command ⭐  *************/
/**
 * Reverse the effects of the up method.
 */
/*******  6cc75c52-b2cb-4e82-a6f0-599e8be3eb88  *******/
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_requests');
    }
};
// ```

// ---

// ## ملاحظة على الـ `reviewed_by`
// ```
// pending  → reviewed_by = null   (لسه محدش شافه)
// approved → reviewed_by = أدمن  (الأدمن الى وافق)
// rejected → reviewed_by = أدمن  (الأدمن الى رفض)
