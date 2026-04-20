@extends('adminlte::page')

@section('title', 'New Purchase | PosSales')

@section('content_header')
<header class="gov-page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h1 class="gov-page-title">
            <i class="fas fa-shopping-cart mr-2" aria-hidden="true"></i>
            New Purchase
        </h1>
        <p class="gov-page-subtitle mb-0">Create and validate supplier purchase details.</p>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn gov-btn gov-btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>
        Back to Purchases
    </a>
</header>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <section class="card gov-card" aria-labelledby="purchase-form-heading">
            <div class="card-header gov-card-header">
                <h2 id="purchase-form-heading" class="gov-section-title mb-0">
                    <i class="fas fa-list mr-2" aria-hidden="true"></i>
                    Purchase information
                </h2>
            </div>
            <div class="card-body gov-card-body">
                @if($errors->any())
                    <div class="alert alert-danger gov-alert" role="alert" aria-live="assertive">
                        <h3 class="gov-alert-title">Please correct the following:</h3>
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="purchase-create-form" method="POST" action="{{ route('purchases.store') }}" novalidate>
                    @csrf

                    <fieldset class="gov-form-section">
                        <legend class="gov-form-legend">Supplier and invoice</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id" class="gov-label">Supplier <span aria-hidden="true">*</span></label>
                                    <select class="form-control gov-input @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required aria-required="true" aria-describedby="supplier_id_feedback">
                                        <option value="">Select supplier</option>
                                    </select>
                                    <small id="supplier_id_feedback" class="gov-validation-message" aria-live="polite">@error('supplier_id'){{ $message }}@enderror</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purchase_date" class="gov-label">Purchase date <span aria-hidden="true">*</span></label>
                                    <input type="date" class="form-control gov-input @error('purchase_date') is-invalid @enderror" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required aria-required="true" aria-describedby="purchase_date_feedback">
                                    <small id="purchase_date_feedback" class="gov-validation-message" aria-live="polite">@error('purchase_date'){{ $message }}@enderror</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="invoice_number" class="gov-label">Invoice number</label>
                                    <input type="text" class="form-control gov-input @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Enter invoice number" aria-describedby="invoice_number_feedback">
                                    <small id="invoice_number_feedback" class="gov-validation-message" aria-live="polite">@error('invoice_number'){{ $message }}@enderror</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method" class="gov-label">Payment method <span aria-hidden="true">*</span></label>
                                    <select class="form-control gov-input @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required aria-required="true" aria-describedby="payment_method_feedback">
                                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                                        <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="credit" {{ old('payment_method') === 'credit' ? 'selected' : '' }}>Store Credit</option>
                                    </select>
                                    <small id="payment_method_feedback" class="gov-validation-message" aria-live="polite">@error('payment_method'){{ $message }}@enderror</small>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="gov-form-section">
                        <legend class="gov-form-legend">Amounts</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subtotal" class="gov-label">Subtotal <span aria-hidden="true">*</span></label>
                                    <input type="number" class="form-control gov-input @error('subtotal') is-invalid @enderror" id="subtotal" name="subtotal" step="0.01" min="0" value="{{ old('subtotal') }}" required aria-required="true" aria-describedby="subtotal_feedback">
                                    <small id="subtotal_feedback" class="gov-validation-message" aria-live="polite">@error('subtotal'){{ $message }}@enderror</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_amount" class="gov-label">Tax amount</label>
                                    <input type="number" class="form-control gov-input @error('tax_amount') is-invalid @enderror" id="tax_amount" name="tax_amount" step="0.01" min="0" value="{{ old('tax_amount', 0) }}" aria-describedby="tax_amount_feedback">
                                    <small id="tax_amount_feedback" class="gov-validation-message" aria-live="polite">@error('tax_amount'){{ $message }}@enderror</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="discount_amount" class="gov-label">Discount amount</label>
                                    <input type="number" class="form-control gov-input @error('discount_amount') is-invalid @enderror" id="discount_amount" name="discount_amount" step="0.01" min="0" value="{{ old('discount_amount', 0) }}" aria-describedby="discount_amount_feedback">
                                    <small id="discount_amount_feedback" class="gov-validation-message" aria-live="polite">@error('discount_amount'){{ $message }}@enderror</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_amount" class="gov-label">Total amount <span aria-hidden="true">*</span></label>
                                    <input type="number" class="form-control gov-input gov-input-readonly @error('total_amount') is-invalid @enderror" id="total_amount" name="total_amount" step="0.01" min="0" value="{{ old('total_amount', 0) }}" readonly required aria-required="true" aria-describedby="total_amount_feedback">
                                    <small id="total_amount_feedback" class="gov-validation-message" aria-live="polite">@error('total_amount'){{ $message }}@enderror</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paid_amount" class="gov-label">Paid amount <span aria-hidden="true">*</span></label>
                                    <input type="number" class="form-control gov-input @error('paid_amount') is-invalid @enderror" id="paid_amount" name="paid_amount" step="0.01" min="0" value="{{ old('paid_amount', 0) }}" required aria-required="true" aria-describedby="paid_amount_feedback">
                                    <small id="paid_amount_feedback" class="gov-validation-message" aria-live="polite">@error('paid_amount'){{ $message }}@enderror</small>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="gov-form-section">
                        <legend class="gov-form-legend">Additional notes</legend>
                        <div class="form-group mb-0">
                            <label for="notes" class="gov-label">Notes</label>
                            <textarea class="form-control gov-input @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Enter any additional notes" aria-describedby="notes_feedback">{{ old('notes') }}</textarea>
                            <small id="notes_feedback" class="gov-validation-message" aria-live="polite">@error('notes'){{ $message }}@enderror</small>
                        </div>
                    </fieldset>

                    <div class="gov-form-actions">
                        <button type="submit" class="btn gov-btn gov-btn-primary">
                            <i class="fas fa-save mr-2" aria-hidden="true"></i>
                            Save Purchase
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn gov-btn gov-btn-secondary ml-2">
                            <i class="fas fa-times mr-2" aria-hidden="true"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <aside class="card gov-card" aria-labelledby="purchase-help-heading">
            <div class="card-header gov-card-header">
                <h2 id="purchase-help-heading" class="gov-section-title mb-0">
                    <i class="fas fa-info-circle mr-2" aria-hidden="true"></i>
                    Guidance
                </h2>
            </div>
            <div class="card-body gov-card-body">
                <section class="gov-note-panel mb-3" aria-labelledby="quick-tips-heading">
                    <h3 id="quick-tips-heading" class="gov-note-title">Quick tips</h3>
                    <ul class="mb-0 pl-3">
                        <li>Select the correct supplier before entering amounts.</li>
                        <li>Use invoice numbers to keep records searchable.</li>
                        <li>Review totals before submitting.</li>
                    </ul>
                </section>
                <section class="gov-warning-panel" aria-labelledby="important-heading">
                    <h3 id="important-heading" class="gov-note-title">Important</h3>
                    <p class="mb-0">Purchase entries update inventory and finance records. Ensure all values are accurate before saving.</p>
                </section>
            </div>
        </aside>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
$(document).ready(function() {
    const form = document.getElementById('purchase-create-form');
    const supplierSelect = document.getElementById('supplier_id');

    fetch('/admin/suppliers/search')
        .then(response => response.json())
        .then(data => {
            const selectedSupplier = @json(old('supplier_id'));
            data.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.id;
                option.textContent = supplier.display_text;
                if (selectedSupplier && selectedSupplier === String(supplier.id)) {
                    option.selected = true;
                }
                supplierSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading suppliers:', error));

    function calculateTotals() {
        const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const total = subtotal + tax - discount;

        document.getElementById('total_amount').value = total.toFixed(2);
        document.getElementById('paid_amount').value = total.toFixed(2);
    }

    function setValidationFeedback(field) {
        const feedback = document.getElementById(`${field.id}_feedback`);

        if (!feedback) {
            return;
        }

        if (field.validity.valid) {
            if (!feedback.dataset.serverError) {
                feedback.textContent = '';
            }
            field.classList.remove('is-invalid');
            return;
        }

        feedback.textContent = field.validationMessage;
        field.classList.add('is-invalid');
    }

    const trackedFields = Array.from(form.querySelectorAll('input, select, textarea'));

    trackedFields.forEach(field => {
        const feedback = document.getElementById(`${field.id}_feedback`);

        if (feedback && field.classList.contains('is-invalid')) {
            feedback.dataset.serverError = 'true';
        }

        const handleFieldInteraction = () => {
            if (feedback && feedback.dataset.serverError) {
                delete feedback.dataset.serverError;
            }
            setValidationFeedback(field);
        };

        field.addEventListener('input', handleFieldInteraction);
        field.addEventListener('change', handleFieldInteraction);
    });

    form.addEventListener('submit', function(event) {
        let hasInvalidField = false;
        let firstInvalidField = null;

        trackedFields.forEach(field => {
            setValidationFeedback(field);
            if (!field.validity.valid) {
                hasInvalidField = true;
                firstInvalidField = firstInvalidField || field;
            }
        });

        if (hasInvalidField) {
            event.preventDefault();
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
        }
    });

    document.getElementById('subtotal').addEventListener('input', calculateTotals);
    document.getElementById('tax_amount').addEventListener('input', calculateTotals);
    document.getElementById('discount_amount').addEventListener('input', calculateTotals);

});
</script>
@stop

@section('adminlte_css')
<link rel="stylesheet" href="{{ asset('css/admin/purchases/create.css') }}">
@stop
