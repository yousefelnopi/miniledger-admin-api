<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // الحقول المسموح إدخالها
    protected $fillable = [
        'name',
        'image',
        'description',
        'parent_id',
        'sort_order',
        'is_active'
    ];

    // تحويل أنواع البيانات تلقائياً
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==============================
    // العلاقات (Relationships)
    // ==============================

    /**
     * جلب القسم الرئيسي (الأب) للقسم الحالي
     * Relationship: Belongs To (Self)
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * جلب الأقسام الفرعية (الأبناء) التابعة للقسم الحالي
     * Relationship: Has Many (Self)
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * جلب المنتجات التابعة لهذا القسم
     * Relationship: One To Many
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
