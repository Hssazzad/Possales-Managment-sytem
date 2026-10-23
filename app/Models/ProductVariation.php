<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    use HasFactory;

    protected $table = 'product_variations';

    protected $fillable = [
        'product_id',
        'variation_type',
        'variation_value',
        'price',
        'purchase_price',
        'extra_price',
        'stock',
        'product_code',
        'barcode',
        'is_active',
    ];

    public function getSkuAttribute(): ?string
    {
        return $this->attributes['product_code'] ?? null;
    }

    public function setSkuAttribute(?string $value): void
    {
        $this->attributes['product_code'] = $value;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class, 'product_variation_id');
    }
}
