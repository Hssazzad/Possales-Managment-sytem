<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pos sale uses database sale price not client price and reduces stock', function () {
    $user = User::factory()->create();

    $customer = \App\Models\Customer::create([
        'name' => 'Test Customer',
        'phone' => '01'.rand(100000000, 999999999),
        'is_active' => true,
    ]);

    $product = Product::query()->create([
        'name' => 'POS Test Product',
        'code' => 'POS-'.uniqid(),
        'price' => 50.00,
        'sale_price' => 100.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/pos/process-sale', [
        'customer_id' => $customer->id,
        'items' => [
            [
                'id' => $product->id,
                'quantity' => 2,
                'discount' => 0,
                'price' => 1.00,
                'name' => 'Client tampered name',
            ],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 200,
        'vat_rate' => 0,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $product->refresh();
    expect((int) $product->stock_quantity)->toBe(8);

    $sale = Sale::query()->latest('id')->first();
    expect($sale)->not->toBeNull();
    expect((float) $sale->total_amount)->toBe(200.0);

    $line = $sale->saleItems()->first();
    expect((float) $line->unit_price)->toBe(100.0);
    expect($line->product_name)->toBe('POS Test Product');
});

test('pos sale rejects invalid customer id', function () {
    $user = User::factory()->create();

    $product = Product::query()->create([
        'name' => 'P',
        'code' => 'C-'.uniqid(),
        'price' => 10,
        'sale_price' => 10,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);

    $this->actingAs($user)->postJson('/pos/process-sale', [
        'items' => [['id' => $product->id, 'quantity' => 1]],
        'payment_method' => 'cash',
        'paid_amount' => 50,
        'customer_id' => 999_999,
    ])->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Selected customer not found. Please select a valid customer.',
        ]);
});
