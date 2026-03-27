<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // 1. الأساسيات (بتاعتك)
            $table->string('name');
            $table->string('image')->nullable();

            // 2. إضافات مقترحة (تقدر تمسح اللي مش محتاجه)
            $table->text('description')->nullable(); // وصف القسم

            // عشان التصنيفات الفرعية (لو القسم ملوش أب، بيبقى لاشئ ويعتبر قسم رئيسي)
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            // ترتيب القسم في واجهة المستخدم
            $table->integer('sort_order')->default(0);

            // 3. حالة التفعيل (بتاعتك)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
