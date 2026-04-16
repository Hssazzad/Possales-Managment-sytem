<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'barcode',
        'brand',
        'model',
        'rack',
        'shelf',
        'pricing_type',
        'description',
        'price',
        'cost',
        'purchase_price',
        'sale_price',
        'mrp',
        'discount_percentage',
        'stock_quantity',
        'min_stock', // Kept for form handling
        'min_stock_level', // The actual DB column
        'category',
        'unit',
        'image',
        'is_active',
        'tax_rate',
        'manufacturing_date',
        'expire_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * ACCESSOR: Bridges the gap between code and DB.
     * Allows you to use $product->min_stock
     */
    public function getMinStockAttribute()
    {
        return $this->attributes['min_stock_level'] ?? 0;
    }

    /**
     * MUTATOR: Bridges the gap between code and DB.
     * Automatically maps 'min_stock' input to 'min_stock_level' column.
     */
    public function setMinStockAttribute($value)
    {
        $this->attributes['min_stock_level'] = $value;
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        $colors = [
            'electronics' => '007bff',
            'clothing' => '28a745',
            'food' => 'ffc107',
            'default' => '6c757d'
        ];

        $color = $colors[$this->category] ?? $colors['default'];
        return "https://via.placeholder.com/150x150/{$color}/ffffff?text=" . urlencode(substr($this->name, 0, 10));
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function isLowStock()
    {
        // This will now use the accessor to check min_stock_level
        return $this->stock_quantity <= $this->min_stock;
    }
}
