<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariation;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    // INDEX — Paginated list of all purchases
    // ═══════════════════════════════════════════════════════════════════════════
    public function index()
    {
        $purchases = Purchase::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.purchases.index', compact('purchases'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CREATE — Show blank purchase form
    // ═══════════════════════════════════════════════════════════════════════════
    public function create()
    {
        $colors = Color::where('is_active', true)->get();
        $sizes  = Size::where('is_active', true)->get();

        return view('admin.purchases.create', compact('colors', 'sizes'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // STORE — Validate → persist purchase + items → increment stock
    // ═══════════════════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        // ─── 1. JSON Decoding ─────────────────────────────────────────────────
        // Frontend sends items as a JSON string in the hidden input `purchase_items`.
        // Keys: product_id | variation_id | qty | unit_price | total
        $decodedItems = json_decode($request->purchase_items, true) ?? [];
        $request->merge(['items' => $decodedItems]);

        // ─── 2. Validation ────────────────────────────────────────────────────
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'purchase_date'        => 'required|date',
            'invoice_number'       => 'nullable|string|max:255',
            'payment_method'       => 'required|string|in:cash,card,bank,credit',
            'subtotal'             => 'nullable|numeric|min:0',
            'tax_amount'           => 'nullable|numeric|min:0',
            'discount_amount'      => 'nullable|numeric|min:0',
            'total_amount'         => 'required|numeric|min:0',
            'paid_amount'          => 'required|numeric|min:0',
            'notes'                => 'nullable|string|max:1000',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.qty'          => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.total'        => 'required|numeric|min:0',
        ]);

        // ─── 3. Transaction ───────────────────────────────────────────────────
        DB::beginTransaction();

        try {
            // ─── Financial Calculations ───────────────────────────────────────
            $totalAmount  = (float) $request->total_amount;
            $paidAmount   = (float) $request->paid_amount;
            $dueAmount    = max(0, $totalAmount - $paidAmount);
            $changeAmount = max(0, $paidAmount  - $totalAmount);

            // ─── Payment Status ───────────────────────────────────────────────
            if ($dueAmount <= 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // ─── Auto-generate Invoice Number ─────────────────────────────────
            $invoiceNumber = $request->invoice_number
                ?? 'INV-' . strtoupper(uniqid());

            // ─── 4. Create Purchase Record ────────────────────────────────────
            $purchase = Purchase::create([
                'supplier_id'     => $request->supplier_id,
                'purchase_date'   => $request->purchase_date,
                'invoice_number'  => $invoiceNumber,
                'payment_method'  => $request->payment_method,
                'subtotal'        => $request->subtotal        ?? 0,
                'tax_amount'      => $request->tax_amount      ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'due_amount'      => $dueAmount,
                'change_amount'   => $changeAmount,
                'payment_status'  => $paymentStatus,
                'notes'           => $request->notes ?? null,
                'user_id'         => Auth::id(),
            ]);

            // ─── 5. Persist Items & Increment Stock ───────────────────────────
            foreach ($decodedItems as $item) {
                $qty         = (int)   $item['qty'];
                $productId   = (int)   $item['product_id'];
                $variationId =         $item['variation_id'] ?? null;
                $unitPrice   = (float) $item['unit_price'];
                $totalPrice  = (float) $item['total'];

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $productId,
                    'variation_id' => $variationId,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $totalPrice,
                ]);

                // Increment main product stock
                Product::find($productId)?->increment('stock_quantity', $qty);

                // Increment variation stock only when applicable
                if (!empty($variationId)) {
                    ProductVariation::find($variationId)?->increment('stock', $qty);
                }

                $this->createPurchasedUnits($productId, $qty, $variationId);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase saved successfully!',
                    'id'      => $purchase->id,
                ]);
            }

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save purchase: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save purchase: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SHOW — View a single purchase with all related data
    // ═══════════════════════════════════════════════════════════════════════════
    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'items.product',
            'items.variation',
            'user',
        ])->findOrFail($id);

        return view('admin.purchases.show', compact('purchase'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // EDIT — Load purchase into edit form with pre-filled items
    // ═══════════════════════════════════════════════════════════════════════════
    public function edit($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'items.product',
            'items.variation',
        ])->findOrFail($id);

        $colors = Color::where('is_active', true)->get();
        $sizes  = Size::where('is_active', true)->get();

        return view('admin.purchases.edit', compact('purchase', 'colors', 'sizes'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // UPDATE — Reverse old stock → delete old items → re-save → add new stock
    // ═══════════════════════════════════════════════════════════════════════════
    public function update(Request $request, $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        // ─── 1. JSON Decoding ─────────────────────────────────────────────────
        $decodedItems = json_decode($request->purchase_items, true) ?? [];
        $request->merge(['items' => $decodedItems]);

        // ─── 2. Validation ────────────────────────────────────────────────────
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'purchase_date'        => 'required|date',
            'invoice_number'       => 'nullable|string|max:255',
            'payment_method'       => 'required|string|in:cash,card,bank,credit',
            'subtotal'             => 'nullable|numeric|min:0',
            'tax_amount'           => 'nullable|numeric|min:0',
            'discount_amount'      => 'nullable|numeric|min:0',
            'total_amount'         => 'required|numeric|min:0',
            'paid_amount'          => 'required|numeric|min:0',
            'notes'                => 'nullable|string|max:1000',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.qty'          => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.total'        => 'required|numeric|min:0',
        ]);

        // ─── 3. Transaction ───────────────────────────────────────────────────
        DB::beginTransaction();

        try {
            // ─── Step A: Reverse OLD stock from existing items ─────────────────
            foreach ($purchase->items as $oldItem) {
                $this->retirePurchasedUnits(
                    (int) $oldItem->product_id,
                    (int) $oldItem->quantity,
                    $oldItem->variation_id ? (int) $oldItem->variation_id : null
                );
                Product::find($oldItem->product_id)?->decrement('stock_quantity', $oldItem->quantity);

                if (!empty($oldItem->variation_id)) {
                    ProductVariation::find($oldItem->variation_id)?->decrement('stock', $oldItem->quantity);
                }
            }

            // ─── Step B: Delete old line items ────────────────────────────────
            $purchase->items()->delete();

            // ─── Step C: Recalculate financials ───────────────────────────────
            $totalAmount  = (float) $request->total_amount;
            $paidAmount   = (float) $request->paid_amount;
            $dueAmount    = max(0, $totalAmount - $paidAmount);
            $changeAmount = max(0, $paidAmount  - $totalAmount);

            if ($dueAmount <= 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // ─── Step D: Update main purchase record ──────────────────────────
            $purchase->update([
                'supplier_id'     => $request->supplier_id,
                'purchase_date'   => $request->purchase_date,
                'invoice_number'  => $request->invoice_number ?? $purchase->invoice_number,
                'payment_method'  => $request->payment_method,
                'subtotal'        => $request->subtotal        ?? 0,
                'tax_amount'      => $request->tax_amount      ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'due_amount'      => $dueAmount,
                'change_amount'   => $changeAmount,
                'payment_status'  => $paymentStatus,
                'notes'           => $request->notes ?? null,
            ]);

            // ─── Step E: Re-create items & apply NEW stock ────────────────────
            foreach ($decodedItems as $item) {
                $qty         = (int)   $item['qty'];
                $productId   = (int)   $item['product_id'];
                $variationId =         $item['variation_id'] ?? null;
                $unitPrice   = (float) $item['unit_price'];
                $totalPrice  = (float) $item['total'];

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $productId,
                    'variation_id' => $variationId,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $totalPrice,
                ]);

                Product::find($productId)?->increment('stock_quantity', $qty);

                if (!empty($variationId)) {
                    ProductVariation::find($variationId)?->increment('stock', $qty);
                }

                $this->createPurchasedUnits($productId, $qty, $variationId);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase updated successfully!',
                ]);
            }

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update purchase: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update purchase: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DESTROY — Reverse stock → delete items → delete purchase
    // ═══════════════════════════════════════════════════════════════════════════
    public function destroy($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        DB::beginTransaction();

        try {
            // ─── 1. Reverse Stock ─────────────────────────────────────────────
            foreach ($purchase->items as $item) {
                $this->retirePurchasedUnits(
                    (int) $item->product_id,
                    (int) $item->quantity,
                    $item->variation_id ? (int) $item->variation_id : null
                );
                Product::find($item->product_id)?->decrement('stock_quantity', $item->quantity);

                if (!empty($item->variation_id)) {
                    ProductVariation::find($item->variation_id)?->decrement('stock', $item->quantity);
                }
            }

            // ─── 2. Delete Items Then Purchase ────────────────────────────────
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase deleted and inventory reversed successfully.',
                ]);
            }

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase deleted and inventory reversed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete purchase: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }

    private function createPurchasedUnits(int $productId, int $quantity, ?int $variationId): void
    {
        $units = ProductUnit::generateUnitCodes($productId, $quantity);

        if ($variationId && $units->isNotEmpty()) {
            ProductUnit::whereIn('id', $units->pluck('id'))
                ->update(['product_variation_id' => $variationId]);
        }
    }

    private function retirePurchasedUnits(int $productId, int $quantity, ?int $variationId): void
    {
        if ($quantity <= 0) {
            return;
        }

        $query = ProductUnit::where('product_id', $productId)
            ->where('status', 'available');

        if ($variationId) {
            $query->where('product_variation_id', $variationId);
        } else {
            $query->whereNull('product_variation_id');
        }

        $unitIds = $query
            ->orderByDesc('id')
            ->limit($quantity)
            ->pluck('id');

        if ($unitIds->isNotEmpty()) {
            ProductUnit::whereIn('id', $unitIds)->update([
                'status' => 'damaged',
                'updated_at' => now(),
            ]);
        }
    }
}
