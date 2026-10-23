@extends('adminlte::page')

@section('title', 'Supplier Payment | ClothBD')

@section('adminlte_css')
<style>
    :root {
        --sp-primary: #123866;
        --sp-accent: #d8a821;
        --sp-border: #d7dee8;
        --sp-muted: #64748b;
        --sp-bg: #f4f7fb;
        --sp-danger: #dc2626;
        --sp-success: #047857;
    }

    .supplier-payment-page {
        background: var(--sp-bg);
        min-height: calc(100vh - 80px);
        padding: 20px;
    }

    .sp-header {
        background: var(--sp-primary);
        border-bottom: 4px solid var(--sp-accent);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .sp-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
    }

    .sp-subtitle {
        margin: 5px 0 0;
        color: #dbe7f5;
        font-size: 13px;
    }

    .sp-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .sp-actions a,
    .sp-button {
        border: 0;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        min-height: 38px;
        padding: 8px 14px;
        text-decoration: none;
    }

    .sp-actions a {
        background: #fff;
        color: var(--sp-primary);
    }

    .sp-button {
        background: var(--sp-primary);
        color: #fff;
        cursor: pointer;
    }

    .sp-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 18px;
    }

    .sp-panel,
    .sp-summary {
        background: #fff;
        border: 1px solid var(--sp-border);
        border-radius: 6px;
        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.06);
    }

    .sp-panel-header {
        border-bottom: 1px solid var(--sp-border);
        padding: 14px 16px;
    }

    .sp-panel-header h2 {
        color: var(--sp-primary);
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }

    .sp-form {
        padding: 16px;
    }

    .sp-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .sp-field {
        margin-bottom: 14px;
    }

    .sp-field label {
        color: #334155;
        display: block;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .sp-field label span {
        color: var(--sp-danger);
    }

    .sp-field input,
    .sp-field select,
    .sp-field textarea {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        color: #0f172a;
        font-size: 14px;
        min-height: 40px;
        padding: 8px 10px;
        width: 100%;
    }

    .sp-field input:focus,
    .sp-field select:focus,
    .sp-field textarea:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 3px rgba(18, 56, 102, 0.12);
        outline: none;
    }

    .sp-help {
        color: var(--sp-muted);
        display: block;
        font-size: 12px;
        margin-top: 5px;
    }

    .sp-submit-row {
        border-top: 1px solid var(--sp-border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 6px;
        padding-top: 16px;
    }

    .sp-link-button {
        align-items: center;
        background: #eef2f7;
        border-radius: 4px;
        color: #334155;
        display: inline-flex;
        font-size: 13px;
        font-weight: 700;
        min-height: 38px;
        padding: 8px 14px;
        text-decoration: none;
    }

    .sp-summary {
        padding: 16px;
    }

    .sp-summary h3 {
        color: var(--sp-primary);
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 12px;
    }

    .sp-stat {
        border: 1px solid var(--sp-border);
        border-radius: 5px;
        margin-bottom: 10px;
        padding: 11px 12px;
    }

    .sp-stat span {
        color: var(--sp-muted);
        display: block;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .sp-stat strong {
        color: #111827;
        display: block;
        font-size: 18px;
        line-height: 1.2;
    }

    .sp-stat.due strong {
        color: var(--sp-danger);
    }

    .sp-stat.pay strong {
        color: var(--sp-success);
    }

    .sp-alert {
        border-radius: 4px;
        font-size: 13px;
        margin-bottom: 14px;
        padding: 12px 14px;
    }

    .sp-alert.error {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #991b1b;
    }

    .sp-alert.success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    @media (max-width: 900px) {
        .sp-grid,
        .sp-row {
            grid-template-columns: 1fr;
        }

        .sp-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@stop

@section('content')
@php
    $formAction = request()->routeIs('supplier-payments.quick-create')
        ? route('supplier-payments.quick-store')
        : route('supplier-payments.store');
    $oldSupplierId = old('supplier_id', request('supplier_id', optional(optional($selectedPurchase)->supplier)->id));
    $oldPurchaseId = old('purchase_id', optional($selectedPurchase)->id);
    $suppliersForJson = $suppliers->map(function ($supplier) {
        return [
            'id' => (string) $supplier->id,
            'name' => $supplier->company_name ? $supplier->company_name . ' - ' . $supplier->name : $supplier->name,
            'due_amount' => (float) ($supplier->due_amount ?? 0),
        ];
    })->values();
    $duePurchasesForJson = $duePurchases->map(function ($purchase) {
        return [
            'id' => (string) $purchase->id,
            'supplier_id' => (string) $purchase->supplier_id,
            'invoice_number' => $purchase->invoice_number,
            'total_amount' => (float) ($purchase->total_amount ?? 0),
            'paid_amount' => (float) ($purchase->paid_amount ?? 0),
            'due_amount' => (float) ($purchase->due_amount ?? 0),
            'created_at' => optional($purchase->created_at)->format('Y-m-d'),
        ];
    })->values();
@endphp

<div class="supplier-payment-page">
    <div class="sp-header">
        <div>
            <h1 class="sp-title"><i class="fas fa-money-bill-wave mr-2"></i>Supplier Payment</h1>
            <p class="sp-subtitle">Record supplier due payments and update purchase balances.</p>
        </div>
        <div class="sp-actions">
            <a href="{{ route('supplier-payments.quick-index') }}"><i class="fas fa-list"></i> Payment History</a>
            <a href="{{ route('supplier.dues') }}"><i class="fas fa-industry"></i> Supplier Dues</a>
        </div>
    </div>

    @if(session('success'))
        <div class="sp-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="sp-alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="sp-alert error">
            <strong>Please fix these errors:</strong>
            <ul class="mb-0 mt-2 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="sp-grid">
        <div class="sp-panel">
            <div class="sp-panel-header">
                <h2><i class="fas fa-receipt mr-2"></i>Payment Details</h2>
            </div>

            <form class="sp-form" method="POST" action="{{ $formAction }}">
                @csrf

                <div class="sp-row">
                    <div class="sp-field">
                        <label>Supplier <span>*</span></label>
                        <select name="supplier_id" id="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (string) $oldSupplierId === (string) $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->company_name ? $supplier->company_name . ' - ' . $supplier->name : $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sp-field">
                        <label>Purchase Bill</label>
                        <select name="purchase_id" id="purchase_id" data-selected="{{ $oldPurchaseId }}">
                            <option value="">Oldest due first</option>
                        </select>
                        <small class="sp-help">Leave blank to adjust the oldest unpaid bills first.</small>
                    </div>
                </div>

                <div class="sp-row">
                    <div class="sp-field">
                        <label>Pay Amount <span>*</span></label>
                        <input type="number" name="amount" id="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                    </div>

                    <div class="sp-field">
                        <label>Payment Method <span>*</span></label>
                        <select name="payment_method" required>
                            <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="credit" {{ old('payment_method') === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                </div>

                <div class="sp-row">
                    <div class="sp-field">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}">
                    </div>

                    <div class="sp-field">
                        <label>Current Due</label>
                        <input type="text" id="current_due_display" value="Tk 0.00" readonly>
                    </div>
                </div>

                <div class="sp-field">
                    <label>Note</label>
                    <textarea name="note" rows="3" placeholder="Optional note">{{ old('note') }}</textarea>
                </div>

                <div class="sp-submit-row">
                    <a class="sp-link-button" href="{{ route('supplier-payments.quick-index') }}">Cancel</a>
                    <button type="submit" class="sp-button">
                        <i class="fas fa-save"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>

        <div class="sp-summary">
            <h3><i class="fas fa-chart-simple mr-2"></i>Summary</h3>
            <div class="sp-stat">
                <span>Supplier</span>
                <strong id="summary_supplier">Not selected</strong>
            </div>
            <div class="sp-stat due">
                <span>Current Due</span>
                <strong id="summary_due">Tk 0.00</strong>
            </div>
            <div class="sp-stat pay">
                <span>Paying Now</span>
                <strong id="summary_pay">Tk 0.00</strong>
            </div>
            <div class="sp-stat due">
                <span>Remaining Due</span>
                <strong id="summary_remaining">Tk 0.00</strong>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const suppliers = @json($suppliersForJson);
    const purchases = @json($duePurchasesForJson);

    const supplierSelect = document.getElementById('supplier_id');
    const purchaseSelect = document.getElementById('purchase_id');
    const amountInput = document.getElementById('amount');
    const currentDueDisplay = document.getElementById('current_due_display');
    const summarySupplier = document.getElementById('summary_supplier');
    const summaryDue = document.getElementById('summary_due');
    const summaryPay = document.getElementById('summary_pay');
    const summaryRemaining = document.getElementById('summary_remaining');

    function money(value) {
        return 'Tk ' + (parseFloat(value) || 0).toFixed(2);
    }

    function selectedSupplier() {
        return suppliers.find(function (supplier) {
            return supplier.id === supplierSelect.value;
        });
    }

    function selectedPurchase() {
        return purchases.find(function (purchase) {
            return purchase.id === purchaseSelect.value;
        });
    }

    function currentDue() {
        const purchase = selectedPurchase();
        if (purchase) {
            return parseFloat(purchase.due_amount) || 0;
        }

        const supplier = selectedSupplier();
        return supplier ? (parseFloat(supplier.due_amount) || 0) : 0;
    }

    function rebuildPurchaseOptions() {
        const selectedSupplierId = supplierSelect.value;
        const selectedPurchaseId = purchaseSelect.dataset.selected || purchaseSelect.value;

        purchaseSelect.innerHTML = '<option value="">Oldest due first</option>';

        purchases
            .filter(function (purchase) {
                return purchase.supplier_id === selectedSupplierId;
            })
            .forEach(function (purchase) {
                const option = document.createElement('option');
                option.value = purchase.id;
                option.textContent = (purchase.invoice_number || ('Purchase #' + purchase.id))
                    + ' | Due: ' + money(purchase.due_amount)
                    + ' | ' + (purchase.created_at || '');
                purchaseSelect.appendChild(option);
            });

        if (selectedPurchaseId && purchaseSelect.querySelector('option[value="' + selectedPurchaseId + '"]')) {
            purchaseSelect.value = selectedPurchaseId;
        }

        purchaseSelect.dataset.selected = '';
        updateSummary();
    }

    function updateSummary() {
        const supplier = selectedSupplier();
        const due = currentDue();
        const pay = Math.max(0, parseFloat(amountInput.value) || 0);
        const remaining = Math.max(0, due - pay);

        summarySupplier.textContent = supplier ? supplier.name : 'Not selected';
        summaryDue.textContent = money(due);
        summaryPay.textContent = money(pay);
        summaryRemaining.textContent = money(remaining);
        currentDueDisplay.value = money(due);
    }

    supplierSelect.addEventListener('change', rebuildPurchaseOptions);
    purchaseSelect.addEventListener('change', updateSummary);
    amountInput.addEventListener('input', updateSummary);

    rebuildPurchaseOptions();
});
</script>
@stop
