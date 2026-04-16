@extends('adminlte::page')

@section('title', 'Supplier Details | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-truck mr-2" style="color:#007bff;"></i> Supplier Details
    </h1>
    <div>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm mr-2">
            <i class="fas fa-edit mr-1"></i> Edit Supplier
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <!-- Supplier Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#007bff;"></i>
                    Supplier Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Supplier ID:</strong></td>
                                <td>#{{ $supplier->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $supplier->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Company Name:</strong></td>
                                <td>{{ $supplier->company_name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $supplier->phone }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $supplier->email ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Supplier Type:</strong></td>
                                <td>{{ $supplier->supplier_type ? ucfirst($supplier->supplier_type) : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tax Number:</strong></td>
                                <td>{{ $supplier->tax_number ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Balance:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $supplier->balance > 0 ? 'success' : 'secondary' }}">
                                        {{ $supplier->formatted_balance }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Due Amount:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $supplier->due_amount > 0 ? 'danger' : 'secondary' }}">
                                        {{ $supplier->formatted_due_amount }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Credit Limit:</strong></td>
                                <td>{{ $supplier->credit_limit > 0 ? $supplier->formatted_credit_limit : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Purchases:</strong></td>
                                <td>{{ $supplier->formatted_total_purchases }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Member Since:</strong></td>
                                <td>{{ $supplier->created_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-map-marker-alt mr-2" style="color:#17a2b8;"></i>
                    Address Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Address</h6>
                        <p>{{ $supplier->address ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Billing Address</h6>
                        <p>{{ $supplier->billing_address ?: $supplier->address ?: '-' }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>City:</strong> {{ $supplier->city ?: '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>State:</strong> {{ $supplier->state ?: '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Zip Code:</strong> {{ $supplier->zip_code ?: '-' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <strong>Country:</strong> {{ $supplier->country ?: '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-bolt mr-2" style="color:#ffc107;"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
                        <i class="fas fa-edit mr-2"></i> Edit Supplier
                    </a>
                    <button onclick="printSupplierDetails()" class="btn btn-info">
                        <i class="fas fa-print mr-2"></i> Print Details
                    </button>
                    <button onclick="deleteSupplier({{ $supplier->id }})" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i> Delete Supplier
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-chart-bar mr-2" style="color:#28a745;"></i>
                    Supplier Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Purchases</small>
                    <p class="mb-1"><strong>{{ $supplier->total_purchases ?: 0 }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Purchase Amount</small>
                    <p class="mb-1"><strong>{{ number_format($supplier->purchases ? $supplier->purchases->sum('total_amount') : 0) }}</strong></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Member Since</small>
                    <p class="mb-1"><strong>{{ $supplier->created_at->format('M d, Y') }}</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
        fetch(`/admin/suppliers/${supplierId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("suppliers.index") }}';
            } else {
                alert('Error deleting supplier: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting supplier. Please try again.');
        });
    }
}

function printSupplierDetails() {
    window.print();
}
</script>
@stop

@section('adminlte_css')
<style>
.badge-success { background-color: #28a745; }
.badge-danger { background-color: #dc3545; }
.badge-secondary { background-color: #6c757d; }
.table-borderless td { padding: 0.5rem 0; }
@media print {
    .no-print { display: none !important; }
    .card { border: 1px solid #000 !important; }
}
</style>
@stop
