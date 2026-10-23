@extends('adminlte::page')

@section('title', 'Dues Management | PosSales')

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
                <input type="text" id="dueSearch" placeholder="Search by customer name or phone...">
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('sales.index') }}" class="btn-export">
                    <i class="fas fa-shopping-cart mr-1"></i> View All Sales
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th class="text-right">Total Due Amount</th>
                        <th class="text-center">Number of Sales</th>
                        <th>Last Sale Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerDues as $customerName => $customerDue)
                    <tr class="due-row">
                        <td>
                            <div class="font-weight-bold text-dark">{{ $customerDue['customer_name'] }}</div>
                            @if($customerDue['customer_id'])
                                <div class="text-muted small">ID: {{ $customerDue['customer_id'] }}</div>
                            @endif
                        </td>
                        <td class="text-muted">
                            @if($customerDue['customer_phone'])
                                <i class="fas fa-phone-alt mr-1 small"></i>{{ $customerDue['customer_phone'] }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right text-danger font-weight-bold">
                            ৳{{ number_format($customerDue['total_due'], 2) }}
                        </td>
                        <td class="text-center font-weight-bold">
                            {{ $customerDue['total_sales'] }}
                        </td>
                        <td class="text-muted">
                            {{ $customerDue['last_sale_date']->format('M d, Y') }}<br>
                            <small>{{ $customerDue['last_sale_date']->format('h:i A') }}</small>
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-inactive">Unpaid</span>
                        </td>
                        <td class="text-center">
                            <!-- Three Dot Menu -->
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <button class="dropdown-item" onclick="viewCustomerDues('{{ $customerName }}')">
                                        <i class="fas fa-eye text-primary mr-2"></i> View Details
                                    </button>
                                    <a class="dropdown-item" href="{{ route('sales.index') }}?search={{ $customerDue['customer_name'] }}">
                                        <i class="fas fa-shopping-cart text-info mr-2"></i> View Sales
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-hand-holding-usd fa-3x mb-3 text-light"></i>
                                <h5>No Sales Dues Found</h5>
                                <p>All sales have been fully paid. No customers have outstanding dues.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Section (Replacing the big cards) -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                Showing <strong>{{ $customerDues->count() }}</strong> customers with dues
            </div>
            <div>
                <span class="badge badge-light border text-info mr-2" style="font-size: 13px; padding: 6px 10px;">
                    Avg Due/Sale: <strong>৳{{ number_format($salesWithDues->avg('due_amount'), 2) }}</strong>
                </span>
                <span class="badge badge-light border text-danger" style="font-size: 13px; padding: 6px 10px;">
                    Total Due: <strong>৳{{ number_format($salesWithDues->sum('due_amount'), 2) }}</strong>
                </span>
            </div>
        </div>

        <!-- Customer Details Modal -->
        <div class="modal fade" id="customerDuesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border: none; border-radius: 8px;">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title font-weight-bold text-dark">
                            <i class="fas fa-user-circle text-primary mr-2"></i>Customer Due Details
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="customerDuesContent">
                        <!-- Content will be loaded via JavaScript -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('adminlte_js')
<script>
// PHP থেকে ডাটা নিয়ে আসছি
const customerDuesData = @json($customerDues);
const salesWithDuesData = @json($salesWithDues);

// Modal এ ডাটা দেখানোর ফাংশন
function viewCustomerDues(customerName) {
    const customerDue = customerDuesData[customerName];
    if (!customerDue) return;

    let salesHtml = `
        <div class="mb-4 p-3 bg-light rounded">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><span class="text-muted">Name:</span> <strong class="text-dark">${customerDue.customer_name}</strong></p>
                    <p class="mb-0"><span class="text-muted">Phone:</span> <strong>${customerDue.customer_phone || '-'}</strong></p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-1"><span class="text-muted">Total Due:</span> <strong class="text-danger" style="font-size: 1.1rem;">৳${customerDue.total_due.toFixed(2)}</strong></p>
                    <p class="mb-0"><span class="text-muted">Sales Count:</span> <strong>${customerDue.total_sales}</strong></p>
                </div>
            </div>
        </div>

        <h6 class="font-weight-bold mb-3">Sales Details</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm text-center">
                <thead class="bg-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Due</th>
                    </tr>
                </thead>
                <tbody>
    `;

    customerDue.sales.forEach(sale => {
        const date = new Date(sale.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        salesHtml += `
            <tr>
                <td class="font-weight-bold text-primary">${sale.invoice_number}</td>
                <td class="text-muted">${date}</td>
                <td class="text-right">৳${sale.total_amount.toFixed(2)}</td>
                <td class="text-right text-success">৳${sale.paid_amount.toFixed(2)}</td>
                <td class="text-right text-danger font-weight-bold">৳${sale.due_amount.toFixed(2)}</td>
            </tr>
        `;
    });

    salesHtml += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('customerDuesContent').innerHTML = salesHtml;
    $('#customerDuesModal').modal('show');
}

// Client-side Search Functionality
document.getElementById('dueSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.due-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@stop