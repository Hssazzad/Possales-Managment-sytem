@extends('adminlte::page')

@section('title', 'Customers | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-users mr-2" style="color:#000000;"></i> Customer Management
    </h1>
    <div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm mr-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-plus mr-1"></i> Create Customer
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
    <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
        <h5 style="margin:0; font-weight:600; color:#000000;">
            <i class="fas fa-list mr-2" style="color:#000000;"></i>
            Customer List
        </h5>
    </div>
    <div class="card-body" style="padding:20px;">
        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <div class="input-group-prepend" style="background:#ffffff; border:2px solid #333333; border-right:none;">
                        <span class="input-group-text" style="background:#ffffff; border:none; color:#000000;">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control" id="customerSearch" placeholder="Search customers by name, phone, or email..." style="border:2px solid #333333; border-left:none; background:#ffffff; color:#000000;">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-success btn-sm" onclick="exportCustomers()" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
            <table class="table" style="margin:0; border-collapse:collapse;"
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">ID</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Name</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Phone</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Email</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Party Type</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Balance</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Due Amount</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Credit Limit</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">City</th>
                        <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $customer->id }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <strong style="color:#000000;">{{ $customer->name }}</strong>
                            @if($customer->party_type)
                            <br><small style="color:#666666;">{{ ucfirst($customer->party_type) }}</small>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $customer->phone }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $customer->email ?: '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $customer->party_type ? ucfirst($customer->party_type) : '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:{{ $customer->balance > 0 ? '#28a745' : '#6c757d' }}; color:#ffffff;">
                                 {{ $customer->formatted_balance }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:{{ $customer->due_amount > 0 ? '#dc3545' : '#6c757d' }}; color:#ffffff;">
                                {{ $customer->formatted_due_amount }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $customer->credit_limit > 0 ? $customer->formatted_credit_limit : '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $customer->city ?: '-' }}</td>
                        <td style="padding:12px; border-bottom:1px solid #333333;">
                            <div class="btn-group btn-group-sm" style="gap:4px;">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm" title="View" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteCustomer({{ $customer->id }})" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4" style="color:#000000;">
                            <i class="fas fa-users fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                            <h5 style="color:#000000;">No customers found</h5>
                            <p style="color:#666666;">Start by creating your first customer.</p>
                            <a href="{{ route('customers.create') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px;">
                                <i class="fas fa-plus mr-2"></i> Create Customer
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="d-flex justify-content-center">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $customers->total() }}</h4>
                <small style="color:#000000; font-weight:600;">Total Customers</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $customers->where('due_amount', '>', 0)->count() }}</h4>
                <small style="color:#000000; font-weight:600;">Customers with Due</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ number_format($customers->sum('due_amount'), 2) }}</h4>
                <small style="color:#000000; font-weight:600;">Total Due Amount</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ number_format($customers->sum('total_purchases'), 2) }}</h4>
                <small style="color:#000000; font-weight:600;">Total Purchases</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('customerSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete customer
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
                location.reload();
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

// Export customers
function exportCustomers() {
    window.location.href = '/admin/customers/export';
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
</style>
@stop
