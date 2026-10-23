<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variation_id',
        'product_name',
        'product_code',
        'quantity',
        'unit_price',
        'discount',
        'total_price',
        'tax_rate',
        'tax_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function getFormattedUnitPriceAttribute()
    {
        return 'TK '.number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'TK '.number_format($this->total_price, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return 'TK '.number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAttribute()
    {
        return 'TK '.number_format($this->discount, 2);
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->unit_price > 0) {
            return ($this->discount / $this->unit_price) * 100;
        }

        return 0;
    }

    public function getFormattedDiscountPercentageAttribute()
    {
        return number_format($this->discount_percentage, 1).'%';
    }

    public function getSubtotalAttribute()
    {
        return $this->unit_price * $this->quantity;
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'TK '.number_format($this->subtotal, 2);
    }

    public function getProfitAttribute()
    {
        $product = $this->product;
        if ($product) {
            return ($this->unit_price - $product->purchase_price) * $this->quantity;
        }

        return 0;
    }

    public function getFormattedProfitAttribute()
    {
        return 'TK '.number_format($this->profit, 2);
    }

    public function getWeightedPriceAttribute()
    {
        return $this->quantity > 0 ? $this->total_price / $this->quantity : $this->unit_price;
    }

    public function getFormattedWeightedPriceAttribute()
    {
        return 'TK '.number_format($this->weighted_price, 2);
    }
}
