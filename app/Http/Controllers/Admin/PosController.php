<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPosSaleRequest;
use App\Models\Customer;
use App\Models\HeldSale;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariation;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with(['stocks.color', 'stocks.size', 'category'])
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->paginate(24);

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        $brands     = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $units      = \App\Models\Unit::where('is_active', true)->orderBy('name')->get();
        $racks      = \App\Models\Rack::where('is_active', true)->orderBy('name')->get();
        $shelves    = \App\Models\Shelf::where('is_active', true)->orderBy('name')->get();
        $colors     = \App\Models\Color::where('is_active', true)->orderBy('name')->get();
        $sizes      = \App\Models\Size::where('is_active', true)->orderBy('name')->get();

        return view('admin.pos.index', compact('products', 'categories', 'brands', 'units', 'racks', 'shelves', 'colors', 'sizes'));
    }

    public function productList()
    {
        $hasProductUnits = Schema::hasTable('product_units');
        $relations = [
            'productVariations' => function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            },
            'category',
            'brand',
            'unit',
        ];

        if ($hasProductUnits) {
            $relations['productVariations.units'] = function ($q) {
                $q->where('status', 'available');
            };
        }

        $products = Product::where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->with($relations)
            ->get()
            ->map(function ($product) {
                return $this->formatProduct($product);
            });

        return response()->json($products);
    }

    public function posSearch(Request $request)
    {
        $search   = $request->get('search', '');
        $category = $request->get('category', 'all');
        $hasProductUnits = Schema::hasTable('product_units');

        $query = Product::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            });

        if ($search) {
            $query->where(function ($q) use ($search, $hasProductUnits) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('productVariations', function($sq) use ($search) {
                        $sq->where('variation_value', 'like', "%{$search}%")
                           ->orWhere('product_code', 'like', "%{$search}%")
                           ->orWhere('barcode', 'like', "%{$search}%");
                    });

                if ($hasProductUnits) {
                    $q->orWhereHas('productUnits', function ($sq) use ($search) {
                        $sq->where('product_code', 'like', "%{$search}%")
                           ->orWhere('barcode', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category_id', $category);
        }

        $relations = [
            'productVariations' => function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            },
            'category',
            'brand',
            'unit',
        ];

        if ($hasProductUnits) {
            $relations['productVariations.units'] = function ($q) {
                $q->where('status', 'available');
            };
        }

        $products = $query
            ->with($relations)
            ->limit(100)
            ->get()
            ->map(function ($product) {
                return $this->formatProduct($product);
            });

        return response()->json($products);
    }

    public function searchProducts(Request $request)
    {
        return $this->posSearch($request);
    }

    public function searchProductsLive(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 1) return response()->json([]);

        $products = Product::where(function($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                      ->orWhere('barcode', 'like', '%'.$q.'%')
                      ->orWhere('code', 'like', '%'.$q.'%');
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->limit(12)
            ->get()
            ->map(function ($product) {
                return [
                    'id'      => $product->id,
                    'name'    => $product->name,
                    'barcode' => $product->barcode,
                    'code'    => $product->code,
                    'price'   => (float) ($product->sale_price ?? 0),
                    'stock'   => (int) ($product->stock_quantity ?? 0),
                    'image'   => $product->image
                        ? asset('storage/' . $product->image)
                        : '',
                ];
            });

        return response()->json($products);
    }

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

    public function quickCustomerStore(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
        ]);

        $customer = Customer::create([
            'name'      => $validated['name'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Customer added successfully!',
            'customer' => $customer
        ]);
    }

    public function processSale(ProcessPosSaleRequest $request)
    {
        $data = array_replace([
            'customer_id'      => null,
            'customer_name'    => null,
            'customer_phone'   => null,
            'customer_address' => null,
            'payment_method'   => 'cash',
            'vat_rate'         => null,
            'discount_type'    => 'flat',
            'discount_value'   => null,
            'shipping'         => null,
            'shipping_charge'  => null,
            'paid_amount'      => null,
            'notes'            => null,
        ], $request->validated());

        $items = $data['items'];

        DB::beginTransaction();

        try {
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

                if ($product->is_active === false) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product is inactive: ' . $product->name,
                    ], 422);
                }

                $variationEntry = null;
                if ($variationId) {
                    $variationEntry = ProductVariation::where('id', $variationId)
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$variationEntry) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid variation for ' . $product->name,
                        ], 422);
                    }

                    if ((int) $variationEntry->stock < $qty) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock for ' . $product->name
                                . ' (' . $variationEntry->variation_value . ').'
                                . ' Available: ' . $variationEntry->stock,
                        ], 422);
                    }
                } else {
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

                // ── Fix: Use variation price if set, else product sale_price ──
                $basePrice  = (float) ($product->sale_price ?? $product->price ?? 0);
                $varPrice   = $variationEntry ? (float) ($variationEntry->price ?? 0) : 0;
                $unitPrice  = ($varPrice > 0) ? $varPrice : $basePrice;

                $lineDiscountPct = (float) ($item['discount'] ?? 0);
                $gross           = $unitPrice * $qty;
                $lineDiscAmount  = round($gross * ($lineDiscountPct / 100), 2);
                $lineNet         = round($gross - $lineDiscAmount, 2);

                $lineSubtotals[]     = $lineNet;
                $lineDiscountParts[] = $lineDiscAmount;
                $resolvedLines[]     = [
                    'product'         => $product,
                    'variation_id'    => $variationId,
                    'variation_entry' => $variationEntry,
                    'quantity'        => $qty,
                    'unit_price'      => $unitPrice,
                    'line_net'        => $lineNet,
                    'line_disc'       => $lineDiscAmount,
                    'scan_code'       => $item['code'] ?? null,
                ];
            }

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
            $customer    = $data['customer_id']
                ? Customer::select('id', 'name', 'phone', 'address', 'area')->find($data['customer_id'])
                : null;
            $customerName    = $data['customer_name'] ?: $customer?->name;
            $customerPhone   = $data['customer_phone'] ?: $customer?->phone;
            $customerAddress = $data['customer_address'] ?: $customer?->address ?: '';
            $customerArea    = $customer?->area ?: '';

            $saleData = [
                'invoice_number'  => $tempInvoice,
                'customer_id'     => $data['customer_id'] ?: null,
                'customer_name'   => $customerName,
                'customer_phone'  => $customerPhone,
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
            ];
            if (Schema::hasColumn('sales', 'customer_address')) {
                $saleData['customer_address'] = $customerAddress;
            }
            $sale = Sale::create($saleData);

            $invoiceNumber = sprintf('INV-%s-%06d', now()->format('Ymd'), $sale->id);
            $sale->update(['invoice_number' => $invoiceNumber]);

            $vatToAllocate = $taxAmount;
            $allocatedVat  = 0.0;
            $lineCount     = count($resolvedLines);

            foreach ($resolvedLines as $idx => $line) {
                $product     = $line['product'];
                $lineNet     = $line['line_net'];
                $qty         = $line['quantity'];
                $unitPrice   = $line['unit_price'];
                $variationId = $line['variation_id'];

                if ($idx === $lineCount - 1) {
                    $lineVat = round($vatToAllocate - $allocatedVat, 2);
                } else {
                    $share        = $subtotal > 0 ? ($lineNet / $subtotal) : 0;
                    $lineVat      = round($vatToAllocate * $share, 2);
                    $allocatedVat += $lineVat;
                }

                SaleItem::create([
                    'sale_id'              => $sale->id,
                    'product_id'           => $product->id,
                    'product_variation_id' => $variationId,
                    'product_name'         => $product->name,
                    'product_code'         => $line['scan_code'] ?? $product->code ?? '',
                    'quantity'             => $qty,
                    'unit_price'           => $unitPrice,
                    'total_price'          => $lineNet,
                    'tax_rate'             => $vatRate,
                    'tax_amount'           => $lineVat,
                ]);

                $deduct = max(1, (int) $qty);

                if ($variationId) {
                    $rowsUpdated = ProductVariation::query()
                        ->where('id', $variationId)
                        ->where('product_id', $product->id)
                        ->where('stock', '>=', $deduct)
                        ->update(['stock' => DB::raw('stock - ' . $deduct)]);

                    if ($rowsUpdated === 1 && $line['variation_entry']) {
                        $this->syncProductStock(
                            $product->id,
                            $line['variation_entry']->variation_value,
                            -$deduct
                        );
                        $this->markProductUnitsSold($variationId, $deduct, $line['scan_code'] ?? null);
                        $newTotal = ProductVariation::where('product_id', $product->id)->sum('stock');
                        Product::where('id', $product->id)->update(['stock_quantity' => $newTotal]);
                    }
                } else {
                    $rowsUpdated = Product::query()
                        ->where('id', $product->id)
                        ->where('stock_quantity', '>=', $deduct)
                        ->update(['stock_quantity' => DB::raw('stock_quantity - ' . $deduct)]);

                    if ($rowsUpdated === 1) {
                        $this->markProductUnitsSoldForProduct($product->id, $deduct, $line['scan_code'] ?? null);
                    }
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

            // Post to Accounting Ledger
            try {
                app(\App\Services\Accounting\AccountingIntegrationService::class)->postPosSale($sale);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Accounting POS posting error: ' . $e->getMessage(), [
                    'sale_id' => $sale->id,
                ]);
            }

            DB::commit();

            $invoiceItems = [];
            foreach ($resolvedLines as $line) {
                $variationEntry = $line['variation_entry'];
                $invoiceItems[] = [
                    'name'       => $line['product']->name,
                    'barcode'    => $line['scan_code'] ?? $line['product']->barcode ?? $line['product']->code ?? '',
                    'code'       => $line['scan_code'] ?? $line['product']->code ?? '',
                    'variation'  => $variationEntry ? $variationEntry->variation_value : null,
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total'      => $line['line_net'],
                ];
            }

            $paymentTypes = \App\Models\PaymentType::active()->pluck('name')->toArray();

            return response()->json([
                'success'        => true,
                'message'        => 'Sale processed successfully',
                'invoice_number' => $invoiceNumber,
                'sale_id'        => $sale->id,
                'total_amount'   => $totalAmount,
                'print_invoice'  => true,
                'invoice_data'   => [
                    'shop_name'        => 'BongoBay',
                    'shop_address'     => '',
                    'shop_phone'       => '',
                    'invoice_no'       => $invoiceNumber,
                    'date'             => now()->format('d-m-Y h:i A'),
                    'customer_name'    => $customerName ?: 'Walk-in Customer',
                    'customer_phone'   => $customerPhone ?: '',
                    'customer_address' => $customerAddress,
                    'customer_area'    => $customerArea,
                    'items'            => $invoiceItems,
                    'subtotal'         => $subtotal,
                    'vat_amount'       => $taxAmount,
                    'vat_rate'         => $vatRate,
                    'discount'         => $orderDiscount,
                    'total'            => $totalAmount,
                    'paid'             => $paidAmount,
                    'due'              => max(0, round($totalAmount - $paidAmount, 2)),
                    'payment_method'   => $data['payment_method'] ?? '-',
                    'payment_types'    => $paymentTypes,
                ]
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

    public function openCashDrawer()
    {
        return response()->json(['success' => true]);
    }

    public function generateReceipt($saleId)
    {
        $sale = Sale::with('items')->findOrFail($saleId);
        return response()->json(['success' => true, 'sale' => $sale]);
    }

    public function getProductByBarcode($barcode)
    {
        $hasProductUnits = Schema::hasTable('product_units');
        $relations = ['productVariations' => function ($q) {
            $q->where('is_active', 1)->orWhereNull('is_active');
        }];

        if ($hasProductUnits) {
            $relations['productVariations.units'] = function ($q) {
                $q->where('status', 'available');
            };
        }

        $product = Product::where(function ($q) use ($barcode, $hasProductUnits) {
                $q->where('barcode', $barcode)
                  ->orWhere('code', $barcode)
                  ->orWhereHas('productVariations', function ($sq) use ($barcode) {
                      $sq->where('barcode', $barcode)
                         ->orWhere('product_code', $barcode);
                  });

                if ($hasProductUnits) {
                    $q->orWhereHas('productUnits', function ($sq) use ($barcode) {
                        $sq->where('barcode', $barcode)
                           ->orWhere('unit_code', $barcode)
                           ->orWhere('product_code', $barcode);
                    });
                }
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->with($relations)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found with barcode: ' . $barcode
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $this->formatProduct($product)
        ]);
    }

    /**
     * Server-side barcode scan ("ফোন দিয়ে স্ক্যান" / camera scan).
     *
     * Resolves the same product as the USB scanner flow via the shared resolver
     * (see resolveProductByScanCode), then redirects back to the POS sell page
     * with a flash message. Nothing here touches sales/stock/tax/discount/cart
     * calculation logic — client-side cart keeps ownership of all calculations.
     */
    public function scanByBarcode(Request $request)
    {
        // 1. Validate the scanned value (same pattern as the USB scanner input).
        $validated = $request->validate(
            ['barcode' => ['required', 'string', 'max:255']],
            [
                'barcode.required' => 'Barcode value is required.',
                'barcode.max'      => 'Barcode value is too long.',
            ]
        );
        $barcode = trim($validated['barcode']);

        // 2. Find the product in the database (shared resolver).
        $product = $this->resolveProductByScanCode($barcode);

        if (!$product) {
            return redirect()
                ->route('pos.index')
                ->withInput()
                ->withErrors(['barcode' => "Product not found for barcode: {$barcode}"]);
        }

        // 3. Redirect back to the sell page with the code + a success message.
        //    The existing client-side cart logic (public/js/pos.js) performs the
        //    add-to-cart and all stock/price/tax calculations as before.
        return redirect()
            ->route('pos.index')
            ->with('scanned_barcode', $barcode)
            ->with('scan_success', "Scanned: {$product->name}");
    }

    /**
     * Shared server-side scan resolver. Priority mirrors the USB scanner flow
     * and the client-side findProductByScanCode():
     *   1. exact product barcode  2. exact product code
     *   3. variation barcode/code  4. unit barcode/code (when the table exists)
     * Only the resolved product identity is returned — no calculation here.
     */
    private function resolveProductByScanCode(string $barcode): ?Product
    {
        $hasProductUnits = Schema::hasTable('product_units');

        $relations = ['productVariations' => function ($q) {
            $q->where('is_active', 1)->orWhereNull('is_active');
        }];
        if ($hasProductUnits) {
            $relations['productVariations.units'] = function ($q) {
                $q->where('status', 'available');
            };
        }

        return Product::where(function ($q) use ($barcode, $hasProductUnits) {
                $q->where('barcode', $barcode)
                  ->orWhere('code', $barcode)
                  ->orWhereHas('productVariations', function ($sq) use ($barcode) {
                      $sq->where('barcode', $barcode)
                         ->orWhere('product_code', $barcode);
                  });

                if ($hasProductUnits) {
                    $q->orWhereHas('productUnits', function ($sq) use ($barcode) {
                        $sq->where('barcode', $barcode)
                           ->orWhere('unit_code', $barcode)
                           ->orWhere('product_code', $barcode);
                    });
                }
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->with($relations)
            ->first();
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

    private function formatProduct(Product $product): array
    {
        $variations    = $product->productVariations ?? collect();
        $hasVariations = $variations->isNotEmpty();

        $stockDisplay = $hasVariations
            ? (int) $variations->sum('stock')
            : (int) ($product->stock_quantity ?? 0);

        return [
            'id'                  => $product->id,
            'name'                => $product->name,
            'code'                => $product->code ?? '-',
            'sku'                 => $product->sku,
            'barcode'             => $product->barcode,
            'unit'                => $product->unit?->name ?? 'pcs',
            'sale_price'          => (float) ($product->sale_price ?? 0),
            'price'               => (float) ($product->sale_price ?? 0),
            'stock_quantity'      => $stockDisplay,
            'discount_percentage' => (float) ($product->discount_percentage ?? 0),
            'image_url'           => $product->image
                ? asset('storage/' . $product->image)
                : asset('images/no-product.png'),
            'category'            => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
            'brand'               => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
            'variations'          => $variations->unique('variation_value')->values()->map(function ($v) use ($product) {
                $availableUnits = Schema::hasTable('product_units')
                    ? ($v->units ?? collect())->where('status', 'available')->values()
                    : collect();
                $firstUnit = $availableUnits->first();

                return [
                    'id'              => $v->id,
                    'variation_value' => $v->variation_value,
                    'extra_price'     => (float) ($v->extra_price ?? 0),
                    'price'           => (float) ($v->price ?? 0),
                    'purchase_price'  => (float) ($v->purchase_price ?? 0),
                    'stock'           => (int) ($v->stock ?? 0),
                    'sku'             => $firstUnit->unit_code ?? $firstUnit->product_code ?? $v->sku ?? $product->barcode,
                    'barcode'         => $product->barcode,
                    'unit_codes'      => $availableUnits->map(fn ($unit) => $unit->unit_code ?? $unit->product_code)->filter()->values(),
                    'unit_barcodes'   => $availableUnits->map(fn () => $product->barcode)->filter()->values(),
                ];
            })->values(),
        ];
    }

    private function syncProductStock(int $productId, string $variationValue, int $delta): void
    {
        $parts     = array_map('trim', explode(' - ', $variationValue));
        $colorName = $parts[0] ?? null;
        $sizeName  = $parts[1] ?? null;

        $colorId = $colorName
            ? \App\Models\Color::where('name', $colorName)->value('id')
            : null;

        $sizeId = $sizeName
            ? \App\Models\Size::where('name', $sizeName)->value('id')
            : null;

        if (!$colorId && !$sizeId) return;

        $query = \App\Models\ProductStock::where('product_id', $productId);
        if ($colorId) $query->where('color_id', $colorId);
        if ($sizeId)  $query->where('size_id',  $sizeId);

        if ($delta < 0) {
            $query->where('quantity', '>=', abs($delta))
                  ->update(['quantity' => DB::raw('quantity + ' . $delta)]);
        } else {
            $query->update(['quantity' => DB::raw('quantity + ' . $delta)]);
        }
    }

    private function markProductUnitsSold(int $variationId, int $quantity, ?string $scanCode = null): void
    {
        if ($quantity <= 0 || !Schema::hasTable('product_units')) {
            return;
        }

        $remaining = $quantity;
        $scanCode = $scanCode ? preg_replace('/[^A-Z0-9]/i', '', strtoupper($scanCode)) : null;

        if ($scanCode) {
            $matchedUnit = ProductUnit::where('product_variation_id', $variationId)
                ->where('status', 'available')
                ->where(function ($q) use ($scanCode) {
                                        $q->where('unit_code', $scanCode)
                                            ->orWhere('product_code', $scanCode)
                      ->orWhere('barcode', $scanCode);
                })
                ->lockForUpdate()
                ->first();

            if ($matchedUnit) {
                $matchedUnit->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                ]);
                $remaining--;
            }
        }

        if ($remaining <= 0) {
            return;
        }

        $unitIds = ProductUnit::where('product_variation_id', $variationId)
            ->where('status', 'available')
            ->orderBy('id')
            ->lockForUpdate()
            ->limit($remaining)
            ->pluck('id');

        if ($unitIds->isNotEmpty()) {
            ProductUnit::whereIn('id', $unitIds)->update([
                'status' => 'sold',
                'sold_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function markProductUnitsSoldForProduct(int $productId, int $quantity, ?string $scanCode = null): void
    {
        if ($quantity <= 0 || !Schema::hasTable('product_units')) {
            return;
        }

        $query = ProductUnit::where('product_id', $productId)
            ->where('status', 'available');
        $scanCode = $scanCode ? preg_replace('/[^A-Z0-9]/i', '', strtoupper($scanCode)) : null;

        if ($scanCode) {
            $query->orderByRaw('CASE WHEN unit_code = ? OR product_code = ? THEN 0 ELSE 1 END', [$scanCode, $scanCode]);
        }

        $unitIds = $query->orderBy('id')->lockForUpdate()->limit($quantity)->pluck('id');
        if ($unitIds->isNotEmpty()) {
            ProductUnit::whereIn('id', $unitIds)->update([
                'status' => 'sold',
                'sold_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
