<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('legacy generated barcodes are changed to their product codes', function () {
    $productId = DB::table('products')->insertGetId([
        'name'           => 'Legacy barcode product',
        'code'           => 'BB04',
        'barcode'        => 'BB250000004',
        'price'          => 1,
        'sale_price'     => 1,
        'stock_quantity' => 0,
        'is_active'      => true,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    (require database_path('migrations/2026_11_10_000001_make_product_barcodes_match_product_codes.php'))->up();

    expect(DB::table('products')->where('id', $productId)->value('barcode'))->toBe('BB04');
});
