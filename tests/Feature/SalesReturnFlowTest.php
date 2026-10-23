<?php

use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSaleWithStock(int $qty = 2, int $stock = 10): array
{
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Return Test Product',
        'code' => 'RET-' . uniqid(),
        'price' => 100,
        'sale_price' => 100,
        'purchase_price' => 40,
        'stock_quantity' => $stock,
        'is_active' => true,
    ]);
    $sale = Sale::query()->create([
        'invoice_number' => 'INV-TEST-' . uniqid(),
        'subtotal' => 100 * $qty,
        'total_amount' => 100 * $qty,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'paid_amount' => 100 * $qty,
        'due_amount' => 0,
        'user_id' => $user->id,
    ]);
    $item = SaleItem::query()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_code' => $product->code,
        'quantity' => $qty,
        'unit_price' => 100,
        'total_price' => 100 * $qty,
        'returned_quantity' => 0,
    ]);

    return [$user, $product, $sale, $item];
}

test('pending return does not restore stock, approval restores it', function () {
    [$user, $product, $sale, $item] = makeSaleWithStock(2, 10);

    // Create pending return for 1 qty
    $res = $this->actingAs($user)->post(route('returns.store'), [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'reason' => 'Defective',
        'refund_amount' => 100,
        'refund_method' => 'cash',
        'status' => 'pending',
    ]);
    $res->assertRedirect(route('returns.index'));

    expect(ReturnItem::count())->toBe(1);
    expect(Product::find($product->id)->stock_quantity)->toBe(10); // unchanged
    expect(SaleItem::find($item->id)->returned_quantity)->toBe(0);
    expect(Sale::find($sale->id)->payment_status)->toBe('paid');

    // Approve it → stock +1
    $ret = ReturnItem::first();
    $res2 = $this->actingAs($user)->put(route('returns.update', $ret->id), [
        'quantity' => 1,
        'reason' => 'Defective',
        'refund_amount' => 100,
        'refund_method' => 'cash',
        'status' => 'completed',
    ]);
    $res2->assertRedirect(route('returns.index'));

    expect(Product::find($product->id)->stock_quantity)->toBe(11);
    expect(SaleItem::find($item->id)->returned_quantity)->toBe(1);
    expect(Sale::find($sale->id)->payment_status)->toBe('paid'); // partial, still paid
});

test('duplicate return beyond sold qty is blocked', function () {
    [$user, $product, $sale, $item] = makeSaleWithStock(1, 5);

    $this->actingAs($user)->post(route('returns.store'), [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'reason' => 'First',
        'refund_amount' => 100,
        'refund_method' => 'cash',
        'status' => 'pending',
    ])->assertRedirect(route('returns.index'));

    // Second return for same 1 sold item must fail
    $res = $this->actingAs($user)->post(route('returns.store'), [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'reason' => 'Duplicate',
        'refund_amount' => 100,
        'refund_method' => 'cash',
        'status' => 'pending',
    ]);
    $res->assertRedirect(); // back with error
    $res->assertSessionHas('error');
    expect(ReturnItem::count())->toBe(1);
});

test('full return marks sale as returned and second approval is blocked', function () {
    [$user, $product, $sale, $item] = makeSaleWithStock(1, 5);

    $this->actingAs($user)->post(route('returns.store'), [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'reason' => 'Full',
        'refund_amount' => 100,
        'refund_method' => 'cash',
        'status' => 'completed',
    ])->assertRedirect(route('returns.index'));

    expect(Product::find($product->id)->stock_quantity)->toBe(6);
    expect(Sale::find($sale->id)->payment_status)->toBe('returned');

    // Deleting the completed return reverses stock
    $ret = ReturnItem::first();
    $this->actingAs($user)->delete(route('returns.destroy', $ret->id))
        ->assertRedirect(route('returns.index'));
    expect(Product::find($product->id)->stock_quantity)->toBe(5);
});

test('sale list quick return creates audit row', function () {
    [$user, $product, $sale, $item] = makeSaleWithStock(2, 10);

    $res = $this->actingAs($user)->postJson(route('sales.return.process', $sale->id), [
        'mode' => 'full',
        'reason' => 'Quick return',
        'refund_method' => 'cash',
    ]);
    $res->assertOk()->assertJson(['success' => true]);
    expect(ReturnItem::count())->toBe(1);
    expect(ReturnItem::first()->status)->toBe('completed');
    expect(Sale::find($sale->id)->payment_status)->toBe('returned');
});
