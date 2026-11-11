<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function guardSupplierId(): int
{
    return DB::table('suppliers')->insertGetId([
        'name'       => 'Guard Supplier',
        'phone'      => '0000000000',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function guardProduct(string $name, string $code, string $barcode): Product
{
    return Product::query()->create([
        'name'           => $name,
        'code'           => $code,
        'barcode'        => $barcode,
        'price'          => 1,
        'sale_price'     => 1,
        'stock_quantity' => 0,
        'is_active'      => true,
    ]);
}

test('create rejects a tampered product code so the sequence cannot skip', function () {
    $user = User::factory()->create();
    guardProduct('First', 'BB01', 'BB250000001');

    $this->actingAs($user)
        ->post('/admin/products', [
            'name'           => 'Tampered',
            'supplier_id'    => guardSupplierId(),
            'purchase_price' => 10,
            'sale_price'     => 20,
            'code'           => 'BB99',
        ])
        ->assertSessionHasErrors('code');

    expect(Product::where('code', 'BB99')->exists())->toBeFalse();
});

test('create assigns the next sequential code and ignores a submitted barcode', function () {
    $user = User::factory()->create();
    guardProduct('First', 'BB01', 'BB250000001');

    $this->actingAs($user)
        ->post('/admin/products', [
            'name'           => 'Second',
            'supplier_id'    => guardSupplierId(),
            'purchase_price' => 10,
            'sale_price'     => 20,
            'code'           => 'BB02',
            // A tampered barcode must not be able to push the code to BB99.
            'barcode'        => 'BB250000099',
        ])
        ->assertRedirect();

    $created = Product::where('name', 'Second')->first();

    expect($created)->not->toBeNull();
    expect($created->code)->toBe('BB02');
    expect($created->barcode)->toBe('BB02');
});

test('create still works when no code is submitted at all', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/products', [
            'name'           => 'No Code',
            'supplier_id'    => guardSupplierId(),
            'purchase_price' => 10,
            'sale_price'     => 20,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(Product::where('name', 'No Code')->value('code'))->toBe('BB01');
});

test('update rejects changing an assigned product code', function () {
    $user = User::factory()->create();
    $product = guardProduct('First', 'BB01', 'BB250000001');

    $this->actingAs($user)
        ->put('/admin/products/' . $product->id, [
            'name'           => 'Renamed',
            'purchase_price' => 10,
            'sale_price'     => 20,
            'code'           => 'BB99',
        ])
        ->assertSessionHasErrors('code');

    $fresh = $product->fresh();
    expect($fresh->code)->toBe('BB01');
    expect($fresh->name)->toBe('First');
});

test('update succeeds when the code is left unchanged', function () {
    $user = User::factory()->create();
    $product = guardProduct('First', 'BB01', 'BB250000001');

    $this->actingAs($user)
        ->put('/admin/products/' . $product->id, [
            'name'           => 'Renamed',
            'purchase_price' => 10,
            'sale_price'     => 20,
            'code'           => 'BB01',
        ])
        ->assertRedirect();

    expect($product->fresh()->name)->toBe('Renamed');
    expect($product->fresh()->code)->toBe('BB01');
});
