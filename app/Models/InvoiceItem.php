<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_name',
        'quantity',
        'buy_price',
        'unit_price',
        'total_row'
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'buy_price'  => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_row'  => 'decimal:2',
    ];

    // ==============================
    // العلاقات
    // ==============================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
