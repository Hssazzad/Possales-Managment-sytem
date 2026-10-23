<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category');
    }

    public function getFormattedExpenseCountAttribute(): int
    {
        return $this->expenses()->count();
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->expenses()->sum('amount'), 2);
    }
}
