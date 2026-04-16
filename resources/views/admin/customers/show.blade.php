@extends('adminlte::page')

@section('title', 'Customer Details | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-user mr-2" style="color:#007bff;"></i> Customer Details
    </h1>
    <div>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm mr-2">
            <i class="fas fa-edit mr-1"></i> Edit Customer
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <!-- Customer Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#007bff;"></i>
                    Customer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Customer ID:</strong></td>
                                <td>#{{ $customer->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $customer->phone }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $customer->email ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Party Type:</strong></td>
                                <td>{{ $customer->party_type ? ucfirst($customer->party_type) : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Balance:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $customer->balance > 0 ? 'success' : 'secondary' }}">
                                        {{ $customer->formatted_balance }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Due Amount:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $customer->due_amount > 0 ? 'danger' : 'secondary' }}">
                                        {{ $customer->formatted_due_amount }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Credit Limit:</strong></td>
                                <td>{{ $customer->credit_limit > 0 ? $customer->formatted_credit_limit : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Purchases:</strong></td>
                                <td>{{ $customer->formatted_total_purchases }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
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
                        <p>{{ $customer->address ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Billing Address</h6>
                        <p>{{ $customer->billing_address ?: $customer->address ?: '-' }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>City:</strong> {{ $customer->city ?: '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>State:</strong> {{ $customer->state ?: '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Zip Code:</strong> {{ $customer->zip_code ?: '-' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <strong>Country:</strong> {{ $customer->country ?: '-' }}
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
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                        <i class="fas fa-edit mr-2"></i> Edit Customer
                    </a>
                    <a href="/pos?customer={{ $customer->id }}" class="btn btn-success">
                        <i class="fas fa-shopping-cart mr-2"></i> Create Sale
                    </a>
                    <button onclick="printCustomerDetails()" class="btn btn-info">
                        <i class="fas fa-print mr-2"></i> Print Details
                    </button>
                    <button onclick="deleteCustomer({{ $customer->id }})" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i> Delete Customer
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-chart-bar mr-2" style="color:#28a745;"></i>
                    Customer Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Sales:</span>
                        <strong>{{ $customer->sales->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Last Sale:</span>
                        <strong>{{ $customer->sales->last()?->created_at?->format('M d, Y') ?: 'No sales yet' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Avg Sale:</strong>
                        <strong>{{ $customer->sales->count() > 0 ? 'Tk' . number_format($customer->sales->avg('total_amount'), 2) : 'Tk0.00' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Member Since:</span>
                        <strong>{{ $customer->created_at->format('M d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        fetch(`/admin/customers/${customerId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('customers.index') }}';
            } else {
                alert('Error deleting customer: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting customer. Please try again.');
        });
    }
}

function printCustomerDetails() {
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
