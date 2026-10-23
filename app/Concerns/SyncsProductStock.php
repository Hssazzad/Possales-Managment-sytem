<?php

namespace App\Concerns;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\ProductVariation;
use App\Models\Size;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

trait SyncsProductStock
{
    private function syncProductStock(int $productId, string $variationValue, int $delta): void
    {
        $parts = array_map('trim', explode(' - ', $variationValue));
        $colorName = $parts[0] ?? null;
        $sizeName = $parts[1] ?? null;

        $colorId = $colorName
            ? Color::where('name', $colorName)->value('id')
            : null;

        $sizeId = $sizeName
            ? Size::where('name', $sizeName)->value('id')
            : null;

        if (!$colorId && !$sizeId) return;

        $query = ProductStock::where('product_id', $productId);
        if ($colorId) $query->where('color_id', $colorId);
        if ($sizeId)  $query->where('size_id', $sizeId);

        if ($delta < 0) {
            $absDelta = abs($delta);
            $query->where('quantity', '>=', $absDelta)->decrement('quantity', $absDelta);
        } else {
            $query->increment('quantity', $delta);
        }
    }

    private function restoreStock($item, int $qty): void
    {
        Log::info('[Stock] restoreStock', ['sale_item_id' => $item->id, 'product_id' => $item->product_id, 'variation_id' => $item->product_variation_id, 'qty' => $qty]);
        if ($item->product_variation_id) {
            ProductVariation::where('id', $item->product_variation_id)
                ->increment('stock', $qty);

            $variation = ProductVariation::find($item->product_variation_id);
            if ($variation) {
                $this->syncProductStock($item->product_id, $variation->variation_value, $qty);
                $this->restoreProductUnits($item->product_variation_id, $qty);

                $newTotal = ProductVariation::where('product_id', $item->product_id)->sum('stock');
                Product::where('id', $item->product_id)->update(['stock_quantity' => $newTotal]);
                Log::info('[Stock] restoreStock variation updated', ['product_id' => $item->product_id, 'variation_id' => $item->product_variation_id, 'newTotal' => $newTotal]);
            }
        } else {
            Product::where('id', $item->product_id)
                ->increment('stock_quantity', $qty);
            Log::info('[Stock] restoreStock product updated', ['product_id' => $item->product_id, 'new_stock' => Product::find($item->product_id)?->stock_quantity]);
        }
    }

    private function returnableQuantity($item): int
    {
        return max(0, (int) $item->quantity - (int) ($item->returned_quantity ?? 0));
    }

    private function deductStock($item, int $qty): void
    {
        Log::info('[Stock] deductStock', ['sale_item_id' => $item->id, 'product_id' => $item->product_id, 'variation_id' => $item->product_variation_id, 'qty' => $qty]);
        if ($qty <= 0) return;

        if ($item->product_variation_id) {
            ProductVariation::where('id', $item->product_variation_id)
                ->where('stock', '>=', $qty)
                ->decrement('stock', $qty);

            // If stock went negative guard (concurrent), clamp at 0 via raw check
            $variation = ProductVariation::find($item->product_variation_id);
            if ($variation) {
                $this->syncProductStock($item->product_id, $variation->variation_value, -$qty);
                $this->markProductUnitsSold($item->product_variation_id, $qty);

                $newTotal = ProductVariation::where('product_id', $item->product_id)->sum('stock');
                Product::where('id', $item->product_id)->update(['stock_quantity' => max(0, (int) $newTotal)]);
                Log::info('[Stock] deductStock variation updated', ['product_id' => $item->product_id, 'variation_id' => $item->product_variation_id, 'newTotal' => $newTotal]);
            }
        } else {
            $before = Product::find($item->product_id)?->stock_quantity;
            Product::where('id', $item->product_id)
                ->where('stock_quantity', '>=', $qty)
                ->decrement('stock_quantity', $qty);
            $after = Product::find($item->product_id)?->stock_quantity;
            Log::info('[Stock] deductStock product updated', ['product_id' => $item->product_id, 'before' => $before, 'after' => $after, 'qty' => $qty]);
        }
    }

    private function markProductUnitsSold(int $variationId, int $qty): void
    {
        if ($qty <= 0 || !Schema::hasTable('product_units')) {
            return;
        }

        $unitIds = ProductUnit::where('product_variation_id', $variationId)
            ->where('status', 'available')
            ->orderBy('id')
            ->limit($qty)
            ->pluck('id');

        if ($unitIds->isNotEmpty()) {
            ProductUnit::whereIn('id', $unitIds)->update([
                'status' => 'sold',
                'sold_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info('[Stock] markProductUnitsSold', ['variation_id' => $variationId, 'qty' => $qty, 'unit_ids' => $unitIds->all()]);
        }
    }

    private function restoreProductUnits(int $variationId, int $qty): void
    {
        if ($qty <= 0 || !Schema::hasTable('product_units')) {
            return;
        }

        $unitIds = ProductUnit::where('product_variation_id', $variationId)
            ->where('status', 'sold')
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->limit($qty)
            ->pluck('id');

        if ($unitIds->isNotEmpty()) {
            ProductUnit::whereIn('id', $unitIds)->update([
                'status' => 'available',
                'sold_at' => null,
                'updated_at' => now(),
            ]);
            Log::info('[Stock] restoreProductUnits', ['variation_id' => $variationId, 'qty' => $qty, 'unit_ids' => $unitIds->all()]);
        }
    }
}
