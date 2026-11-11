<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_code',
        'product_variation_id',
        'product_code',
        'barcode',
        'status',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public static function generateUnitCodes(
        int $productId,
        int $quantity,
        ?int $startSerial = null,
        ?int $productVariationId = null
    ): Collection {
        if ($quantity <= 0) {
            return collect();
        }

        return DB::transaction(function () use ($productId, $quantity, $productVariationId): Collection {
            $product = Product::findOrFail($productId);
            $created = collect();

            for ($index = 0; $index < $quantity; $index++) {
                $attributes = [
                    'product_id' => $productId,
                    'barcode' => $product->barcode,
                    'status' => 'available',
                ];

                if ($productVariationId !== null) {
                    $attributes['product_variation_id'] = $productVariationId;
                }

                $created->push(static::create($attributes));
            }

            return $created;
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
