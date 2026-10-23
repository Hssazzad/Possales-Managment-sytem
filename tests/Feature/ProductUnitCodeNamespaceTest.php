<?php

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeNamespaceProduct(string $name, string $code, string $barcode): Product
{
    return Product::query()->create([
        'name'           => $name,
        'code'           => $code,
        'barcode'        => $barcode,
        'price'          => 10.00,
        'sale_price'     => 12.00,
        'stock_quantity' => 5,
        'is_active'      => true,
    ]);
}

test('generated unit codes use product code as prefix with zero-padded sequence', function () {
    $product = makeNamespaceProduct('Pant', 'BB01', 'BB250000001');

    $units = ProductUnit::generateUnitCodes($product->id, 3);

    expect($units)->toHaveCount(3);

    foreach ($units as $unit) {
        expect($unit->unit_code)->toStartWith('BB01-');
        // The legacy mirror column stays in step with unit_code.
        expect($unit->product_code)->toBe($unit->unit_code);
    }

    // Uniqueness holds across the namespace.
    expect($units->pluck('unit_code')->unique())->toHaveCount(3);

    // Check the specific format: BB01-001, BB01-002, BB01-003
    expect($units[0]->unit_code)->toBe('BB01-001');
    expect($units[1]->unit_code)->toBe('BB01-002');
    expect($units[2]->unit_code)->toBe('BB01-003');
});

test('different products get different unit code prefixes', function () {
    $product1 = makeNamespaceProduct('Pant', 'BB01', 'BB250000001');
    $product2 = makeNamespaceProduct('Shirt', 'SH02', 'SH250000002');

    $units1 = ProductUnit::generateUnitCodes($product1->id, 2);
    $units2 = ProductUnit::generateUnitCodes($product2->id, 2);

    expect($units1)->toHaveCount(2);
    expect($units2)->toHaveCount(2);

    // Product 1 units should start with BB01-
    foreach ($units1 as $unit) {
        expect($unit->unit_code)->toStartWith('BB01-');
    }

    // Product 2 units should start with SH02-
    foreach ($units2 as $unit) {
        expect($unit->unit_code)->toStartWith('SH02-');
    }

    // No overlap between the two sets
    $allCodes = $units1->pluck('unit_code')->merge($units2->pluck('unit_code'));
    expect($allCodes->unique())->toHaveCount(4);
});

test('unit codes sequence continues from existing units for same product', function () {
    $product = makeNamespaceProduct('Pant', 'BB01', 'BB250000001');

    // First batch of 3 units
    $units1 = ProductUnit::generateUnitCodes($product->id, 3);
    expect($units1[0]->unit_code)->toBe('BB01-001');
    expect($units1[2]->unit_code)->toBe('BB01-003');

    // Second batch of 2 units - should continue from 004
    $units2 = ProductUnit::generateUnitCodes($product->id, 2);
    expect($units2[0]->unit_code)->toBe('BB01-004');
    expect($units2[1]->unit_code)->toBe('BB01-005');
});

test('barcode remains shared for all units of same product', function () {
    $product = makeNamespaceProduct('Pant', 'BB01', 'BB250000001');

    $units = ProductUnit::generateUnitCodes($product->id, 5);

    expect($units)->toHaveCount(5);

    // All units should share the same barcode from the product
    $expectedBarcode = 'BB250000001';
    foreach ($units as $unit) {
        expect($unit->barcode)->toBe($expectedBarcode);
    }

    // But unit codes should be unique
    expect($units->pluck('unit_code')->unique())->toHaveCount(5);
});
