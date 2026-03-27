<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // 1. استدعاء كلاس النصوص

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', // 2. ضفناه هنا عشان نقدر نخزنه لما ييجي من الكاشير
        'name',
        'phone',
        'address',
        'notes'
    ];

    // ==============================
    // سحر لارافيل (Auto-Generate UUID)
    // ==============================

    /**
     * الدالة دي بتشتغل أوتوماتيك أول ما نيجي نحفظ عميل جديد
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // لو الـ uuid مش مبعوت (جاي فاضي)، احنا نكاريت واحد جديد
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // ==============================
    // العلاقات
    // ==============================
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
