<?php

use App\Models\ProductUnit;
use App\Models\ProductVariation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backfill product_units for variations that are missing individual unit records.
//
// Units live in their own reserved namespace (ProductUnit::formatUnitCode =>
// U00021) and their barcode mirrors the parent product's barcode. A unit never
// allocates a BB{seq} value, so the product codes stay 1:1 with the products.
Artisan::command('product-units:backfill', function () {
    if (!Schema::hasTable('product_units')) {
        $this->error('product_units table does not exist.');
        return;
    }

    $variations = ProductVariation::with('product')->get();
    $total      = $variations->count();
    $processed  = 0;
    $created    = 0;

    $this->info("Checking {$total} variation(s) for missing product_units...");
    $bar = $this->output->createProgressBar($total);
    $bar->start();

    foreach ($variations as $variation) {
        $target = max(0, (int) $variation->stock);
        $existing = ProductUnit::where('product_variation_id', $variation->id)
            ->where('status', 'available')
            ->count();

        $missing = $target - $existing;

        if ($missing > 0) {
            ProductUnit::generateUnitCodes($variation->product_id, $missing, null, $variation->id);
            $created += $missing;
        }

        $processed++;
        $bar->advance();
    }

    $bar->finish();
    $this->newLine();
    $this->info("Done! {$created} product_unit(s) created for {$processed} variation(s).");
})->purpose('Backfill product_units for variations missing individual unit records');
