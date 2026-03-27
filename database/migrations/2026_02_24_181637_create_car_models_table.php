<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();

            // 🔹 البيانات الأساسية
            $table->string('brand');   // Toyota
            $table->string('model');   // Corolla
            $table->year('year');      // 2020

            // 🔹 بيانات اختيارية (مهمة لقطع الغيار)
            $table->string('engine')->nullable();   // 1.6L
            $table->string('trim')->nullable();     // GL / Sport

            $table->timestamps();

            // 🔒 منع التكرار الحقيقي
            $table->unique(
                ['brand', 'model', 'year', 'engine'],
                'car_unique_index'
            );

            // 🚀 Indexes (للـ Filters)
            $table->index('brand');
            $table->index('model');
            $table->index('year');

            // 🚀 Composite Indexes (للـ Queries المركبة)
            $table->index(['brand', 'model'], 'brand_model_index');
            $table->index(['brand', 'model', 'year'], 'full_search_index');

            // 🔥 Full-Text Search (للبحث الحر)
            $table->fullText(['brand', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};
