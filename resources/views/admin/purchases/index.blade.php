@extends('adminlte::page')

@section('title', 'Purchase List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-shopping-cart mr-2" style="color:#000000;"></i> Purchase Management
    </h1>
    <div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm mr-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-plus mr-1"></i> New Purchase
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
            Purchase List
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
                    <input type="text" class="form-control" id="purchaseSearch" placeholder="Search purchases by invoice number or supplier..." style="border:2px solid #333333; border-left:none; background:#ffffff; color:#000000;">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-success btn-sm" onclick="exportPurchases()" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>

        <!-- Purchases Table -->
        <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
            <table class="table" style="margin:0; border-collapse:collapse;"
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">ID</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Invoice Number</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Supplier</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Date</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Total Amount</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Payment Method</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Status</th>
                        <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $purchase->id }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <strong style="color:#000000;">{{ $purchase->invoice_number }}</strong>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($purchase->supplier)
                                {{ $purchase->supplier->name }}
                                @if($purchase->supplier->company_name)
                                <br><small style="color:#666666;">{{ $purchase->supplier->company_name }}</small>
                                @endif
                            @else
                                <span style="color:#000000;">-</span>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $purchase->created_at->format('M d, Y') }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#28a745; color:#ffffff;">
                                {{ $purchase->formatted_total_amount }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#17a2b8; color:#ffffff;">
                                {{ ucfirst($purchase->payment_method) }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:{{ $purchase->payment_status == 'paid' ? '#28a745' : '#ffc107' }}; color:{{ $purchase->payment_status == 'paid' ? '#ffffff' : '#212529' }};">
                                {{ ucfirst($purchase->payment_status) }}
                            </span>
                        </td>
                        <td style="padding:12px; border-bottom:1px solid #333333;">
                            <div class="btn-group btn-group-sm" style="gap:4px;">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm" title="View" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deletePurchase({{ $purchase->id }})" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4" style="color:#000000;">
                            <i class="fas fa-shopping-cart fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                            <h5 style="color:#000000;">No purchases found</h5>
                            <p style="color:#666666;">Start by creating your first purchase.</p>
                            <a href="{{ route('purchases.create') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px;">
                                <i class="fas fa-plus mr-2"></i> Create Purchase
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($purchases && $purchases->hasPages())
        <div class="d-flex justify-content-center">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $purchases ? $purchases->total() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Total Purchases</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $purchases ? $purchases->where('payment_status', 'paid')->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Paid Purchases</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ number_format($purchases ? $purchases->sum('total_amount') : 0) }}</h4>
                <small style="color:#000000; font-weight:600;">Total Purchase Amount</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $purchases ? $purchases->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">This Month</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('purchaseSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete purchase
function deletePurchase(purchaseId) {
    if (confirm('Are you sure you want to delete this purchase? This action cannot be undone.')) {
        fetch(`/admin/purchases/${purchaseId}`, {
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
                alert('Error deleting purchase: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting purchase. Please try again.');
        });
    }
}

// Export purchases
function exportPurchases() {
    window.location.href = '/admin/purchases/export';
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
