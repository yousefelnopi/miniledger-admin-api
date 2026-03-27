<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentRequest extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustment_requests';

    protected $fillable = [
        'product_id',
        'cashier_id',
        'reviewed_by',
        'type',
        'quantity',
        'reason',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ==============================
    // العلاقات
    // ==============================

    // المنتج الى الطلب عليه
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // الكاشير الى بعت الطلب
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // الأدمن الى راجع الطلب
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==============================
    // Scopes
    // ==============================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
