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

        return DB::transaction(function () use ($productId, $quantity, $startSerial, $productVariationId): Collection {
            $product = Product::findOrFail($productId);

            // Use product's own SKU/code as prefix, fallback to a default
            $prefix = $product->code ?? $product->sku ?? 'PROD';

            // Create a unique sequence key per product+variation
            $sequenceKey = 'product_'.$productId.'_'.($productVariationId ?? 'base');

            $sequence = DB::table('unit_code_sequences')
                ->where('sequence_key', $sequenceKey)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                // Find the highest existing serial for this specific product+variation
                $highestExistingSerial = static::query()
                    ->where('product_id', $productId)
                    ->when($productVariationId, fn ($q) => $q->where('product_variation_id', $productVariationId))
                    ->pluck('unit_code')
                    ->map(function ($unitCode): int {
                        // Extract the sequence number after the hyphen (e.g., BB01-005 -> 5)
                        if (preg_match('/-(\d+)$/', (string) $unitCode, $matches)) {
                            return (int) $matches[1];
                        }
                        // Legacy format without hyphen (e.g., BB01 -> try to extract digits)
                        if (preg_match('/(\d+)$/', (string) $unitCode, $matches)) {
                            return (int) $matches[1];
                        }

                        return 0;
                    })
                    ->max() ?? 0;

                DB::table('unit_code_sequences')->insertOrIgnore([
                    'sequence_key' => $sequenceKey,
                    'current_value' => $highestExistingSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sequence = DB::table('unit_code_sequences')
                    ->where('sequence_key', $sequenceKey)
                    ->lockForUpdate()
                    ->first();
            }

            $serial = max(
                (int) $sequence->current_value,
                (int) ($startSerial ?? 0)
            );
            $barcode = $product->barcode;
            $created = collect();

            for ($index = 0; $index < $quantity; $index++) {
                do {
                    $serial++;
                    // Format: PREFIX-001, PREFIX-002, etc.
                    $unitCode = sprintf('%s-%03d', $prefix, $serial);
                } while (static::where('unit_code', $unitCode)->exists());

                $attributes = [
                    'product_id' => $productId,
                    'unit_code' => $unitCode,
                    'product_code' => $unitCode,
                    'barcode' => $barcode,
                    'status' => 'available',
                ];

                if ($productVariationId !== null) {
                    $attributes['product_variation_id'] = $productVariationId;
                }

                $created->push(static::create($attributes));
            }

            DB::table('unit_code_sequences')
                ->where('sequence_key', $sequenceKey)
                ->update([
                    'current_value' => $serial,
                    'updated_at' => now(),
                ]);

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
