<?php
namespace App\Http\Controllers\Admin;

use App\Concerns\SyncsProductStock;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    use SyncsProductStock;
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['items.product', 'user', 'customer'])
            ->orderBy('created_at', 'desc');

        // Fully returned sales live in Return List only (?show_returned=1 to include them)
        if (!$request->boolean('show_returned')) {
            $query->where('payment_status', '!=', 'returned');
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->get('date'));
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        $sales = $query->paginate(50);
        // Full filtered list for the same-page print report (screen table stays paginated)
        $printSales = (clone $query)->get();
        $printSummary = [
            'total' => $printSales->count(),
            'total_amount' => $printSales->sum('total_amount'),
            'paid_amount' => $printSales->sum('paid_amount'),
            'due_amount' => $printSales->sum('due_amount'),
        ];
        $shopName = env('SHOP_NAME', config('app.name', 'BongoBay'));
        $shopAddress = env('SHOP_ADDRESS', '');
        $shopPhone = env('SHOP_PHONE', '');
        return view('admin.sales.index', compact('sales', 'printSales', 'printSummary', 'shopName', 'shopAddress', 'shopPhone'));
    }

    /**
     * Display the specified sale.
     */
    public function show($id)
    {
        $sale = Sale::with(['items.product', 'user'])->findOrFail($id);
        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Return invoice data as JSON (for the sales list invoice modal).
     */
    public function invoiceData(Sale $sale)
    {
        $sale->load(['items.product', 'items.variation', 'customer']);

        $items = $sale->items->map(function ($item) {
            $variationStr = null;
            if ($item->variation) {
                $variationStr = $item->variation->variation_value;
            } elseif ($item->product) {
                $variations = $item->product->variations;
                if ($variations && $variations->count() > 0) {
                    $variationStr = $variations->first()->variation_value;
                }
            }

            return [
                'name'       => $item->product_name ?? $item->product?->name ?? 'Product',
                'code'       => $item->product_code ?? $item->product?->code ?? '',
                'variation'  => $variationStr,
                'color'      => '',
                'size'       => '',
                'quantity'   => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total'      => (float) $item->unit_price * (int) $item->quantity,
            ];
        });

        $customerName    = $sale->customer_name ?: ($sale->customer?->name ?: 'Walk-in Customer');
        $customerPhone   = $sale->customer_phone ?: ($sale->customer?->phone ?: '');
        $customerAddress = $sale->customer?->address ?? '';
        $customerArea    = $sale->customer?->area ?? '';

        return response()->json([
            'success' => true,
            'data'    => [
                'shop_name'        => config('app.shop_name', env('SHOP_NAME', 'Possales')),
                'shop_address'     => env('SHOP_ADDRESS', ''),
                'shop_phone'       => env('SHOP_PHONE', ''),
                'shop_email'       => env('SHOP_EMAIL', ''),
                'invoice_no'       => $sale->invoice_number ?? 'SL-' . $sale->id,
                'date'             => $sale->created_at ? $sale->created_at->format('d-m-Y h:i A') : now()->format('d-m-Y h:i A'),
                'customer_name'    => $customerName,
                'customer_phone'   => $customerPhone,
                'customer_address' => $customerAddress,
                'customer_area'    => $customerArea,
                'items'            => $items,
                'subtotal'         => (float) ($sale->subtotal ?? $sale->items->sum(fn($i) => $i->unit_price * $i->quantity)),
                'vat_amount'       => (float) ($sale->tax_amount ?? 0),
                'vat_rate'         => 0,
                'discount'         => (float) ($sale->discount_amount ?? 0),
                'total'            => (float) ($sale->total_amount ?? 0),
                'paid'             => (float) ($sale->paid_amount ?? 0),
                'due'              => (float) ($sale->due_amount ?? max(0, ($sale->total_amount ?? 0) - ($sale->paid_amount ?? 0))),
                'payment_method'   => $sale->payment_method ?? 'Cash',
            ],
        ]);
    }

    /**
     * Generate receipt for the specified sale.
     */
    public function receipt($id)
    {
        $sale = Sale::with(['items.product', 'user'])->findOrFail($id);
        return view('admin.sales.receipt', compact('sale'));
    }

    /**
     * POS sale invoice as PDF (sale list logic unchanged; separate download).
     */
    public function invoicePdf(Sale $sale)
    {
        $sale->load(['items.product', 'items.variation', 'customer', 'user']);

        $pdf = Pdf::loadView('admin.sales.invoice-pdf', compact('sale'));
        $pdf->setPaper('a4', 'portrait');

        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $sale->invoice_number) ?: $sale->id;

        return $pdf->stream('invoice-' . $safe . '.pdf');
    }

    /**
     * Search sales for autocomplete.
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');

        $sales = Sale::where('invoice_number', 'like', "%{$search}%")
            ->orWhere('customer_name', 'like', "%{$search}%")
            ->orWhere('customer_phone', 'like', "%{$search}%")
            ->orWhereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->with('customer')
            ->limit(20)
            ->get(['id', 'invoice_number', 'customer_id', 'customer_name', 'total_amount', 'created_at'])
            ->map(function ($sale) {
                $sale->customer_name = $sale->customer_name ?: $sale->customer?->name;
                return $sale;
            });

        return response()->json($sales);
    }

    /**
     * Search sales for return dropdown.
     */
    public function searchSales(Request $request)
    {
        $search = $request->get('search', '');

        $sales = Sale::where('invoice_number', 'like', "%{$search}%")
            ->orWhere('customer_name', 'like', "%{$search}%")
            ->orWhere('customer_phone', 'like', "%{$search}%")
            ->orWhereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id', 'invoice_number', 'customer_id', 'customer_name', 'total_amount', 'created_at']);

        $sales->transform(function ($sale) {
            $customerName = $sale->customer_name ?: $sale->customer?->name;
            $sale->display_text = ($sale->invoice_number ?? 'Sale #' . $sale->id)
                . ' — ' . ($customerName ?? 'Unknown')
                . ' — ৳' . number_format($sale->total_amount, 2);
            return $sale;
        });

        return response()->json($sales);
    }

    /**
     * Update sale details (Edit modal AJAX).
     */
    public function update(Request $request, $id)
    {
        try {
            $sale = Sale::findOrFail($id);

            $sale->customer_name  = $request->customer_name  ?? $sale->customer_name;
            $sale->customer_phone = $request->customer_phone ?? $sale->customer_phone;
            $sale->payment_method = $request->payment_method ?? $sale->payment_method;
            $sale->payment_status = $request->payment_status ?? $sale->payment_status;

            // Paid amount update করলে due recalculate
            if ($request->has('paid_amount')) {
                $sale->paid_amount = floatval($request->paid_amount);
                $sale->due_amount  = max(0, $sale->total_amount - $sale->paid_amount);
            }

            $sale->save();

            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully.',
                'due'     => $sale->due_amount,
                'paid'    => $sale->paid_amount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating sale: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process sale return — Full or Partial (Return modal AJAX).
     * Stock automatically restored + ReturnItem audit rows created.
     */
    public function processReturn(Request $request, $id)
    {
        Log::info('[SaleReturn] processReturn called', [
            'sale_id' => $id,
            'mode' => $request->input('mode', 'full'),
            'user_id' => auth()->id(),
            'input' => $request->all(),
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $sale = Sale::whereKey($id)->lockForUpdate()->firstOrFail();
                $items = SaleItem::where('sale_id', $sale->id)->lockForUpdate()->get();
                $mode = $request->input('mode', 'full'); // 'full' or 'partial'
                $reason = $request->input('reason', 'Return from sale list');
                $refundMethod = $request->input('refund_method', $sale->payment_method ?? 'cash');
                if (!in_array($refundMethod, ['cash', 'card', 'bank', 'credit'], true)) {
                    $refundMethod = 'cash';
                }
                $restoredQty = 0;
                $createdReturns = 0;

                Log::info('[SaleReturn] processReturn transaction started', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_number,
                    'payment_status' => $sale->payment_status,
                    'mode' => $mode,
                    'items_count' => $items->count(),
                ]);

                if ($sale->payment_status === 'returned' && (int) $items->sum('returned_quantity') === 0) {
                    Log::warning('[SaleReturn] already fully returned', ['sale_id' => $sale->id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'This sale is already fully returned.',
                    ], 422);
                }

                $makeReturnRow = function ($saleItem, int $qty) use ($sale, $reason, $refundMethod, &$createdReturns) {
                    Log::info('[SaleReturn] creating ReturnItem', [
                        'sale_id' => $sale->id,
                        'sale_item_id' => $saleItem->id,
                        'product_id' => $saleItem->product_id,
                        'qty' => $qty,
                    ]);
                    // Skip if an identical completed row already covers this (idempotency guard)
                    $createdReturn = \App\Models\ReturnItem::create([
                        'sale_id' => $sale->id,
                        'sale_item_id' => $saleItem->id,
                        'customer_id' => $sale->customer_id,
                        'product_id' => $saleItem->product_id,
                        'quantity' => $qty,
                        'reason' => $reason,
                        'refund_amount' => (float) $saleItem->unit_price * $qty,
                        'refund_method' => $refundMethod,
                        'status' => 'completed',
                        'return_date' => now()->toDateString(),
                        'notes' => 'Created from Sale List quick return',
                    ]);

                    try {
                        app(\App\Services\Accounting\AccountingIntegrationService::class)->postSalesReturn($createdReturn, $sale);
                    } catch (\Throwable $e) {
                        Log::error('Accounting return post error: ' . $e->getMessage());
                    }

                    $createdReturns++;
                };

                if ($mode === 'full') {
                // সব items return — পুরো stock restore
                    foreach ($items as $item) {
                        $qty = $this->returnableQuantity($item);
                        Log::info('[SaleReturn] full mode item', ['sale_item_id' => $item->id, 'returnable' => $qty, 'sold_qty' => $item->quantity, 'returned_qty' => $item->returned_quantity]);
                        if ($qty <= 0) continue;

                        $this->restoreStock($item, $qty);
                        $item->increment('returned_quantity', $qty);
                        $makeReturnRow($item, $qty);
                        $restoredQty += $qty;
                    }

                    if ($restoredQty <= 0) {
                        Log::warning('[SaleReturn] full mode nothing restored', ['sale_id' => $sale->id]);
                        return response()->json([
                            'success' => false,
                            'message' => 'This sale is already fully returned.',
                        ], 422);
                    }

                    $sale->update(['payment_status' => 'returned']);
                    Log::info('[SaleReturn] full mode sale marked returned', ['sale_id' => $sale->id]);

                } else {
                // Partial — শুধু selected items এর qty restore
                    $returnItems = $request->input('items', []);

                    if (empty($returnItems)) {
                        Log::warning('[SaleReturn] partial mode no items selected', ['sale_id' => $sale->id]);
                        return response()->json([
                            'success' => false,
                            'message' => 'No items selected for return.',
                        ], 422);
                    }

                    foreach ($returnItems as $ri) {
                        $saleItem = $items->firstWhere('id', (int) ($ri['sale_item_id'] ?? 0));
                        if (!$saleItem) {
                            Log::warning('[SaleReturn] partial mode sale item not found', ['sale_item_id' => $ri['sale_item_id'] ?? 0]);
                            continue;
                        }

                        $qty = intval($ri['quantity'] ?? 0);
                        if ($qty <= 0) continue;

                    // Max qty check — sold quantity এর বেশি return হবে না
                        $qty = min($qty, $this->returnableQuantity($saleItem));
                        Log::info('[SaleReturn] partial mode item', ['sale_item_id' => $saleItem->id, 'requested' => $ri['quantity'] ?? 0, 'returnable' => $this->returnableQuantity($saleItem), 'will_restore' => $qty]);
                        if ($qty <= 0) continue;

                        $this->restoreStock($saleItem, $qty);
                        $saleItem->increment('returned_quantity', $qty);
                        $makeReturnRow($saleItem, $qty);
                        $restoredQty += $qty;
                    }

                    if ($restoredQty <= 0) {
                        Log::warning('[SaleReturn] partial mode nothing restored', ['sale_id' => $sale->id]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected items were already returned.',
                        ], 422);
                    }

                    $freshItems = SaleItem::where('sale_id', $sale->id)->get();
                    if ($freshItems->every(fn($item) => $this->returnableQuantity($item) <= 0)) {
                        $sale->update(['payment_status' => 'returned']);
                        Log::info('[SaleReturn] partial mode all items returned, sale marked returned', ['sale_id' => $sale->id]);
                    }
                }

                Log::info('[SaleReturn] processReturn success', ['sale_id' => $sale->id, 'restored_qty' => $restoredQty, 'return_records' => $createdReturns]);
                return response()->json([
                    'success' => true,
                    'message' => 'Return processed successfully. Stock restored.',
                    'restored_qty' => $restoredQty,
                    'return_records' => $createdReturns,
                ]);
            });

        } catch (\Exception $e) {
            report($e);
            Log::error('[SaleReturn] processReturn exception', ['sale_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing return: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $sale = Sale::whereKey($id)->lockForUpdate()->firstOrFail();
                $items = SaleItem::where('sale_id', $sale->id)->lockForUpdate()->get();
                $skipStockRestore = $sale->payment_status === 'returned'
                    && (int) $items->sum('returned_quantity') === 0;

                foreach ($items as $item) {
                    if ($skipStockRestore) break;

                    $qty = $this->returnableQuantity($item);
                    if ($qty > 0) {
                        $this->restoreStock($item, $qty);
                    }
                }

                $sale->delete();
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale deleted successfully and stock quantities restored.',
                ]);
            }

            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting sale: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('sales.index')->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected sales.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('sales.index')->with('error', 'No sales selected.');
        }

        $deleted = 0;
        $errors = 0;

        foreach ($ids as $id) {
            try {
                DB::transaction(function () use ($id) {
                    $sale = Sale::whereKey($id)->lockForUpdate()->firstOrFail();
                    $items = SaleItem::where('sale_id', $sale->id)->lockForUpdate()->get();
                    $skipStockRestore = $sale->payment_status === 'returned'
                        && (int) $items->sum('returned_quantity') === 0;

                    foreach ($items as $item) {
                        if ($skipStockRestore) break;
                        $qty = $this->returnableQuantity($item);
                        if ($qty > 0) {
                            $this->restoreStock($item, $qty);
                        }
                    }

                    $sale->delete();
                });
                $deleted++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $message = "$deleted sale(s) deleted successfully.";
        if ($errors > 0) {
            $message .= " $errors sale(s) failed.";
        }

        return redirect()->route('sales.index')->with(
            $errors > 0 ? 'error' : 'success',
            $message
        );
    }

    /**
     * Get sales statistics for dashboard.
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', today()->format('Y-m-d'));

            $stats = Sale::whereDate('created_at', $date)
                ->selectRaw('COUNT(*) as total_sales')
                ->selectRaw('COALESCE(SUM(total_amount), 0) as total_revenue')
                ->selectRaw('COALESCE(AVG(total_amount), 0) as avg_sale_value')
                ->first();

            $paymentMethods = Sale::whereDate('created_at', $date)
                ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('payment_method')
                ->get();

            return response()->json([
                'total_sales'     => (int) $stats->total_sales,
                'total_revenue'   => (float) $stats->total_revenue,
                'avg_sale_value'  => (float) $stats->avg_sale_value,
                'payment_methods' => $paymentMethods,
            ]);
        } catch (\Exception $e) {
            Log::error('Sale stats error', ['message' => $e->getMessage()]);
            return response()->json([
                'success'  => false,
                'message'  => 'Unable to fetch sale stats.',
            ], 500);
        }
    }

}
