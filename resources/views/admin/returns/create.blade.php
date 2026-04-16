@extends('adminlte::page')

@section('title', 'New Return | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-undo mr-2" style="color:#000000;"></i> New Return
    </h1>
    <div>
        <a href="{{ route('returns.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Returns
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
                    <i class="fas fa-undo mr-2" style="color:#000000;"></i>
                    Return Information
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <form method="POST" action="{{ route('returns.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sale_id" class="form-label" style="color:#000000; font-weight:600;">Sale Invoice *</label>
                                <select class="form-control" id="sale_id" name="sale_id" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Sale Invoice</option>
                                    <!-- Sales will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_id" class="form-label" style="color:#000000; font-weight:600;">Customer</label>
                                <select class="form-control" id="customer_id" name="customer_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Customer (Optional)</option>
                                    <!-- Customers will be loaded here -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_id" class="form-label" style="color:#000000; font-weight:600;">Product *</label>
                                <select class="form-control" id="product_id" name="product_id" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Product</option>
                                    <!-- Products will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity" class="form-label" style="color:#000000; font-weight:600;">Quantity *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="refund_amount" class="form-label" style="color:#000000; font-weight:600;">Refund Amount *</label>
                                <input type="number" class="form-control" id="refund_amount" name="refund_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="refund_method" class="form-label" style="color:#000000; font-weight:600;">Refund Method *</label>
                                <select class="form-control" id="refund_method" name="refund_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="credit">Store Credit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reason" class="form-label" style="color:#000000; font-weight:600;">Return Reason *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter the reason for return" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label" style="color:#000000; font-weight:600;">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Enter any additional notes"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Return
                        </button>
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary ml-2">
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
                    Return Guidelines
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Select the original sale invoice</li>
                        <li>Choose the product being returned</li>
                        <li>Enter the correct quantity</li>
                        <li>Specify refund amount accurately</li>
                        <li>Provide clear return reason</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Returns will be processed after approval. Make sure all details are accurate before submitting.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Load data for dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // Load sales
    fetch('/admin/sales/search')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const select = document.getElementById('sale_id');
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            if (data && data.length > 0) {
                data.forEach(sale => {
                    const option = document.createElement('option');
                    option.value = sale.id;
                    option.textContent = sale.display_text || sale.invoice_number || 'Sale #' + sale.id;
                    select.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "No sales found";
                option.disabled = true;
                select.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading sales:', error);
            const select = document.getElementById('sale_id');
            const option = document.createElement('option');
            option.value = "";
            option.textContent = "Error loading sales";
            option.disabled = true;
            select.appendChild(option);
        });

    // Load customers
    fetch('/admin/customers/search')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('customer_id');
            data.forEach(customer => {
                const option = document.createElement('option');
                option.value = customer.id;
                option.textContent = customer.display_text;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading customers:', error));

    // Load products
    fetch('/admin/products/search')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('product_id');
            data.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading products:', error));
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-group { margin-bottom: 1rem; }
.alert { margin-bottom: 1rem; }
</style>
@stop
