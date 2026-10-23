@extends('adminlte::page')

@section('title', 'Supplier Dues | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        
        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="dueSearch" placeholder="Search by supplier or company...">
            </div>
            <div class="action-buttons">
                <a href="{{ route('purchases.index') }}" class="btn-export">
                    <i class="fas fa-truck mr-1"></i> View Purchases
                </a>
                <a href="{{ route('pos.index') }}" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> New Purchase
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Supplier Info</th>
                        <th>Phone</th>
                        <th class="text-right">Total Due Amount</th>
                        <th class="text-center">Purchases</th>
                        <th>Last Purchase</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplierDues as $supplierName => $supplierDue)
                    <tr class="due-row">
                        <td>
                            <div class="font-weight-bold text-dark">{{ $supplierDue['supplier_name'] }}</div>
                            @if($supplierDue['supplier_company'])
                                <div class="text-muted small"><i class="fas fa-building mr-1"></i>{{ $supplierDue['supplier_company'] }}</div>
                            @endif
                        </td>
                        <td class="text-muted">
                            {!! $supplierDue['supplier_phone'] ? '<i class="fas fa-phone-alt mr-1 small"></i>'.$supplierDue['supplier_phone'] : '-' !!}
                        </td>
                        <td class="text-right text-danger font-weight-bold">
                            ৳{{ number_format($supplierDue['total_due'], 2) }}
                        </td>
                        <td class="text-center font-weight-bold">{{ $supplierDue['total_purchases'] }}</td>
                        <td class="text-muted">
                            {{ $supplierDue['last_purchase_date']->format('M d, Y') }}<br>
                            <small>{{ $supplierDue['last_purchase_date']->format('h:i A') }}</small>
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-inactive">Unpaid</span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <button class="dropdown-item" onclick="viewSupplierDues('{{ $supplierName }}')">
                                        <i class="fas fa-eye text-primary mr-2"></i> View Details
                                    </button>
                                    <a class="dropdown-item" href="{{ route('purchases.index') }}?search={{ $supplierDue['supplier_name'] }}">
                                        <i class="fas fa-shopping-cart text-info mr-2"></i> View Purchases
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-truck fa-3x mb-3 text-light"></i>
                                <h5>No Supplier Dues Found</h5>
                                <p>All purchases have been fully paid. No suppliers have outstanding dues.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                Showing <strong>{{ $supplierDues->count() }}</strong> suppliers with dues
            </div>
            <div>
                <span class="badge badge-light border text-info mr-2" style="font-size: 13px; padding: 6px 10px;">
                    Avg Due: <strong>৳{{ number_format($purchasesWithDues->avg('due_amount'), 2) }}</strong>
                </span>
                <span class="badge badge-light border text-danger" style="font-size: 13px; padding: 6px 10px;">
                    Total Due: <strong>৳{{ number_format($purchasesWithDues->sum('due_amount'), 2) }}</strong>
                </span>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="supplierDuesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border: none; border-radius: 8px;">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-truck text-primary mr-2"></i>Supplier Due Details</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" id="supplierDuesContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
const supplierDuesData = @json($supplierDues);

function viewSupplierDues(supplierName) {
    const supplierDue = supplierDuesData[supplierName];
    if (!supplierDue) return;

    let purchasesHtml = `
        <div class="mb-4 p-3 bg-light rounded">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><span class="text-muted">Supplier:</span> <strong class="text-dark">${supplierDue.supplier_name}</strong></p>
                    <p class="mb-0"><span class="text-muted">Company:</span> <strong>${supplierDue.supplier_company || '-'}</strong></p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-1"><span class="text-muted">Total Due:</span> <strong class="text-danger" style="font-size: 1.1rem;">৳${supplierDue.total_due.toFixed(2)}</strong></p>
                    <p class="mb-0"><span class="text-muted">Purchases Count:</span> <strong>${supplierDue.total_purchases}</strong></p>
                </div>
            </div>
        </div>
        <h6 class="font-weight-bold mb-3">Purchase Details</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm text-center">
                <thead class="bg-light">
                    <tr><th>Invoice #</th><th>Date</th><th class="text-right">Total</th><th class="text-right">Paid</th><th class="text-right">Due</th></tr>
                </thead>
                <tbody>
    `;

    supplierDue.purchases.forEach(purchase => {
        const date = new Date(purchase.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        purchasesHtml += `
            <tr>
                <td class="font-weight-bold text-primary">${purchase.invoice_number}</td>
                <td class="text-muted">${date}</td>
                <td class="text-right">৳${purchase.total_amount.toFixed(2)}</td>
                <td class="text-right text-success">৳${purchase.paid_amount.toFixed(2)}</td>
                <td class="text-right text-danger font-weight-bold">৳${purchase.due_amount.toFixed(2)}</td>
            </tr>
        `;
    });

    purchasesHtml += `</tbody></table></div>`;
    document.getElementById('supplierDuesContent').innerHTML = purchasesHtml;
    $('#supplierDuesModal').modal('show');
}

document.getElementById('dueSearch')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.due-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
});
</script>
@stop