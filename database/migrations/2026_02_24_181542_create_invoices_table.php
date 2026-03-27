<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // الرقم المميز
            $table->string('uuid')->unique()->index();

            // مين الكاشير
            $table->foreignId('user_id')->constrained();

            // مين العميل
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            // الحسابات
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);

            // نوع العملية: بيع ولا إرجاع
            $table->enum('type', ['sale', 'return'])->default('sale');

            // هل اترفعت للسيرفر ولا لسه
            $table->boolean('is_synced')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
