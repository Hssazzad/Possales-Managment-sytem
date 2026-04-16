<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_token',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax_amount',
        'total_amount',
        'cart_data',
        'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cart_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
