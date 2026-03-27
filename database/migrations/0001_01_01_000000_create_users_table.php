<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الموظف
            $table->string('email')->unique(); // اسم المستخدم للدخول
            $table->string('password');

            // --- إضافات مهمة للـ POS ---
            $table->tinyInteger('role')->default('2'); // الصلاحية[1=> Admin, 2=> Cashier]
            $table->string('api_token', 80)->unique()->nullable()->default(null); // عشان ربط جهاز الكاشير
            $table->boolean('is_active')->default(true); // حالة الموظف
            $table->timestamp('last_seen_at')->nullable();
            // ---------------------------

            $table->timestamp('email_verified_at')->nullable(); // ممكن تسيبه عادي
            $table->rememberToken();
            $table->timestamps();
        });

        // جدول استعادة كلمة المرور (سيبه زي ما هو)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // جدول الجلسات (سيبه زي ما هو ومفيد جداً)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
