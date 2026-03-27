<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    // ثوابت عشان نستخدمها في الكود بدل الأرقام (عشان الكود يبقى مقروء)
    const ROLE_ADMIN = 1;
    const ROLE_CASHIER = 2;

    /**
     * الحقول المسموح بتعبئتها
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',         // 1 or 2
        'api_token',    // توكن الربط
        'is_active',    // حالة الموظف
        'last_seen_at', // آخر ظهور
    ];

    /**
     * الحقول المخفية (عشان الأمان لما نرجع بيانات المستخدم JSON)
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token', // نخفيه عشان محدش يسرقه، ونظهره بس وقت تسجيل الدخول
    ];

    /**
     * تحويل البيانات لأنواعها الصحيحة
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'      => 'hashed',
            'is_active'     => 'boolean',  // يرجع true/false
            'last_seen_at'  => 'datetime', // يرجع كائن تاريخ
            'role'          => 'integer',
        ];
    }

    // ==============================
    // العلاقات (Relationships)
    // ==============================

    /**
     * الموظف (الكاشير) عمل فواتير كتير
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // ==============================
    // دوال مساعدة (Helper Methods)
    // ==============================
    // دي هتريحك جداً في الـ Controller والـ Blade عشان متكتبش أرقام

    // هل المستخدم ده أدمن؟
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // هل المستخدم ده كاشير؟
    public function isCashier()
    {
        return $this->role === self::ROLE_CASHIER;
    }

    // ==============================
    // Scopes (للفلترة)
    // ==============================

    // هاتلي الموظفين النشطين بس (عشان لو موظف مشي ميعرفش يدخل)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
