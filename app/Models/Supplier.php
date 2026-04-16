<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company_name',
        'supplier_type',
        'balance',
        'due_amount',
        'credit_limit',
        'address',
        'billing_address',
        'city',
        'state',
        'zip_code',
        'country',
        'contact_person',
        'tax_number',
        'notes',
        'total_purchases',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    public function getFormattedDueAmountAttribute()
    {
        return 'Tk' . number_format($this->due_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'Tk' . number_format($this->balance, 2);
    }

    public function getFormattedTotalPurchasesAttribute()
    {
        return 'Tk' . number_format($this->total_purchases, 2);
    }

    public function getFormattedCreditLimitAttribute()
    {
        return 'Tk' . number_format($this->credit_limit, 2);
    }
}
