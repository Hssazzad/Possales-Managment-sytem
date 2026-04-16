@extends('adminlte::page')

@section('title', 'New Purchase | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-shopping-cart mr-2" style="color:#000000;"></i> New Purchase
    </h1>
    <div>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Purchases
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-list mr-2" style="color:#000000;"></i>
                    Purchase Information
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <form method="POST" action="{{ route('purchases.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_id" class="form-label" style="color:#000000; font-weight:600;">Supplier *</label>
                                <select class="form-control" id="supplier_id" name="supplier_id" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Supplier</option>
                                    <!-- Suppliers will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_date" class="form-label" style="color:#000000; font-weight:600;">Purchase Date *</label>
                                <input type="date" class="form-control" id="purchase_date" value="{{ date('Y-m-d') }}" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_number" class="form-label" style="color:#000000; font-weight:600;">Invoice Number</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="Enter invoice number" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method" class="form-label" style="color:#000000; font-weight:600;">Payment Method *</label>
                                <select class="form-control" id="payment_method" name="payment_method" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="credit">Store Credit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subtotal" class="form-label" style="color:#000000; font-weight:600;">Subtotal *</label>
                                <input type="number" class="form-control" id="subtotal" name="subtotal" step="0.01" min="0" required style="border:2px solid #333333; background:#ffffff; color:#000000;">="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_amount" class="form-label" style="color:#000000; font-weight:600;">Tax Amount *</label>
                                <input type="number" class="form-control" id="tax_amount" name="tax_amount" step="0.01" min="0" value="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount_amount" class="form-label" style="color:#000000; font-weight:600;">Discount Amount</label>
                                <input type="number" class="form-control" id="discount_amount" name="discount_amount" step="0.01" min="0" value="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_amount" class="form-label" style="color:#000000; font-weight:600;">Total Amount *</label>
                                <input type="number" class="form-control" id="total_amount" name="total_amount" step="0.01" min="0" value="0" readonly style="border:2px solid #333333; background:#f5f5f5; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="paid_amount" class="form-label" style="color:#000000; font-weight:600;">Paid Amount *</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" step="0.01" min="0" value="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label" style="color:#000000; font-weight:600;">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Enter any additional notes" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                            <i class="fas fa-save mr-2"></i> Save Purchase
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary ml-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#17a2b8;"></i>
                    Purchase Information
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Select supplier from dropdown</li>
                        <li>Enter purchase details accurately</li>
                        <li>Double-check totals before saving</li>
                        <li>Keep invoice numbers organized</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Make sure to enter correct supplier information and purchase details. This will affect inventory and financial records.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Load suppliers
$(document).ready(function() {
    // Load suppliers from API
    fetch('/admin/suppliers/search')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('supplier_id');
            data.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.id;
                option.textContent = supplier.display_text;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading suppliers:', error));

    // Calculate totals
    function calculateTotals() {
        const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;

        const total = subtotal + tax - discount;
        document.getElementById('total_amount').value = total.toFixed(2);
        document.getElementById('paid_amount').value = total.toFixed(2);
    }

    document.getElementById('subtotal').addEventListener('input', calculateTotals);
    document.getElementById('tax_amount').addEventListener('input', calculateTotals);
    document.getElementById('discount_amount').addEventListener('input', calculateTotals);
});
</script>
@stop

@section('adminlte_css')
<style>
.form-group { margin-bottom: 1rem; }
.alert { margin-bottom: 1rem; }
</style>
@stop
