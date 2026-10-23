<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * The migration that re-packs existing product codes into a dense BB01..BBnn
 * series and clears the variation BB codes.
 */
function runRepackMigration(): void
{
    $migration = require database_path('migrations/2026_09_13_000002_repack_product_codes_into_dense_series.php');
    $migration->up();
}

function productRow(int $id, string $code, string $barcode): void
{
    DB::table('products')->insert([
        'id'             => $id,
        'name'           => 'Product ' . $code,
        'code'           => $code,
        'barcode'        => $barcode,
        'price'          => 1,
        'sale_price'     => 1,
        'stock_quantity' => 0,
        'is_active'      => 1,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);
}

test('existing products are repacked into a dense BB01..BBnn series', function () {
    productRow(62, 'BB01', 'BB250000001');
    productRow(63, 'BB13', 'BB250000013');
    productRow(73, 'BB15', 'BB250000015');

    runRepackMigration();

    $codes = DB::table('products')->orderBy('id')->pluck('code', 'id')->all();

    expect($codes)->toBe([62 => 'BB01', 63 => 'BB02', 73 => 'BB03']);

    $barcodes = DB::table('products')->orderBy('id')->pluck('barcode', 'id')->all();

    expect($barcodes)->toBe([
        62 => 'BB250000001',
        63 => 'BB250000002',
        73 => 'BB250000003',
    ]);
});

test('repacking keeps sku in step and releases variation codes', function () {
    productRow(62, 'BB01', 'BB250000001');
    productRow(63, 'BB13', 'BB250000013');

    // `products.sku` has no migration in this repo, so it may not exist on a
    // fresh schema; the migration itself guards with Schema::hasColumn.
    $hasSku = \Illuminate\Support\Facades\Schema::hasColumn('products', 'sku');

    if ($hasSku) {
        DB::table('products')->where('id', 62)->update(['sku' => 'BB01']);
        DB::table('products')->where('id', 63)->update(['sku' => 'BB13']);
    }

    DB::table('product_variations')->insert([
        'product_id'      => 62,
        'variation_type'  => 'Stock',
        'variation_value' => 'Red - XS',
        'product_code'    => 'BB12',
        'barcode'         => 'BB250000012',
        'is_active'       => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    runRepackMigration();

    if ($hasSku) {
        expect(DB::table('products')->where('id', 63)->value('sku'))->toBe('BB02');
    }

    $variation = DB::table('product_variations')->first();
    expect($variation->product_code)->toBeNull();
    expect($variation->barcode)->toBeNull();
});

test('the next generated code continues the dense series after repacking', function () {
    productRow(62, 'BB01', 'BB250000001');
    productRow(63, 'BB13', 'BB250000013');
    productRow(73, 'BB15', 'BB250000015');

    runRepackMigration();

    $probe = new class {
        use App\Concerns\GeneratesProductIdentifiers;

        public function next(): string
        {
            return $this->generateNextProductCode();
        }
    };

    expect($probe->next())->toBe('BB04');
});
