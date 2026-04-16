@extends('adminlte::page')

@section('title', 'Returns List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-undo mr-2" style="color:#000000;"></i> Returns Management
    </h1>
    <div>
        <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm mr-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-plus mr-1"></i> New Return
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
            Returns List
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
                    <input type="text" class="form-control" id="returnSearch" placeholder="Search returns by invoice or customer..." style="border:2px solid #333333; border-left:none; background:#ffffff; color:#000000;">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-success btn-sm" onclick="exportReturns()" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>

        <!-- Returns Table -->
        <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
            <table class="table" style="margin:0; border-collapse:collapse;"
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">ID</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Sale Invoice</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Customer</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Product</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Quantity</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Refund Amount</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Status</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Date</th>
                        <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $return->id }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($return->sale && $return->sale->invoice_number)
                                <strong style="color:#000000;">{{ $return->sale->invoice_number }}</strong>
                            @else
                                <span style="color:#666666;">Sale #{{ $return->sale_id ?: 'N/A' }}</span>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($return->customer)
                                <span style="color:#000000;">{{ $return->customer->name }}</span>
                            @else
                                <span style="color:#000000;">-</span>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($return->product)
                                <span style="color:#000000;">{{ $return->product->name }}</span>
                            @else
                                <span style="color:#000000;">-</span>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $return->quantity }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#ffc107; color:#212529;">
                                {{ $return->formatted_refund_amount }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($return->status == 'completed')
                                <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#28a745; color:#ffffff;">
                            @elseif($return->status == 'pending')
                                <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#ffc107; color:#212529;">
                            @elseif($return->status == 'approved')
                                <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#17a2b8; color:#ffffff;">
                            @else
                                <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#dc3545; color:#ffffff;">
                            @endif
                                {{ ucfirst($return->status) }}
                            </span>
                                {{ ucfirst($return->status) }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $return->return_date->format('M d, Y') }}</td>
                        <td style="padding:12px; border-bottom:1px solid #333333;">
                            <div class="btn-group btn-group-sm" style="gap:4px;">
                                <a href="{{ route('returns.show', $return->id) }}" class="btn btn-sm" title="View" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('returns.edit', $return->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteReturn({{ $return->id }})" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4" style="color:#000000;">
                            <i class="fas fa-undo fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                            <h5 style="color:#000000;">No returns found</h5>
                            <p style="color:#666666;">Start by creating your first return.</p>
                            <a href="{{ route('returns.create') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px;">
                                <i class="fas fa-plus mr-2"></i> Create Return
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($returns && $returns->hasPages())
        <div class="d-flex justify-content-center">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $returns ? $returns->total() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Total Returns</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $returns ? $returns->where('status', 'pending')->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Pending Returns</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ number_format($returns ? $returns->sum('refund_amount') : 0) }}</h4>
                <small style="color:#000000; font-weight:600;">Total Refunded</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $returns ? $returns->where('status', 'completed')->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Completed</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('returnSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete return
function deleteReturn(returnId) {
    if (confirm('Are you sure you want to delete this return? This action cannot be undone.')) {
        fetch(`/admin/returns/${returnId}`, {
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
                alert('Error deleting return: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting return. Please try again.');
        });
    }
}

// Export returns
function exportReturns() {
    window.location.href = '/admin/returns/export';
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; }
.badge-info { background-color: #17a2b8; }
.badge-danger { background-color: #dc3545; }
.table th { background-color: #f8f9fa; }
.btn-group-sm > .btn { padding: 0.25rem 0.5rem; }
</style>
@stop
