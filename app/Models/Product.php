<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    // =========================================================================
    // FILLABLE — all columns the model may mass-assign
    // =========================================================================
    protected $fillable = [
        // Identity
        'name', 'code', 'sku', 'barcode',

        // Relations
        'category_id', 'brand_id', 'supplier_id', 'model_id',
        'rack_id', 'shelf_id', 'unit_id',

        // Specs
        'size_id', 'color_id',

        // Pricing
        'pricing_type',
        'price',        // alias kept for legacy reads
        'cost',         // alias kept for legacy reads
        'purchase_price',
        'sale_price',
        'mrp',
        'discount_percentage',
        'tax_rate',

        // Stock
        'opening_stock',
        'stock_quantity',
        'min_stock',        // virtual alias — maps to min_stock_level via accessor
        'min_stock_level',
        'low_stock_alert',  // used by bulk-upload

        // Content
        'description',
        'image',

        // Dates
        'manufacturing_date',
        'expire_date',

        // Status
        'is_active',
    ];

    // =========================================================================
    // APPENDS — accessors to include in JSON/array serialization
    // =========================================================================
    protected $appends = ['image_url'];

    // =========================================================================
    // CASTS — automatic type conversion on get/set
    // =========================================================================
    protected $casts = [
        'price'               => 'decimal:2',
        'cost'                => 'decimal:2',
        'purchase_price'      => 'decimal:2',
        'sale_price'          => 'decimal:2',
        'mrp'                 => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_rate'            => 'decimal:2',
        'opening_stock'       => 'integer',
        'stock_quantity'      => 'integer',
        'min_stock_level'     => 'integer',
        'low_stock_alert'     => 'integer',
        'is_active'           => 'boolean',
        'manufacturing_date'  => 'date',
        'expire_date'         => 'date',
    ];

    // =========================================================================
    // ACCESSORS & MUTATORS
    // =========================================================================

    /**
     * Virtual "min_stock" attribute that reads/writes "min_stock_level".
     * Keeps backward-compat with any code using $product->min_stock.
     */
    public function getMinStockAttribute(): int
    {
        return (int) ($this->attributes['min_stock_level'] ?? 0);
    }

    public function setMinStockAttribute(int $value): void
    {
        $this->attributes['min_stock_level'] = $value;
    }

    /**
     * Always return a valid image URL — never a null/broken src.
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            return asset('storage/' . $this->image);
        }

        return asset('images/no-product.png');
    }

    /**
     * Legacy string columns (brand/rack/shelf) share names with their
     * relations and would otherwise shadow them, always returning the
     * raw (null) column. Prefer the relation; fall back to raw value.
     */
    public function getBrandAttribute($value)
    {
        return $this->getRelationValue('brand') ?? $value;
    }

    public function getRackAttribute($value)
    {
        return $this->getRelationValue('rack') ?? $value;
    }

    public function getShelfAttribute($value)
    {
        return $this->getRelationValue('shelf') ?? $value;
    }

    /**
     * Null-safe size accessor — returns name instead of id for display.
     */
    public function getDisplaySizeAttribute(): string
    {
        return $this->size?->name ?? '-';
    }

    /**
     * Null-safe color accessor — returns name instead of id for display.
     */
    public function getDisplayColorAttribute(): string
    {
        return $this->color?->name ?? '-';
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id');
    }

    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Named "productVariations" to avoid conflict with Eloquent's own
     * "variations()" method name that some packages reserve.
     */
    public function productVariations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    /**
     * Alias kept so existing code using ->variations still works.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }

    /**
     * Get the sum of all variation quantities.
     */
    public function sumOfStock(): int
    {
        return (int) ($this->stocks->sum('quantity') ?: 0);
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expire_date')
                     ->where('expire_date', '<', now()->format('Y-m-d'));
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name',    'like', "%{$search}%")
              ->orWhere('code',    'like', "%{$search}%")
              ->orWhere('sku',     'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Returns true if this product is running low on stock.
     */
    public function isLowStock(): bool
    {
        return ($this->stock_quantity ?? 0) <= ($this->min_stock_level ?? 0);
    }

    /**
     * Payload used when printing barcode/QR labels.
     */
    public function getLabelScanPayload(): array
    {
        $value = $this->barcode ?: ($this->sku ?: ($this->code ?: (string) $this->id));
        $isEan13 = preg_match('/^\d{13}$/', (string) $value) === 1;

        return [
            'value'  => $value,
            'format' => $isEan13 ? 'ean13' : 'code128',
        ];
    }

    /**
     * Best available short identifier for label display.
     */
    public function getLabelDisplaySku(): string
    {
        return $this->sku ?? $this->code ?? (string) $this->id;
    }
}
