<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id', // ربطنا المنتج بالمورد
        'name',
        'sku',          // الباركود
        'part_number',  // رقم القطعة (OEM)
        'brand',        // الماركة
        'location_code',   // مكان الرف (A-12)
        'description',
        'image',
        'buy_price',
        'sell_price',
        'stock_qty',
        'alert_stock',
        'is_active'
    ];

    /**
     * تحويل أنواع البيانات تلقائياً
     */
    protected $casts = [
        'is_active' => 'boolean',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'stock_qty' => 'integer',
        'alert_stock' => 'integer',
    ];

    // ==============================
    // العلاقات (Relationships)
    // ==============================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * المنتج ده بنجيبه من مورد معين
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // علاقة Many-to-Many
    public function carModels()
    {
        return $this->belongsToMany(CarModel::class, 'car_model_product');
    }

    // ==============================
    // Scopes (للبحث والفلترة الذكية)
    // ==============================

    /**
     * فلتر: هاتلي المنتجات النشطة بس (للكاشير)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * فلتر: هاتلي النواقص (اللي كميتها قلت عن حد الخطر)
     * الاستخدام: Product::lowStock()->get();
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'alert_stock');
    }

    // ==============================
    // Accessors (قيم محسوبة)
    // ==============================

    /**
     * ميزة إضافية: حساب هامش الربح تلقائياً
     * الاستخدام: $product->profit
     */
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->sell_price - $this->buy_price,
        );
    }

    /**
     * فلتر: هاتلي المنتجات المتوافقة مع موديل عربية معين
     * الاستخدام: Product::forCarModel(5)->get();
     */
    public function scopeForCarModel($query, $carModelId)
    {
        return $query->whereHas('carModels', function ($q) use ($carModelId) {
            // بنبحث برقم موديل العربية جوه جدول الـ car_models
            $q->where('car_models.id', $carModelId);
        });
    }
}
