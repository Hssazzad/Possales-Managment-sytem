<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Seller-facing scan identifiers.
 *
 * Product Code and Product Barcode are the ONLY product identifiers.
 * SKU / variation codes / unit codes must never resolve a product, otherwise
 * one value can match two different products (the BB12 collision).
 */
function makeScanResolverProduct(string $name, string $code, string $barcode): Product
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

test('scanner resolves a product by its exact product code', function () {
    $user = User::factory()->create();
    $pant = makeScanResolverProduct('Pant', 'BB01', 'BB250000001');

    $this->actingAs($user)
        ->getJson('/pos/get-product/BB01')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('product.id', $pant->id)
        ->assertJsonPath('product.code', 'BB01');
});

test('scanner resolves a product by its exact product barcode', function () {
    $user = User::factory()->create();
    $shirt = makeScanResolverProduct('Shirt', 'BB15', 'BB250000015');

    $this->actingAs($user)
        ->getJson('/pos/get-product/BB250000015')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('product.id', $shirt->id);
});

test('product barcode takes priority over product code when both could match', function () {
    $user = User::factory()->create();
    // Pathological data: one product's barcode equals another product's code.
    $byBarcode = makeScanResolverProduct('By Barcode', 'BB77', 'BB250000099');
    makeScanResolverProduct('By Code', 'BB250000099', 'BB250000098');

    $this->actingAs($user)
        ->getJson('/pos/get-product/BB250000099')
        ->assertOk()
        ->assertJsonPath('product.id', $byBarcode->id);
});

test('a value that only exists as a variation or unit code never resolves to a product', function () {
    $user = User::factory()->create();
    $pant  = makeScanResolverProduct('Pant', 'BB01', 'BB250000001');
    $shirt = makeScanResolverProduct('Shirt', 'BB15', 'BB250000015');

    // BB12 lives only in the retired namespaces: a variation code on one
    // product and a unit code on another. Before the fix, an unordered
    // "barcode OR code OR variation OR unit -> first()" query returned the
    // wrong product here.
    DB::table('product_variations')->insert([
        'product_id'      => $pant->id,
        'variation_type'  => 'Color - Size',
        'variation_value' => 'Red - XS',
        'product_code'    => 'BB12',
        'barcode'         => null,
        'is_active'       => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    DB::table('product_units')->insert([
        'product_id'   => $shirt->id,
        'unit_code'    => 'BB12',
        'product_code' => 'BB12',
        'barcode'      => 'BB250000015',
        'status'       => 'available',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    $this->actingAs($user)
        ->getJson('/pos/get-product/BB12')
        ->assertStatus(404)
        ->assertJsonPath('success', false);
});

test('an unknown scan value returns not found', function () {
    $user = User::factory()->create();
    makeScanResolverProduct('Pant', 'BB01', 'BB250000001');

    $this->actingAs($user)
        ->getJson('/pos/get-product/BB999')
        ->assertStatus(404)
        ->assertJsonPath('success', false);
});
