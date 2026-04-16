<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedIncomeAttribute()
    {
        // This would be calculated from actual income records
        // For now, return sample data
        return 0;
    }

    public function getTransactionCountAttribute()
    {
        // This would be calculated from actual income records
        // For now, return sample data
        return 0;
    }
}
