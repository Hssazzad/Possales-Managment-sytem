<?php

use App\Models\Product;

test('label scan payload prefers valid ean13 barcode over sku', function () {
    $product = new Product;
    $product->forceFill([
        'barcode' => '5901234123457',
        'sku' => 'SKU-999',
        'code' => 'INT-1',
    ]);

    expect($product->getLabelScanPayload())->toBe([
        'value' => '5901234123457',
        'format' => 'ean13',
    ]);
});

test('label scan payload falls back to code when barcode empty', function () {
    $product = new Product;
    $product->forceFill([
        'barcode' => null,
        'sku' => null,
        'code' => 'FALLBACK-CODE',
    ]);

    expect($product->getLabelScanPayload())->toBe([
        'value' => 'FALLBACK-CODE',
        'format' => 'code128',
    ]);
});

test('label scan payload uses sku when barcode empty', function () {
    $product = new Product;
    $product->forceFill([
        'barcode' => '',
        'sku' => 'MY-SKU',
        'code' => 'C1',
    ]);

    expect($product->getLabelScanPayload())->toBe([
        'value' => 'MY-SKU',
        'format' => 'code128',
    ]);
});

test('label display sku prefers sku over code', function () {
    $product = new Product;
    $product->forceFill(['sku' => 'S', 'code' => 'C']);

    expect($product->getLabelDisplaySku())->toBe('S');
});
