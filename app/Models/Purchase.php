<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
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
        'notes',
        'supplier_id',
        'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAmountAttribute(): string
    {
        return number_format($this->discount_amount, 2);
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return number_format($this->paid_amount, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('payment_status', '!=', 'cancelled');
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                  $supplierQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('company_name', 'like', "%{$search}%");
              });
        });
    }
}
