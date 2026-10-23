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
        'shipping_charge',
        'total_amount',
        'payment_method',
        'payment_status',
        'paid_amount',
        'due_amount',
        'change_amount',
        'customer_id',
        'customer_name',
        'customer_phone',
        'notes',
        'sale_date',
        'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'TK '.number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return 'TK '.number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return 'TK '.number_format($this->discount_amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return 'TK '.number_format($this->paid_amount, 2);
    }

    public function getFormattedChangeAmountAttribute()
    {
        return 'TK '.number_format($this->change_amount, 2);
    }

    public function getFormattedDueAmountAttribute()
    {
        return 'TK '.number_format($this->due_amount, 2);
    }

    public function getFormattedShippingChargeAttribute()
    {
        return 'TK '.number_format($this->shipping_charge, 2);
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getProfitAttribute()
    {
        $totalCost = $this->items->sum(function ($item) {
            $product = $item->product;

            return $product ? ($product->purchase_price * $item->quantity) : 0;
        });

        return $this->total_amount - $totalCost;
    }

    public function getFormattedProfitAttribute()
    {
        return 'TK '.number_format($this->profit, 2);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
            ->where('due_amount', '>', 0);
    }
}
