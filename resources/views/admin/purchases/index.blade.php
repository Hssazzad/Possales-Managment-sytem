@extends('adminlte::page')

@section('title', 'Purchase List | PosSales')

@section('adminlte_css')
    <!-- আমাদের গ্লোবাল CSS ফাইল -->
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        
        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <!-- Search Box -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="purchaseSearch" placeholder="Search by invoice or supplier...">
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="exportPurchases()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <a href="{{ route('purchases.create') }}" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> New Purchase
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Due</th>
                        <th class="text-center">Payment</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr class="purchase-row">
                        <td class="font-weight-bold text-primary">{{ $purchase->invoice_number }}</td>
                        <td>
                            @if($purchase->supplier)
                                <div class="font-weight-bold">{{ $purchase->supplier->name }}</div>
                                @if($purchase->supplier->company_name)
                                    <div class="text-muted small"><i class="fas fa-building mr-1"></i>{{ $purchase->supplier->company_name }}</div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark">{{ $purchase->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="text-right font-weight-bold">৳{{ number_format($purchase->total_amount, 2) }}</td>
                        <td class="text-right text-success font-weight-bold">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                        <td class="text-right font-weight-bold {{ $purchase->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ৳{{ number_format($purchase->due_amount, 2) }}
                        </td>
                        <td class="text-center">
                            @if($purchase->payment_method == 'cash')
                                <span class="status-badge status-active">Cash</span>
                            @else
                                <span class="status-badge" style="background-color: #e0f2fe; color: #0284c7;">{{ ucfirst($purchase->payment_method) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($purchase->payment_status == 'paid')
                                <span class="status-badge status-active">Paid</span>
                            @else
                                <span class="status-badge status-inactive">{{ ucfirst($purchase->payment_status) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <!-- Three Dot Menu -->
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <a class="dropdown-item" href="{{ route('purchases.show', $purchase->id) }}">
                                        <i class="fas fa-eye text-primary mr-2"></i> View Details
                                    </a>
                                    <a class="dropdown-item" href="{{ route('purchases.edit', $purchase->id) }}">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit Purchase
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deletePurchase({{ $purchase->id }})">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-shopping-cart fa-3x mb-3 text-light"></i>
                                <h5>No Purchases Found</h5>
                                <p>Start by creating your first purchase order.</p>
                                <a href="{{ route('purchases.create') }}" class="btn-add-new mt-2 d-inline-block">
                                    <i class="fas fa-plus mr-1"></i> Create Purchase
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Summary -->
        @if($purchases && $purchases->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                Showing {{ $purchases->firstItem() }} to {{ $purchases->lastItem() }} of {{ $purchases->total() }} purchases
                
                <!-- আমরা বড় স্ট্যাট বক্সগুলো বাদ দিয়ে এখানে ছোট ব্যাজ হিসেবে দেখিয়ে দিচ্ছি -->
                <span class="ml-3 badge badge-light border text-primary">Total: ৳{{ number_format($purchases->sum('total_amount'), 2) }}</span>
                <span class="ml-2 badge badge-light border text-danger">Due: ৳{{ number_format($purchases->sum('due_amount'), 2) }}</span>
            </div>
            <div>
                {{ $purchases->links() }}
            </div>
        </div>
        @endif

    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality (Client-side)
document.getElementById('purchaseSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.purchase-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
});

// Export functionality
function exportPurchases() {
    window.location.href = '/admin/purchases/export';
}

// Delete functionality
function deletePurchase(purchaseId) {
    if (confirm('⚠️ Are you sure you want to delete this purchase?\n\nThis action will:\n✓ Delete the purchase record\n✓ Restore inventory quantities\n✓ Reverse payment records\n\nThis cannot be undone!')) {
        fetch(`/admin/purchases/${purchaseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Purchase deleted successfully. Inventory restored.');
                location.reload();
            } else {
                alert('❌ Error: ' + (data.message || 'Could not delete purchase'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error deleting purchase. Please try again.');
        });
    }
}
</script>
@stop