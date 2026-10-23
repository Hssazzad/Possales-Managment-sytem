@extends('adminlte::page')

@section('title')
    Income Dashboard | {{ config('app.name') }}
@stop

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Today's Sale Income</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($todaySalesIncome, 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-shopping-cart text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Today's Due Collected</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($todayDueCollected, 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-hand-holding-usd text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Cash in Hand</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($totalCashInHand, 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-wallet text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <!-- Date Filter -->
            <div class="d-flex align-items-center" style="gap: 10px;">
                <div class="search-box" style="width: auto;">
                    <i class="fas fa-calendar"></i>
                    <input type="date" id="dateFilter" value="{{ $dateFilter }}" style="width: 200px;">
                </div>
                <button onclick="applyDateFilter()" class="btn-add-new" style="padding: 10px 20px;">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="exportIncome()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button onclick="window.print()" class="btn-export">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        <!-- Filtered Date Summary -->
        <div class="alert alert-info mb-3 d-flex justify-content-between align-items-center">
            <span><strong>Showing income for: {{ date('F d, Y', strtotime($dateFilter)) }}</strong></span>
            <span class="badge badge-primary" style="font-size: 14px; padding: 8px 12px;">Total: ৳{{ number_format($filteredTotal, 2) }}</span>
        </div>

        <!-- Income Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Payment Method</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allIncomes as $income)
                    <tr class="income-row" data-type="{{ $income['type'] }}">
                        <td>
                            <div class="font-weight-bold">{{ date('M d, Y', strtotime($income['date'])) }}</div>
                            <div class="text-muted small">{{ date('h:i A', strtotime($income['date'])) }}</div>
                        </td>
                        <td>
                            @if($income['type'] == 'sales')
                                <span class="status-badge" style="background-color: #fff3e0; color: #e65100;">
                                    <i class="fas fa-shopping-cart mr-1"></i>{{ $income['source'] }}
                                </span>
                            @else
                                <span class="status-badge" style="background-color: #e8f5e9; color: #2e7d32;">
                                    <i class="fas fa-hand-holding-usd mr-1"></i>{{ $income['source'] }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $income['reference'] }}</div>
                        </td>
                        <td class="text-right font-weight-bold" style="font-size: 1.1rem; color: #2e7d32;">
                            ৳{{ number_format($income['amount'], 2) }}
                        </td>
                        <td class="text-center">
                            <span class="variation-tag">
                                <i class="fas fa-{{ $income['payment_method'] == 'cash' ? 'money-bill-wave' : ($income['payment_method'] == 'bank' ? 'university' : 'mobile-alt') }} mr-1"></i>
                                {{ ucfirst($income['payment_method']) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($income['type'] == 'sales' && isset($income['sale_id']))
                                <a href="{{ route('sales.show', $income['sale_id']) }}" class="btn btn-sm btn-link text-primary" title="View Invoice">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3 text-light"></i>
                                <h5>No Income Records Found</h5>
                                <p>No income recorded for {{ date('F d, Y', strtotime($dateFilter)) }}.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Footer -->
        @if($allIncomes->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                <span class="mr-3"><i class="fas fa-shopping-cart text-warning mr-1"></i> Sales: <strong>৳{{ number_format($filteredSalesIncome, 2) }}</strong></span>
                <span class="mr-3"><i class="fas fa-hand-holding-usd text-success mr-1"></i> Due Collected: <strong>৳{{ number_format($filteredDueCollected, 2) }}</strong></span>
                <span><i class="fas fa-calculator text-primary mr-1"></i> Total: <strong>৳{{ number_format($filteredTotal, 2) }}</strong></span>
            </div>
            <div class="text-muted text-sm">
                {{ $allIncomes->count() }} transaction(s)
            </div>
        </div>
        @endif

    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Apply date filter
function applyDateFilter() {
    const date = document.getElementById('dateFilter').value;
    if (date) {
        window.location.href = '{{ route('income.index') }}?date=' + date;
    }
}

// Search functionality
function filterIncome() {
    const searchTerm = document.getElementById('incomeSearch').value.toLowerCase();
    document.querySelectorAll('.income-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
}

// Export income
function exportIncome() {
    const date = document.getElementById('dateFilter').value;
    window.location.href = '/admin/income/export?date=' + date;
}

// Handle Enter key on date filter
document.getElementById('dateFilter').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyDateFilter();
    }
});
</script>
@stop

