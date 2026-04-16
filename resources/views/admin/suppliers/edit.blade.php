@extends('adminlte::page')

@section('title', 'Edit Supplier | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-truck mr-2" style="color:#ffc107;"></i> Edit Supplier
    </h1>
    <div>
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-info btn-sm mr-2">
            <i class="fas fa-eye mr-1"></i> View
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-truck mr-2" style="color:#ffc107;"></i>
                    Edit Supplier Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Supplier Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="{{ $supplier->phone }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Company and Supplier Type -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="{{ $supplier->company_name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_type" class="form-label">Supplier Type</label>
                                <select class="form-control" id="supplier_type" name="supplier_type">
                                    <option value="">Select one</option>
                                    <option value="manufacturer" {{ $supplier->supplier_type == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                    <option value="distributor" {{ $supplier->supplier_type == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                    <option value="wholesaler" {{ $supplier->supplier_type == 'wholesaler' ? 'selected' : '' }}>Wholesaler</option>
                                    <option value="retailer" {{ $supplier->supplier_type == 'retailer' ? 'selected' : '' }}>Retailer</option>
                                    <option value="service" {{ $supplier->supplier_type == 'service' ? 'selected' : '' }}>Service Provider</option>
                                    <option value="importer" {{ $supplier->supplier_type == 'importer' ? 'selected' : '' }}>Importer</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Balance and Due -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="balance" class="form-label">Balance</label>
                                <input type="number" class="form-control" id="balance" name="balance" value="{{ $supplier->balance }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_amount" class="form-label">Due</label>
                                <input type="number" class="form-control" id="due_amount" name="due_amount" value="{{ $supplier->due_amount }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Email and Credit Limit -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $supplier->email }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="credit_limit" class="form-label">Credit Limit</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" value="{{ $supplier->credit_limit }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3">{{ $supplier->address }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3">{{ $supplier->billing_address }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- City, State, Zip Code -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $supplier->city }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="{{ $supplier->state }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" value="{{ $supplier->zip_code }}">
                            </div>
                        </div>
                    </div>

                    <!-- Country and Contact Person -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ $supplier->country }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ $supplier->contact_person }}">
                            </div>
                        </div>
                    </div>

                    <!-- Tax Number and Notes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_number" class="form-label">Tax Number</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" value="{{ $supplier->tax_number }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ $supplier->notes }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active" class="form-label">Status</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Supplier
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Empty column for balance -->
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Supplier
                        </button>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary ml-2">
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
                    Supplier Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Supplier ID</small>
                    <p class="mb-1"><strong>#{{ $supplier->id }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Member Since</small>
                    <p class="mb-1"><strong>{{ $supplier->created_at->format('M d, Y') }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-1"><strong>{{ $supplier->updated_at->format('M d, Y H:i') }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Purchases</small>
                    <p class="mb-1"><strong>{{ $supplier->total_purchases ?: 0 }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Purchase Amount</small>
                    <p class="mb-1"><strong>{{ number_format($supplier->purchases->sum('total_amount'), 2) }}</strong></p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-exclamation-triangle mr-2" style="color:#ffc107;"></i>
                    Important Notes
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Warning</h6>
                    <p class="mb-0">Changing supplier information may affect existing purchase records and reports.</p>
                </div>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Tips</h6>
                    <ul class="mb-0">
                        <li>Phone number must be unique</li>
                        <li>Credit limit affects purchase processing</li>
                        <li>Due amount will be tracked automatically</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_css')
<style>
.form-check-input:checked { background-color: #007bff; border-color: #007bff; }
</style>
@stop
