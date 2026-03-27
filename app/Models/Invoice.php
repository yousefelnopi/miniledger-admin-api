<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'uuid',
        'user_id',
        'customer_id',
        'total_amount',
        'discount',
        'tax',
        'type',
        'is_synced',
    ];

    protected $casts = [
        'is_synced'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'total_amount' => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // ==============================
    // Scopes
    // ==============================

    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    public function scopeReturns($query)
    {
        return $query->where('type', 'return');
    }

    public function scopeSynced($query)
    {
        return $query->where('is_synced', true);
    }

    public function scopeNotSynced($query)
    {
        return $query->where('is_synced', false);
    }

    // ==============================
    // العلاقات
    // ==============================

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
