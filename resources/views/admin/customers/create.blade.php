@extends('adminlte::page')

@section('title', 'Create Customer | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-users mr-2" style="color:#000000;"></i> Create Customer
    </h1>
    <div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Customers
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
                    <i class="fas fa-user-plus mr-2" style="color:#000000;"></i>
                    Customer Information
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <form id="customerForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label" style="color:#000000; font-weight:600;">Customer Name *</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Customer Name" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label" style="color:#000000; font-weight:600;">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Party Type and Balance -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="party_type" class="form-label" style="color:#000000; font-weight:600;">Party Type</label>
                                <select class="form-control" id="party_type" name="party_type" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select one</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="individual">Individual</option>
                                    <option value="government">Government</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="balance" class="form-label" style="color:#000000; font-weight:600;">Balance</label>
                                <input type="number" class="form-control" id="balance" name="balance" placeholder="Ex: 500" step="0.01" min="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Due and Email -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_amount" class="form-label" style="color:#000000; font-weight:600;">Due</label>
                                <input type="number" class="form-control" id="due_amount" name="due_amount" step="0.01" min="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label" style="color:#000000; font-weight:600;">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Credit Limit and Address -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="credit_limit" class="form-label" style="color:#000000; font-weight:600;">Party Credit Limit</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" placeholder="Ex: 800" step="0.01" min="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address" class="form-label" style="color:#000000; font-weight:600;">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter Address" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="billing_address" class="form-label" style="color:#000000; font-weight:600;">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3" placeholder="Address line 1" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- City, State, Zip Code -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="city" class="form-label" style="color:#000000; font-weight:600;">City</label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="Enter city" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="state" class="form-label" style="color:#000000; font-weight:600;">State</label>
                                <input type="text" class="form-control" id="state" name="state" placeholder="Enter state" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="zip_code" class="form-label" style="color:#000000; font-weight:600;">Zip Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Enter zip code" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country" class="form-label" style="color:#000000; font-weight:600;">Country</label>
                                <input type="text" class="form-control" id="country" name="country" placeholder="Enter country" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Empty column for balance -->
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                            <i class="fas fa-save mr-2"></i> Create Customer
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary ml-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-info-circle mr-2" style="color:#000000;"></i>
                    Quick Info
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <div class="alert alert-info" style="background:#f5f5f5; border:2px solid #333333; color:#000000;">
                    <h6><i class="fas fa-lightbulb mr-2" style="color:#000000;"></i> Customer Creation Tips</h6>
                    <ul class="mb-0" style="color:#000000;">
                        <li>Name and Phone are required fields</li>
                        <li>Phone number must be unique</li>
                        <li>Customer will be available in POS immediately after creation</li>
                        <li>You can search customers by name or phone in POS</li>
                    </ul>
                </div>
                <div class="alert alert-success" style="background:#f5f5f5; border:2px solid #333333; color:#000000;">
                    <h6><i class="fas fa-check-circle mr-2" style="color:#000000;"></i> Benefits</h6>
                    <ul class="mb-0" style="color:#000000;">
                        <li>Track customer purchase history</li>
                        <li>Manage customer dues</li>
                        <li>Quick customer selection in POS</li>
                        <li>Better customer service</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
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

@section('adminlte_js')
<script>
document.getElementById('customerForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/customers', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Customer created successfully!');
            window.location.href = '{{ route("customers.index") }}';
        } else {
            alert('Error creating customer. Please check your inputs.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating customer. Please try again.');
    });
});
</script>
@stop
