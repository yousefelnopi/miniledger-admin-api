<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    /**
     * 🔹 Mass Assignment
     */
    protected $fillable = [
        'brand',
        'model',
        'year',
        'engine',
        'trim',
    ];

    /**
     * 🔹 Type Casting
     */
    protected $casts = [
        'year' => 'integer',
    ];

    /**
     * 🔹 العلاقة مع المنتجات (Many-to-Many)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'car_model_product');
    }

    /**
     * 🔥 Accessor (اسم كامل جاهز للعرض)
     */
    public function getFullNameAttribute()
    {
        return "{$this->brand} {$this->model} {$this->year}";
    }

    /**
     * 🔥 Scope للبحث السريع
     */
    public function scopeSearch($query, $q)
    {
        return $query->whereFullText(['brand', 'model'], $q)
            ->orWhere('brand', 'like', "%{$q}%")
            ->orWhere('model', 'like', "%{$q}%")
            ->orWhere('year', 'like', "%{$q}%")
            ->orWhere('engine', 'like', "%{$q}%");
    }
}
