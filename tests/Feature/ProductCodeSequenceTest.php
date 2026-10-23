<?php

use App\Concerns\GeneratesProductIdentifiers;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

class ProductCodeSequenceProbe
{
    use GeneratesProductIdentifiers;

    public function nextProductCode(array $exclude = []): string
    {
        return $this->generateNextProductCode($exclude);
    }

    public function nextBarcode(array $exclude = []): string
    {
        return $this->generateNextBarcode($exclude);
    }
}

function sequenceProduct(string $code, string $barcode): Product
{
    return Product::query()->create([
        'name'           => 'Product ' . $code,
        'code'           => $code,
        'barcode'        => $barcode,
        'price'          => 1,
        'sale_price'     => 1,
        'stock_quantity' => 0,
        'is_active'      => true,
    ]);
}

function sequenceVariation(string $code, ?string $barcode = null): void
{
    \Illuminate\Support\Facades\DB::table('product_variations')->insert([
        'product_id'      => 1,
        'variation_type'  => 'Stock',
        'variation_value' => 'Red - XS',
        'product_code'    => $code,
        'barcode'         => $barcode,
        'is_active'       => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
}

test('product codes fill the lowest free gap starting from BB01', function () {
    $probe = new ProductCodeSequenceProbe();

    expect($probe->nextProductCode())->toBe('BB01');

    sequenceProduct('BB01', 'BB250000001');

    expect($probe->nextProductCode())->toBe('BB02');
    expect($probe->nextBarcode())->toBe('BB250000002');

    // Pre-existing out-of-order codes (BB13, BB15) must not stop gap filling.
    sequenceProduct('BB13', 'BB250000013');
    sequenceProduct('BB15', 'BB250000015');

    expect($probe->nextProductCode())->toBe('BB02');
});

test('the product series stays 1:1 with the number of products', function () {
    $probe = new ProductCodeSequenceProbe();

    $codes = [];
    for ($i = 1; $i <= 5; $i++) {
        $code = $probe->nextProductCode();
        $codes[] = $code;
        sequenceProduct($code, 'BB' . (250000000 + $i));
    }

    expect($codes)->toBe(['BB01', 'BB02', 'BB03', 'BB04', 'BB05']);
});

test('a freed product code gap is reused', function () {
    $probe = new ProductCodeSequenceProbe();

    sequenceProduct('BB01', 'BB250000001');
    $second = sequenceProduct('BB02', 'BB250000002');

    expect($probe->nextProductCode())->toBe('BB03');

    $second->delete();

    expect($probe->nextProductCode())->toBe('BB02');
});

test('variations never take or block a product number', function () {
    $probe = new ProductCodeSequenceProbe();

    sequenceProduct('BB01', 'BB250000001');

    // A legacy variation still carrying a BB code must not reserve BB02:
    // variations are identified by id + variation_value, not by a product code.
    sequenceVariation('BB02');

    expect($probe->nextProductCode())->toBe('BB02');
});

test('generated code and barcode always share the same sequence', function () {
    $probe = new ProductCodeSequenceProbe();

    sequenceProduct('BB01', 'BB250000001');

    $pair = ['code' => $probe->nextProductCode(), 'barcode' => $probe->nextBarcode()];

    expect($pair['code'])->toBe('BB02');
    expect($pair['barcode'])->toBe('BB250000002');
});
