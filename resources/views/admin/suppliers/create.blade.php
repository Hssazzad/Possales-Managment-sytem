@extends('adminlte::page')

@section('title', 'Create Supplier | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-truck mr-2" style="color:#000000;"></i> Create Supplier
    </h1>
    <div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Suppliers
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
                    <i class="fas fa-truck mr-2" style="color:#000000;"></i>
                    Supplier Information
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <form id="supplierForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label" style="color:#000000; font-weight:600;">Supplier Name *</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Supplier Name" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label" style="color:#000000; font-weight:600;">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Company and Supplier Type -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_name" class="form-label" style="color:#000000; font-weight:600;">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter Company Name" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_type" class="form-label" style="color:#000000; font-weight:600;">Supplier Type</label>
                                <select class="form-control" id="supplier_type" name="supplier_type" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select one</option>
                                    <option value="manufacturer">Manufacturer</option>
                                    <option value="distributor">Distributor</option>
                                    <option value="wholesaler">Wholesaler</option>
                                    <option value="retailer">Retailer</option>
                                    <option value="service">Service Provider</option>
                                    <option value="importer">Importer</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Balance and Due -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="balance" class="form-label" style="color:#000000; font-weight:600;">Balance</label>
                                <input type="number" class="form-control" id="balance" name="balance" placeholder="Ex: 500" step="0.01" min="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_amount" class="form-label" style="color:#000000; font-weight:600;">Due</label>
                                <input type="number" class="form-control" id="due_amount" name="due_amount" step="0.01" min="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Email and Credit Limit -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label" style="color:#000000; font-weight:600;">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="credit_limit" class="form-label" style="color:#000000; font-weight:600;">Credit Limit</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" placeholder="Ex: 800" step="0.01" min="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address" class="form-label" style="color:#000000; font-weight:600;">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter Address" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="billing_address" class="form-label" style="color:#000000; font-weight:600;">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3" placeholder="Address line 1" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
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

                    <!-- Country and Contact Person -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country" class="form-label" style="color:#000000; font-weight:600;">Country</label>
                                <input type="text" class="form-control" id="country" name="country" placeholder="Enter country" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_person" class="form-label" style="color:#000000; font-weight:600;">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Enter Contact Person" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <!-- Tax Number and Notes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_number" class="form-label" style="color:#000000; font-weight:600;">Tax Number</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" placeholder="Enter Tax Number" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes" class="form-label" style="color:#000000; font-weight:600;">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter any notes" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                            <i class="fas fa-save mr-2"></i> Create Supplier
                        </button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary ml-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
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
                    <h6><i class="fas fa-lightbulb mr-2" style="color:#000000;"></i> Supplier Creation Tips</h6>
                    <ul class="mb-0" style="color:#000000;">
                        <li>Name and Phone are required fields</li>
                        <li>Phone number must be unique</li>
                        <li>Supplier will be available in POS immediately after creation</li>
                        <li>You can search suppliers by name or phone in POS</li>
                    </ul>
                </div>

                <div class="alert alert-success" style="background:#f5f5f5; border:2px solid #333333; color:#000000;">
                    <h6><i class="fas fa-check-circle mr-2" style="color:#000000;"></i> Benefits</h6>
                    <ul class="mb-0" style="color:#000000;">
                        <li>Track supplier purchase history</li>
                        <li>Manage supplier dues</li>
                        <li>Quick supplier selection in POS</li>
                        <li>Better supplier relationship management</li>
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
document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/suppliers', {
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
            alert('Supplier created successfully!');
            window.location.href = '{{ route("suppliers.index") }}';
        } else {
            alert('Error creating supplier. Please check your inputs.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating supplier. Please try again.');
    });
});
</script>
@stop
