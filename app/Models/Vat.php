<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vat extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 2).'%';
    }

    public function getRateAsDecimalAttribute(): float
    {
        return $this->rate / 100; // Convert percentage to decimal (e.g., 15.00 -> 0.15)
    }
}
