<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\SyncsProductStock;
use App\Http\Controllers\Controller;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnsController extends Controller
{
    use SyncsProductStock;

    public function index()
    {
        Log::info('[Return] index loaded', ['user_id' => auth()->id()]);
        $returns = ReturnItem::with(['sale', 'customer', 'product'])->orderBy('created_at', 'desc')->paginate(20);
        $summary = [
            'total' => ReturnItem::count(),
            'pending' => ReturnItem::where('status', 'pending')->count(),
            'completed' => ReturnItem::whereIn('status', ['approved', 'completed'])->count(),
            'total_refunded' => ReturnItem::whereIn('status', ['approved', 'completed'])->sum('refund_amount'),
        ];
        // Full list for the same-page print report (screen table stays paginated)
        $printReturns = ReturnItem::with(['sale', 'customer', 'product'])->orderBy('created_at', 'desc')->get();
        $shopName = env('SHOP_NAME', config('app.name', 'BongoBay'));
        $shopAddress = env('SHOP_ADDRESS', '');
        $shopPhone = env('SHOP_PHONE', '');
        return view('admin.returns.index', compact('returns', 'summary', 'printReturns', 'shopName', 'shopAddress', 'shopPhone'));
    }

    public function create(Request $request)
    {
        $sale = null;

        if ($request->filled('sale_id')) {
            $sale = Sale::with(['items.product', 'customer'])->find($request->sale_id);

            if (!$sale) {
                return redirect()->route('returns.index')
                    ->with('error', 'Sale not found.');
            }
        }

        return view('admin.returns.create', compact('sale'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'sale_item_id' => 'nullable|exists:sale_items,id',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|string|in:cash,card,bank,credit',
            'status' => 'nullable|string|in:pending,approved,completed',
            'return_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        Log::info('[Return] store called', [
            'sale_id' => $validated['sale_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'refund_method' => $validated['refund_method'] ?? null,
            'user_id' => auth()->id(),
        ]);

        try {
            $return = DB::transaction(function () use ($validated) {
                $sale = Sale::whereKey($validated['sale_id'])->lockForUpdate()->firstOrFail();
                Log::info('[Return] store transaction started', ['sale_id' => $sale->id, 'invoice' => $sale->invoice_number, 'payment_status' => $sale->payment_status]);

                // Product must belong to this sale
                $saleItems = SaleItem::where('sale_id', $sale->id)
                    ->where('product_id', $validated['product_id'])
                    ->lockForUpdate()
                    ->get();

                if ($saleItems->isEmpty()) {
                    Log::warning('[Return] store abort — product not in sale', ['sale_id' => $sale->id, 'product_id' => $validated['product_id']]);
                    abort(422, 'Selected product does not belong to this sale.');
                }

                // Resolve target sale item (variation-aware when possible)
                $targetItem = null;
                if (!empty($validated['sale_item_id'])) {
                    $targetItem = $saleItems->firstWhere('id', (int) $validated['sale_item_id']);
                    if (!$targetItem) {
                        Log::warning('[Return] store abort — sale_item_id not in sale', ['sale_id' => $sale->id, 'sale_item_id' => $validated['sale_item_id']]);
                        abort(422, 'Selected sale item does not belong to this sale.');
                    }
                }

                $soldQty = (int) $saleItems->sum('quantity');

                // Already reserved/returned via ReturnItems (pending+approved+completed all reserve stock intent)
                $alreadyReserved = (int) ReturnItem::where('sale_id', $sale->id)
                    ->where('product_id', $validated['product_id'])
                    ->whereIn('status', ReturnItem::ACTIVE_STATUSES)
                    ->sum('quantity');

                // Legacy stock already restored via Sale list flow without ReturnItem row
                $alreadyRestoredViaItems = (int) $saleItems->sum('returned_quantity');
                // Avoid double counting: restored qty that is already represented by completed ReturnItems
                $completedReturnQty = (int) ReturnItem::where('sale_id', $sale->id)
                    ->where('product_id', $validated['product_id'])
                    ->whereIn('status', ['approved', 'completed'])
                    ->sum('quantity');
                $legacyRestored = max(0, $alreadyRestoredViaItems - $completedReturnQty);

                $available = $soldQty - $alreadyReserved - $legacyRestored;

                Log::info('[Return] store availability', [
                    'sale_id' => $sale->id,
                    'soldQty' => $soldQty,
                    'alreadyReserved' => $alreadyReserved,
                    'legacyRestored' => $legacyRestored,
                    'available' => $available,
                    'requested' => (int) $validated['quantity'],
                ]);

                if ($available <= 0) {
                    Log::warning('[Return] store abort — already fully returned', ['sale_id' => $sale->id, 'soldQty' => $soldQty]);
                    abort(422, "This product is already fully returned (sold {$soldQty}, already returned " . ($soldQty - $available) . ").");
                }

                if ((int) $validated['quantity'] > $available) {
                    Log::warning('[Return] store abort — qty exceeds available', [
                        'sale_id' => $sale->id,
                        'requested' => (int) $validated['quantity'],
                        'available' => $available,
                    ]);
                    abort(422, "Only {$available} item(s) available for return (sold {$soldQty}, already returned " . ($soldQty - $available) . ").");
                }

                $status = $validated['status'] ?? 'pending';

                $payload = [
                    'sale_id' => $sale->id,
                    'sale_item_id' => $targetItem?->id,
                    'customer_id' => $validated['customer_id'] ?? $sale->customer_id,
                    'product_id' => $validated['product_id'],
                    'quantity' => (int) $validated['quantity'],
                    'reason' => $validated['reason'],
                    'refund_amount' => $validated['refund_amount'],
                    'refund_method' => $validated['refund_method'],
                    'notes' => $validated['notes'] ?? null,
                    'return_date' => $validated['return_date'] ?? now()->toDateString(),
                    'status' => $status,
                ];

                $return = ReturnItem::create($payload);
                Log::info('[Return] store row created', ['return_id' => $return->id, 'status' => $status, 'qty' => $return->quantity]);

                // Immediate stock effect for approved/completed returns
                if (in_array($status, ['approved', 'completed'], true)) {
                    Log::info('[Return] store applyRestore', ['return_id' => $return->id, 'qty' => (int) $validated['quantity']]);
                    $this->applyRestore($sale, $saleItems, (int) $validated['quantity']);

                    try {
                        app(\App\Services\Accounting\AccountingIntegrationService::class)->postSalesReturn($return, $sale);
                    } catch (\Throwable $e) {
                        Log::error('Accounting return post error: ' . $e->getMessage());
                    }
                }

                return $return;
            });

            Log::info('[Return] store success', ['return_id' => $return->id, 'status' => $return->status]);
            return redirect()->route('returns.index')
                ->with('success', "Return #{$return->id} created successfully" . (in_array($return->status, ['approved', 'completed'], true) ? ' and stock restored.' : ' (pending). Approve it to restore stock.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('[Return] store validation error', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            Log::warning('[Return] store http exception', ['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);
            Log::error('[Return] store exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Error creating return: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $return = ReturnItem::with(['sale', 'customer', 'product'])->findOrFail($id);
        $view = view()->exists('admin.returns.show')
            ? 'admin.returns.show'
            : 'admin.returns.index';
        if ($view === 'admin.returns.index') {
            return redirect()->route('returns.index');
        }
        return view($view, compact('return'));
    }

    public function edit($id)
    {
        $return = ReturnItem::findOrFail($id);
        if (!view()->exists('admin.returns.edit')) {
            return redirect()->route('returns.index')->with('error', 'Edit view not available.');
        }
        return view('admin.returns.edit', compact('return'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|string|in:cash,card,bank,credit',
            'status' => 'required|string|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        Log::info('[Return] update called', [
            'return_id' => $id,
            'new_status' => $validated['status'],
            'new_quantity' => $validated['quantity'],
            'user_id' => auth()->id(),
        ]);

        try {
            DB::transaction(function () use ($id, $validated) {
                $return = ReturnItem::whereKey($id)->lockForUpdate()->firstOrFail();
                $sale = Sale::whereKey($return->sale_id)->lockForUpdate()->firstOrFail();
                $saleItems = SaleItem::where('sale_id', $sale->id)
                    ->where('product_id', $return->product_id)
                    ->lockForUpdate()
                    ->get();

                $oldStatus = $return->status;
                $newStatus = $validated['status'];
                $oldActive = in_array($oldStatus, ['approved', 'completed'], true);
                $newActive = in_array($newStatus, ['approved', 'completed'], true);
                $oldReserved = in_array($oldStatus, ReturnItem::ACTIVE_STATUSES, true);
                $newReserved = in_array($newStatus, ReturnItem::ACTIVE_STATUSES, true);

                Log::info('[Return] update transition', [
                    'return_id' => $return->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'oldActive' => $oldActive,
                    'newActive' => $newActive,
                    'oldReserved' => $oldReserved,
                    'newReserved' => $newReserved,
                ]);

                // Quantity change validation (only matters while reserved)
                if ((int) $validated['quantity'] !== (int) $return->quantity && $newReserved) {
                    $soldQty = (int) $saleItems->sum('quantity');
                    $otherReserved = (int) ReturnItem::where('sale_id', $sale->id)
                        ->where('product_id', $return->product_id)
                        ->where('id', '!=', $return->id)
                        ->whereIn('status', ReturnItem::ACTIVE_STATUSES)
                        ->sum('quantity');
                    $legacyRestored = max(0, (int) $saleItems->sum('returned_quantity')
                        - (int) ReturnItem::where('sale_id', $sale->id)
                            ->where('product_id', $return->product_id)
                            ->where('id', '!=', $return->id)
                            ->whereIn('status', ['approved', 'completed'])
                            ->sum('quantity')
                        - ($oldActive ? (int) $return->quantity : 0));
                    $available = $soldQty - $otherReserved - $legacyRestored - ($oldReserved && !$oldActive ? 0 : 0);
                    // When this return itself was reserved, its old qty is free to reuse:
                    if ($oldReserved) {
                        $available += (int) $return->quantity;
                    }
                    if ((int) $validated['quantity'] > $available) {
                        Log::warning('[Return] update abort — qty exceeds available', [
                            'return_id' => $return->id,
                            'requested' => (int) $validated['quantity'],
                            'available' => $available,
                        ]);
                        abort(422, "Only {$available} item(s) available for return.");
                    }
                }

                $wasActive = $oldActive;
                $willBeActive = $newActive;

                // Stock transitions
                if (!$wasActive && $willBeActive) {
                    // pending/rejected -> approved/completed : restore
                    $qty = min((int) $validated['quantity'], $this->totalReturnable($saleItems));
                    if ($qty <= 0) {
                        Log::warning('[Return] update abort — no returnable qty', ['return_id' => $return->id]);
                        abort(422, 'Selected items were already returned to stock.');
                    }
                    Log::info('[Return] update applyRestore', ['return_id' => $return->id, 'qty' => $qty]);
                    $this->applyRestore($sale, $saleItems, $qty);
                    $validated['quantity'] = $qty;

                    try {
                        app(\App\Services\Accounting\AccountingIntegrationService::class)->postSalesReturn($return, $sale);
                    } catch (\Throwable $e) {
                        Log::error('Accounting return post error: ' . $e->getMessage());
                    }
                } elseif ($wasActive && !$willBeActive) {
                    // approved/completed -> pending/rejected : reverse
                    $qty = min((int) $return->quantity, (int) $saleItems->sum('returned_quantity'));
                    Log::info('[Return] update applyReverse', ['return_id' => $return->id, 'qty' => $qty > 0 ? $qty : (int) $return->quantity]);
                    $this->applyReverse($sale, $saleItems, $qty > 0 ? $qty : (int) $return->quantity);
                } elseif ($wasActive && $willBeActive && (int) $validated['quantity'] !== (int) $return->quantity) {
                    // active -> active with qty change : adjust delta
                    $delta = (int) $validated['quantity'] - (int) $return->quantity;
                    if ($delta > 0) {
                        $qty = min($delta, $this->totalReturnable($saleItems));
                        if ($qty <= 0) {
                            Log::warning('[Return] update abort — no additional returnable qty', ['return_id' => $return->id]);
                            abort(422, 'No additional returnable quantity available.');
                        }
                        Log::info('[Return] update applyRestore delta', ['return_id' => $return->id, 'delta' => $delta, 'qty' => $qty]);
                        $this->applyRestore($sale, $saleItems, $qty);
                        $validated['quantity'] = (int) $return->quantity + $qty;
                    } elseif ($delta < 0) {
                        Log::info('[Return] update applyReverse delta', ['return_id' => $return->id, 'delta' => abs($delta)]);
                        $this->applyReverse($sale, $saleItems, abs($delta));
                    }
                }

                $return->update($validated);
                $this->refreshSaleReturnStatus($sale->id);
                Log::info('[Return] update saved', ['return_id' => $return->id, 'status' => $validated['status'], 'quantity' => $validated['quantity']]);
            });

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Return updated successfully.']);
            }

            return redirect()->route('returns.index')
                ->with('success', 'Return updated successfully');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            Log::warning('[Return] update http exception', ['return_id' => $id, 'message' => $e->getMessage()]);
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);
            Log::error('[Return] update exception', ['return_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error updating return: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error updating return: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        Log::info('[Return] destroy called', ['return_id' => $id, 'user_id' => auth()->id()]);
        try {
            DB::transaction(function () use ($id) {
                $return = ReturnItem::whereKey($id)->lockForUpdate()->firstOrFail();
                Log::info('[Return] destroy transaction', ['return_id' => $return->id, 'status' => $return->status, 'qty' => $return->quantity]);

                // If stock was restored for this return, reverse it
                if (in_array($return->status, ['approved', 'completed'], true)) {
                    $saleItems = SaleItem::where('sale_id', $return->sale_id)
                        ->where('product_id', $return->product_id)
                        ->lockForUpdate()
                        ->get();
                    $sale = Sale::whereKey($return->sale_id)->lockForUpdate()->first();
                    $qty = min((int) $return->quantity, (int) $saleItems->sum('returned_quantity'));
                    if ($qty > 0 && $sale) {
                        Log::info('[Return] destroy applyReverse', ['return_id' => $return->id, 'qty' => $qty]);
                        $this->applyReverse($sale, $saleItems, $qty);
                    }
                }

                $saleId = $return->sale_id;
                $return->delete();
                Log::info('[Return] destroy deleted', ['return_id' => $id, 'sale_id' => $saleId]);

                if ($saleId) {
                    $this->refreshSaleReturnStatus($saleId);
                }
            });

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Return deleted successfully.']);
            }

            return redirect()->route('returns.index')
                ->with('success', 'Return deleted successfully');
        } catch (\Exception $e) {
            report($e);
            Log::error('[Return] destroy exception', ['return_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting return: ' . $e->getMessage()], 500);
            }
            return redirect()->route('returns.index')->with('error', 'Error deleting return: ' . $e->getMessage());
        }
    }

    public function export()
    {
        Log::info('[Return] export called', ['user_id' => auth()->id()]);
        $returns = ReturnItem::with(['sale', 'customer', 'product'])->orderBy('created_at', 'desc')->get();
        Log::info('[Return] export rows', ['count' => $returns->count()]);

        $filename = 'sales_returns_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($returns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Date', 'Sale Invoice', 'Customer', 'Product', 'Qty', 'Refund Amount', 'Method', 'Status', 'Reason']);
            foreach ($returns as $r) {
                fputcsv($out, [
                    $r->id,
                    optional($r->return_date)->format('Y-m-d') ?? $r->created_at->format('Y-m-d'),
                    $r->sale?->invoice_number ?? $r->sale_id,
                    $r->customer?->name ?? 'Walk-in Customer',
                    $r->product?->name ?? 'Unknown',
                    $r->quantity,
                    $r->refund_amount,
                    $r->refund_method,
                    $r->status,
                    $r->reason,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function receipt($id)
    {
        $return = ReturnItem::with(['sale', 'customer', 'product'])->findOrFail($id);
        if (!view()->exists('admin.returns.receipt')) {
            return redirect()->route('returns.show', $id);
        }
        Log::info('[Return] receipt print', ['return_id' => $return->id, 'user_id' => auth()->id()]);
        $shopName = env('SHOP_NAME', config('app.name', 'BongoBay'));
        $shopAddress = env('SHOP_ADDRESS', '');
        $shopPhone = env('SHOP_PHONE', '');
        return view('admin.returns.receipt', compact('return', 'shopName', 'shopAddress', 'shopPhone'));
    }

    // ── Helpers ──────────────────────────────────────

    private function totalReturnable($saleItems): int
    {
        $total = (int) $saleItems->sum(fn($item) => $this->returnableQuantity($item));
        Log::info('[Return] totalReturnable', ['total' => $total, 'sale_item_ids' => $saleItems->pluck('id')->all()]);
        return $total;
    }

    /**
     * Restore stock and bump SaleItem.returned_quantity FIFO across matching items.
     */
    private function applyRestore(Sale $sale, $saleItems, int $qty): void
    {
        Log::info('[Return] applyRestore start', ['sale_id' => $sale->id, 'qty' => $qty]);
        $remaining = $qty;
        foreach ($saleItems as $item) {
            if ($remaining <= 0) break;
            $can = $this->returnableQuantity($item);
            if ($can <= 0) continue;
            $take = min($can, $remaining);
            Log::info('[Return] applyRestore item', ['sale_item_id' => $item->id, 'take' => $take, 'remaining' => $remaining]);
            $this->restoreStock($item, $take);
            $item->increment('returned_quantity', $take);
            $remaining -= $take;
        }

        $this->refreshSaleReturnStatus($sale->id);
        Log::info('[Return] applyRestore done', ['sale_id' => $sale->id, 'qty' => $qty]);
    }

    /**
     * Reverse a previous restore (reject/delete/downgrade).
     */
    private function applyReverse(Sale $sale, $saleItems, int $qty): void
    {
        Log::info('[Return] applyReverse start', ['sale_id' => $sale->id, 'qty' => $qty]);
        $remaining = $qty;
        // Reverse LIFO so FIFO restores unwind correctly
        foreach ($saleItems->sortByDesc('id') as $item) {
            if ($remaining <= 0) break;
            $restored = (int) ($item->returned_quantity ?? 0);
            if ($restored <= 0) continue;
            $take = min($restored, $remaining);
            Log::info('[Return] applyReverse item', ['sale_item_id' => $item->id, 'take' => $take, 'remaining' => $remaining]);
            $this->deductStock($item, $take);
            $item->decrement('returned_quantity', $take);
            $remaining -= $take;
        }

        $this->refreshSaleReturnStatus($sale->id);
        Log::info('[Return] applyReverse done', ['sale_id' => $sale->id, 'qty' => $qty]);
    }

    private function refreshSaleReturnStatus(int $saleId): void
    {
        $items = SaleItem::where('sale_id', $saleId)->get();
        if ($items->isEmpty()) {
            Log::info('[Return] refreshSaleReturnStatus no items', ['sale_id' => $saleId]);
            return;
        }

        $allReturned = $items->every(fn($item) => $this->returnableQuantity($item) <= 0);
        $anyReturned = (int) $items->sum('returned_quantity') > 0;
        $returnableQtys = $items->map(fn($item) => $this->returnableQuantity($item))->all();

        $sale = Sale::find($saleId);
        if (!$sale) {
            Log::warning('[Return] refreshSaleReturnStatus sale not found', ['sale_id' => $saleId]);
            return;
        }

        $oldPaymentStatus = $sale->payment_status;
        if ($allReturned) {
            $sale->update(['payment_status' => 'returned']);
            Log::info('[Return] refreshSaleReturnStatus -> returned', ['sale_id' => $saleId, 'old' => $oldPaymentStatus]);
        } elseif ($anyReturned && $sale->payment_status === 'returned') {
            // Was fully returned but now partially reversed → reopen as paid
            $newStatus = $sale->due_amount > 0 ? 'partial' : 'paid';
            $sale->update(['payment_status' => $newStatus]);
            Log::info('[Return] refreshSaleReturnStatus reopened', ['sale_id' => $saleId, 'old' => $oldPaymentStatus, 'new' => $newStatus]);
        } else {
            Log::info('[Return] refreshSaleReturnStatus no change', ['sale_id' => $saleId, 'payment_status' => $sale->payment_status, 'allReturned' => $allReturned, 'anyReturned' => $anyReturned, 'returnableQtys' => $returnableQtys]);
        }
    }
}
