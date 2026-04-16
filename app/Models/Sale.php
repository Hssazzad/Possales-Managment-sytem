<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'paid_amount',
        'change_amount',
        'customer_id',
        'customer_name',
        'customer_phone',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return '$' . number_format($this->discount_amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return '$' . number_format($this->paid_amount, 2);
    }

    public function getFormattedChangeAmountAttribute()
    {
        return '$' . number_format($this->change_amount, 2);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }
}
