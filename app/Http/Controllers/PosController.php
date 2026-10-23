<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPosSaleRequest;
use App\Models\Customer;
use App\Models\HeldSale;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PosController extends Controller
{
    // ─────────────────────────────────────────
    // POS Page
    // ─────────────────────────────────────────
    public function index()
    {
        return view('admin.pos.index');
    }

    // ─────────────────────────────────────────
    // Product List (simple, no variation)
    // ─────────────────────────────────────────
    public function productList()
    {
        $products = Product::where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->with(['productVariations' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->get()
            ->map(function ($product) {
                return $this->formatProduct($product);
            });

        return response()->json($products);
    }

    // ─────────────────────────────────────────
    // POS Search (with variation + category filter)
    // ─────────────────────────────────────────
    public function posSearch(Request $request)
    {
        $search   = $request->get('search', '');
        $category = $request->get('category', 'all');

        $query = Product::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category_id', $category);
        }

        $products = $query
            ->with(['productVariations' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->limit(50)
            ->get()
            ->map(function ($product) {
                return $this->formatProduct($product);
            });

        return response()->json($products);
    }

    // ─────────────────────────────────────────
    // Search Products (alias of posSearch)
    // ─────────────────────────────────────────
    public function searchProducts(Request $request)
    {
        return $this->posSearch($request);
    }

    // ─────────────────────────────────────────
    // Customer Search
    // ─────────────────────────────────────────
    public function customerSearch(Request $request)
    {
        $query = Customer::select('id', 'name', 'phone');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->limit(30)->get());
    }

    // ─────────────────────────────────────────
    // Process Sale — MAIN method (variation fixed)
    // ─────────────────────────────────────────
    public function processSale(ProcessPosSaleRequest $request)
    {
        $data = array_replace([
            'customer_id'     => null,
            'customer_name'   => null,
            'customer_phone'  => null,
            'vat_rate'        => null,
            'discount_type'   => 'flat',
            'discount_value'  => null,
            'shipping'        => null,
            'shipping_charge' => null,
            'paid_amount'     => null,
            'notes'           => null,
        ], $request->validated());

        $items = $data['items'];

        DB::beginTransaction();

        try {
            // ── Load products with lock ──
            $productIds = collect($items)->pluck('id')->unique()->values()->all();
            $products   = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'One or more products are no longer available.',
                ], 422);
            }

            $lineSubtotals     = [];
            $lineDiscountParts = [];
            $resolvedLines     = [];

            foreach ($items as $item) {
                $product     = $products->get($item['id']);
                $qty         = (int) $item['quantity'];
                $variationId = $item['variation_id'] ?? null;

                // ── Product active check ──
                if ($product->is_active === false) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product is inactive: ' . $product->name,
                    ], 422);
                }

                // ── Stock check ──
                if ($variationId) {
                    // Variation stock check
                    $variation = ProductVariation::where('id', $variationId)
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$variation) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid variation for ' . $product->name,
                        ], 422);
                    }

                    if ((int) $variation->stock < $qty) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock for ' . $product->name
                                . ' (' . $variation->variation_value . ').'
                                . ' Available: ' . $variation->stock,
                        ], 422);
                    }
                } else {
                    // Product stock check (no variation)
                    $stock = (int) ($product->stock_quantity ?? 0);
                    if ($stock < $qty) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock for ' . $product->name
                                . ' (available: ' . $stock . ').',
                        ], 422);
                    }
                }

                // ── Line price calculation ──
                $unitPrice       = (float) ($product->sale_price ?? $product->price ?? 0);

                // variation extra price যোগ করো
                if ($variationId && isset($variation)) {
                    $unitPrice += (float) ($variation->extra_price ?? 0);
                }

                $lineDiscountPct = (float) ($item['discount'] ?? 0);
                $gross           = $unitPrice * $qty;
                $lineDiscAmount  = round($gross * ($lineDiscountPct / 100), 2);
                $lineNet         = round($gross - $lineDiscAmount, 2);

                $lineSubtotals[]     = $lineNet;
                $lineDiscountParts[] = $lineDiscAmount;
                $resolvedLines[]     = [
                    'product'      => $product,
                    'variation_id' => $variationId,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'line_net'     => $lineNet,
                    'line_disc'    => $lineDiscAmount,
                ];
            }

            // ── Order totals ──
            $subtotal        = round(array_sum($lineSubtotals), 2);
            $lineDiscountSum = round(array_sum($lineDiscountParts), 2);

            $vatRate   = (float) ($data['vat_rate'] ?? 0);
            $taxAmount = round($subtotal * ($vatRate / 100), 2);

            $discType      = $data['discount_type'] ?? 'flat';
            $discVal       = (float) ($data['discount_value'] ?? 0);
            $orderDiscount = $discType === 'percent'
                ? round($subtotal * ($discVal / 100), 2)
                : round($discVal, 2);

            $shipping            = (float) ($data['shipping_charge'] ?? $data['shipping'] ?? 0);
            $totalAmount         = max(0, round($subtotal + $taxAmount - $orderDiscount + $shipping, 2));
            $totalDiscountAmount = round($lineDiscountSum + $orderDiscount, 2);

            $paidAmount  = (float) ($data['paid_amount'] ?? $totalAmount);
            $paidAmount  = max(0, $paidAmount);
            $tempInvoice = 'TEMP-' . str_replace('-', '', (string) Str::uuid());

            // ── Create sale ──
            $sale = Sale::create([
                'invoice_number'  => $tempInvoice,
                'customer_id'     => $data['customer_id'] ?: null,
                'customer_name'   => $data['customer_name'] ?? null,
                'customer_phone'  => $data['customer_phone'] ?? null,
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $totalDiscountAmount,
                'shipping_charge' => $shipping,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'due_amount'      => max(0, round($totalAmount - $paidAmount, 2)),
                'payment_method'  => $data['payment_method'],
                'payment_status'  => $paidAmount >= $totalAmount ? 'paid' : 'partial',
                'sale_date'       => now(),
                'user_id'         => auth()->id(),
                'notes'           => $data['notes'] ?? null,
            ]);

            $invoiceNumber = sprintf('INV-%s-%06d', now()->format('Ymd'), $sale->id);
            $sale->update(['invoice_number' => $invoiceNumber]);

            // ── Create sale items + deduct stock ──
            $vatToAllocate = $taxAmount;
            $allocatedVat  = 0.0;
            $lineCount     = count($resolvedLines);

            foreach ($resolvedLines as $idx => $line) {
                $product     = $line['product'];
                $lineNet     = $line['line_net'];
                $qty         = $line['quantity'];
                $unitPrice   = $line['unit_price'];
                $variationId = $line['variation_id'];

                // VAT allocation
                if ($idx === $lineCount - 1) {
                    $lineVat = round($vatToAllocate - $allocatedVat, 2);
                } else {
                    $share        = $subtotal > 0 ? ($lineNet / $subtotal) : 0;
                    $lineVat      = round($vatToAllocate * $share, 2);
                    $allocatedVat += $lineVat;
                }

                SaleItem::create([
                    'sale_id'      => $sale->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code ?? '',
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $lineNet,
                    'tax_rate'     => $vatRate,
                    'tax_amount'   => $lineVat,
                ]);

                // ── Stock deduct ──
                $deduct = max(1, (int) $qty);

                if ($variationId) {
                    // Variation stock deduct
                    $rowsUpdated = ProductVariation::query()
                        ->where('id', $variationId)
                        ->where('product_id', $product->id)
                        ->where('stock', '>=', $deduct)
                        ->update(['stock' => DB::raw('stock - ' . $deduct)]);
                } else {
                    // Product stock deduct
                    $rowsUpdated = Product::query()
                        ->where('id', $product->id)
                        ->where('stock_quantity', '>=', $deduct)
                        ->update(['stock_quantity' => DB::raw('stock_quantity - ' . $deduct)]);
                }

                if ($rowsUpdated !== 1) {
                    DB::rollBack();
                    Log::warning('POS stock decrement race', [
                        'product_id'   => $product->id,
                        'variation_id' => $variationId,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock changed while processing. Please try again.',
                    ], 409);
                }
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Sale processed successfully',
                'invoice_number' => $invoiceNumber,
                'sale_id'        => $sale->id,
                'total_amount'   => $totalAmount,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('POS Sale Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to complete sale. Please try again.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────
    // Hold Sale
    // ─────────────────────────────────────────
    public function holdSale(Request $request)
    {
        $request->validate(['items' => 'required|array|min:1']);

        try {
            $data     = $request->all();
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                $discount  = ($item['discount'] ?? 0) / 100;
                $subtotal += $itemTotal * (1 - $discount);
            }

            $heldSale = HeldSale::create([
                'hold_token'     => Str::random(8),
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal'       => $subtotal,
                'tax_amount'     => 0,
                'total_amount'   => $subtotal,
                'cart_data'      => $data['items'],
                'user_id'        => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'hold_id' => $heldSale->hold_token,
            ]);

        } catch (\Throwable $e) {
            Log::error('Hold Sale Error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to hold sale.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────
    // Get Held Sales
    // ─────────────────────────────────────────
    public function getHeldSales()
    {
        $heldSales = HeldSale::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($heldSale) {
                return [
                    'id'            => $heldSale->id,
                    'hold_token'    => $heldSale->hold_token,
                    'customer_name' => $heldSale->customer_name ?? 'No name',
                    'total_amount'  => number_format($heldSale->total_amount, 2),
                    'created_at'    => $heldSale->created_at->format('M d, Y h:i A'),
                    'items_count'   => is_array($heldSale->cart_data)
                        ? count($heldSale->cart_data)
                        : count(json_decode($heldSale->cart_data, true) ?? []),
                ];
            });

        return response()->json($heldSales);
    }

    // ─────────────────────────────────────────
    // Resume Held Sale
    // ─────────────────────────────────────────
    public function resumeSale($holdId)
    {
        $heldSale = HeldSale::where('hold_token', $holdId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$heldSale) {
            return response()->json([
                'success' => false,
                'message' => 'Held sale not found',
            ], 404);
        }

        $cartData = is_array($heldSale->cart_data)
            ? $heldSale->cart_data
            : json_decode($heldSale->cart_data, true);

        $heldSale->delete();

        return response()->json(['success' => true, 'items' => $cartData]);
    }

    // ─────────────────────────────────────────
    // Today Stats
    // ─────────────────────────────────────────
    public function getTodayStats()
    {
        try {
            $todaySales   = Sale::whereDate('created_at', today())->get();
            $totalRevenue = $todaySales->sum('total_amount');
            $totalItems   = SaleItem::whereHas('sale', fn($q) => $q->whereDate('created_at', today()))->sum('quantity');

            return response()->json([
                'success'           => true,
                'total_sales'       => number_format($totalRevenue, 2),
                'transaction_count' => $todaySales->count(),
                'total_items'       => $totalItems,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success'           => true,
                'total_sales'       => '0.00',
                'transaction_count' => 0,
                'total_items'       => 0,
            ]);
        }
    }

    // ─────────────────────────────────────────
    // Other helpers
    // ─────────────────────────────────────────
    public function openCashDrawer()
    {
        return response()->json(['success' => true]);
    }

    public function generateReceipt($saleId)
    {
        $sale = Sale::with('items')->findOrFail($saleId);
        return response()->json(['success' => true, 'sale' => $sale]);
    }

    public function searchSales(Request $request)
    {
        $search = $request->get('search', '');
        $sales  = Sale::where('invoice_number', 'like', "%{$search}%")
            ->orWhere('customer_name', 'like', "%{$search}%")
            ->limit(20)
            ->get();

        return response()->json($sales);
    }

    // ─────────────────────────────────────────
    // Private helper — product format
    // ─────────────────────────────────────────
    private function formatProduct(Product $product): array
    {
        return [
            'id'                  => $product->id,
            'name'                => $product->name,
            'code'                => $product->code ?? '-',
            'sku'                 => $product->sku,
            'barcode'             => $product->barcode,
            'unit'                => $product->unit ?? 'pcs',
            'sale_price'          => (float) ($product->sale_price ?? 0),
            'price'               => (float) ($product->sale_price ?? 0),
            'stock_quantity'      => (int) ($product->stock_quantity ?? 0),
            'discount_percentage' => (float) ($product->discount_percentage ?? 0),
            'image_url'           => $product->image
                ? asset('storage/' . $product->image)
                : asset('images/no-image.png'),
            'variations'          => $product->productVariations->map(fn($v) => [
                'id'              => $v->id,
                'variation_value' => $v->variation_value,
                'extra_price'     => (float) ($v->extra_price ?? 0),
                'stock'           => (int) ($v->stock ?? 0),
                'sku'             => $v->sku ?? null,
            ])->values(),
        ];
    }
}