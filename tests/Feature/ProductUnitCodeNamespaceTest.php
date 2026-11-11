<?php

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('units share their product barcode without a separate unit code', function () {
    $product = Product::query()->create([
        'name'           => 'Pant',
        'code'           => 'BB01',
        'barcode'        => 'BB01',
        'price'          => 10,
        'sale_price'     => 12,
        'stock_quantity' => 5,
        'is_active'      => true,
    ]);

    $units = ProductUnit::generateUnitCodes($product->id, 3);

    expect($units)->toHaveCount(3)
        ->and($units->pluck('barcode')->unique()->all())->toBe(['BB01'])
        ->and($units->pluck('unit_code')->filter())->toBeEmpty()
        ->and($units->pluck('product_code')->filter())->toBeEmpty();
});
