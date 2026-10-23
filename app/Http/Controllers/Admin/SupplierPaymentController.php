<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplierPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = SupplierPayment::with(['supplier', 'purchase'])
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.supplier-payments.index', compact('payments', 'suppliers'));
    }

    public function create(Request $request): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();

        $duePurchases = Purchase::with('supplier')
            ->where('due_amount', '>', 0)
            ->orderBy('created_at')
            ->get();

        $selectedPurchase = null;
        if ($request->filled('purchase_id')) {
            $selectedPurchase = Purchase::with('supplier')->find($request->purchase_id);
        }

        return view('admin.supplier-payments.create', compact(
            'suppliers',
            'duePurchases',
            'selectedPurchase'
        ));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('supplier_payments')) {
            return $this->paymentResponse(
                $request,
                false,
                'supplier_payments table not found. Please create the table first.'
            );
        }

        $validated = $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'purchase_id'    => 'nullable|exists:purchases,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank,credit',
            'payment_date'   => 'nullable|date',
            'note'           => 'nullable|string|max:1000',
        ]);

        try {
            $createdPaymentId = null;

            DB::transaction(function () use ($validated, &$createdPaymentId) {
                $supplier = Supplier::whereKey($validated['supplier_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $amount = (float) $validated['amount'];
                $purchaseId = $validated['purchase_id'] ?? null;
                $appliedToPurchases = 0.0;
                $supplierDueReduction = 0.0;
                $overPayment = 0.0;
                $paymentPurchaseId = null;

                if ($purchaseId) {
                    $purchase = Purchase::whereKey($purchaseId)
                        ->where('supplier_id', $supplier->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$purchase) {
                        throw ValidationException::withMessages([
                            'purchase_id' => 'Selected purchase does not belong to this supplier.',
                        ]);
                    }

                    if ((float) $purchase->due_amount <= 0) {
                        throw ValidationException::withMessages([
                            'purchase_id' => 'Selected purchase has no due amount.',
                        ]);
                    }

                    $appliedToPurchases = $this->applyPaymentToPurchase($purchase, $amount);
                    $supplierDueReduction = $appliedToPurchases;
                    $overPayment = max(0, $amount - $appliedToPurchases);
                    $paymentPurchaseId = $purchase->id;
                } else {
                    $duePurchases = Purchase::where('supplier_id', $supplier->id)
                        ->where('due_amount', '>', 0)
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    $totalPurchaseDueBefore = $duePurchases->sum(function ($purchase) {
                        return (float) $purchase->due_amount;
                    });

                    $remainingAmount = $amount;

                    foreach ($duePurchases as $purchase) {
                        if ($remainingAmount <= 0) {
                            break;
                        }

                        $applied = $this->applyPaymentToPurchase($purchase, $remainingAmount);
                        $appliedToPurchases += $applied;
                        $remainingAmount -= $applied;
                    }

                    $manualSupplierDue = max(0, (float) $supplier->due_amount - $totalPurchaseDueBefore);
                    $manualDuePayment = min($remainingAmount, $manualSupplierDue);
                    $remainingAmount -= $manualDuePayment;

                    $supplierDueReduction = $appliedToPurchases + $manualDuePayment;
                    $overPayment = max(0, $remainingAmount);
                }

                $payment = SupplierPayment::create([
                    'supplier_id'    => $supplier->id,
                    'purchase_id'    => $paymentPurchaseId,
                    'amount'         => $amount,
                    'payment_method' => $validated['payment_method'],
                    'payment_date'   => $validated['payment_date'] ?? now()->toDateString(),
                    'note'           => $validated['note'] ?? 'Supplier due payment',
                    'user_id'        => auth()->id(),
                ]);

                $createdPaymentId = $payment->id;

                $supplier->due_amount = max(0, (float) $supplier->due_amount - $supplierDueReduction);
                $supplier->balance = (float) ($supplier->balance ?? 0) + $overPayment;
                $supplier->save();
            });

            return $this->paymentResponse(
                $request,
                true,
                'Supplier payment saved successfully! Payment invoice is opening in a new tab.',
                $createdPaymentId
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->paymentResponse(
                $request,
                false,
                'Failed to save supplier payment: ' . $e->getMessage()
            );
        }
    }

    public function duePurchases(int $supplierId): JsonResponse
    {
        $purchases = Purchase::where('supplier_id', $supplierId)
            ->where('due_amount', '>', 0)
            ->orderBy('created_at')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'invoice_number' => $purchase->invoice_number,
                    'total_amount' => (float) $purchase->total_amount,
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'created_at' => optional($purchase->created_at)->format('Y-m-d'),
                ];
            });

        return response()->json($purchases);
    }

    /**
     * Download supplier payment receipt as PDF (invoice-style).
     */
    public function invoicePdf(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load(['supplier', 'purchase', 'user']);

        $pdf = Pdf::loadView('admin.supplier-payments.invoice-pdf', [
            'payment' => $supplierPayment,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $no = str_pad((string) $supplierPayment->id, 6, '0', STR_PAD_LEFT);
        $filename = 'supplier-payment-receipt-' . $no . '.pdf';

        return $pdf->stream($filename);
    }

    private function applyPaymentToPurchase(Purchase $purchase, float $amount): float
    {
        $dueBeforePayment = (float) $purchase->due_amount;
        $appliedAmount = min($amount, $dueBeforePayment);

        if ($appliedAmount <= 0) {
            return 0.0;
        }

        $purchase->paid_amount = (float) $purchase->paid_amount + $appliedAmount;
        $purchase->due_amount = max(0, $dueBeforePayment - $appliedAmount);
        $purchase->payment_status = $this->resolvePaymentStatus($purchase);
        $purchase->save();

        return $appliedAmount;
    }

    private function resolvePaymentStatus(Purchase $purchase): string
    {
        if ((float) $purchase->due_amount <= 0) {
            return 'paid';
        }

        return (float) $purchase->paid_amount > 0 ? 'partial' : 'pending';
    }

    private function paymentResponse(Request $request, bool $success, string $message, ?int $supplierPaymentIdForPdf = null)
    {
        if ($success && $supplierPaymentIdForPdf) {
            session()->flash('open_supplier_payment_pdf', $supplierPaymentIdForPdf);
        }

        if ($request->expectsJson()) {
            $payload = [
                'success' => $success,
                'message' => $message,
            ];

            if ($success && $supplierPaymentIdForPdf) {
                $payload['invoice_pdf_url'] = route('supplier-payments.invoice-pdf', ['supplierPayment' => $supplierPaymentIdForPdf]);
            }

            return response()->json($payload, $success ? 200 : 422);
        }

        $indexRoute = $request->routeIs('supplier-payments.quick-store')
            ? 'supplier-payments.quick-index'
            : 'supplier-payments.index';

        $redirect = $success
            ? redirect()->route($indexRoute)
            : redirect()->back()->withInput();

        return $redirect->with($success ? 'success' : 'error', $message);
    }
}
