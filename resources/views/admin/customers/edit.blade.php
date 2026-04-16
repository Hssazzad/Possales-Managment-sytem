@extends('adminlte::page')

@section('title', 'Edit Customer | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-user-edit mr-2" style="color:#ffc107;"></i> Edit Customer
    </h1>
    <div>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-info btn-sm mr-2">
            <i class="fas fa-eye mr-1"></i> View
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
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
                    <i class="fas fa-user-edit mr-2" style="color:#ffc107;"></i>
                    Edit Customer Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.update', $customer) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="{{ $customer->phone }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Party Type and Balance -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="party_type" class="form-label">Party Type</label>
                                <select class="form-control" id="party_type" name="party_type">
                                    <option value="">Select one</option>
                                    <option value="retail" {{ $customer->party_type == 'retail' ? 'selected' : '' }}>Retail</option>
                                    <option value="wholesale" {{ $customer->party_type == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                                    <option value="corporate" {{ $customer->party_type == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                    <option value="individual" {{ $customer->party_type == 'individual' ? 'selected' : '' }}>Individual</option>
                                    <option value="government" {{ $customer->party_type == 'government' ? 'selected' : '' }}>Government</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="balance" class="form-label">Balance</label>
                                <input type="number" class="form-control" id="balance" name="balance" value="{{ $customer->balance }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Due and Email -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_amount" class="form-label">Due</label>
                                <input type="number" class="form-control" id="due_amount" name="due_amount" value="{{ $customer->due_amount }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credit Limit and Address -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="credit_limit" class="form-label">Party Credit Limit</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" value="{{ $customer->credit_limit }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3">{{ $customer->address }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Billing Address -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3">{{ $customer->billing_address }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- City, State, Zip Code -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $customer->city }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="{{ $customer->state }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" value="{{ $customer->zip_code }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Country and Status -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ $customer->country }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active" class="form-label">Status</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $customer->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Customer
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Customer
                        </button>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary ml-2">
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
                    Customer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Customer ID</small>
                    <p class="mb-1"><strong>#{{ $customer->id }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Member Since</small>
                    <p class="mb-1"><strong>{{ $customer->created_at->format('M d, Y') }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-1"><strong>{{ $customer->updated_at->format('M d, Y H:i') }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Sales</small>
                    <p class="mb-1"><strong>{{ $customer->sales->count() }} sales</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Purchases</small>
                    <p class="mb-1"><strong>{{ $customer->formatted_total_purchases }}</strong></p>
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
                    <p class="mb-0">Changing customer information may affect existing sales records and reports.</p>
                </div>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Tips</h6>
                    <ul class="mb-0">
                        <li>Phone number must be unique</li>
                        <li>Credit limit affects payment processing</li>
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
