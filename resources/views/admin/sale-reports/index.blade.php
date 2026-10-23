@extends('adminlte::page')

@section('title', 'Sales Report | POS')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">
        <i class="fas fa-chart-line mr-2"></i> Sales Report
    </h1>
    <span class="text-muted">
        <i class="far fa-calendar-alt mr-1"></i> {{ now()->format('d M Y') }}
    </span>
</div>
@stop

@section('content')
<div class="container-fluid">

    <!-- Filter Card -->
    <div class="card card-primary card-outline mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i> Filters</h3>
            <div class="card-tools">
                <a href="{{ route('sale-reports.index') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sale-reports.index') }}" id="filterForm">
                <div class="row">
                    <!-- Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="date_range">Date Range</label>
                        <select name="date_range" id="date_range" class="form-control" onchange="handleDateRangeChange()">
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last_7_days" {{ $dateRange == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_30_days" {{ $dateRange == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="col-md-3 mb-3" id="startDateContainer" style="{{ $dateRange != 'custom' ? 'display:none;' : '' }}">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                               value="{{ $startDate }}" onchange="setCustomRange()">
                    </div>

                    <!-- End Date -->
                    <div class="col-md-3 mb-3" id="endDateContainer" style="{{ $dateRange != 'custom' ? 'display:none;' : '' }}">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                               value="{{ $endDate }}" onchange="setCustomRange()">
                    </div>

                    <!-- Customer Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="customer_id">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} {{ $customer->phone ? '- '.$customer->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Status -->
                    <div class="col-md-3 mb-3">
                        <label for="payment_status">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="due" {{ request('payment_status') == 'due' ? 'selected' : '' }}>Due</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['total_sales']) }}</h3>
                    <p>Total Sales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>৳{{ number_format($summary['total_amount'], 2) }}</h3>
                    <p>Total Amount</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>৳{{ number_format($summary['total_discount'], 2) }}</h3>
                    <p>Total Discount</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percent"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>৳{{ number_format($summary['total_paid'], 2) }}</h3>
                    <p>Total Paid</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>৳{{ number_format($summary['total_due'], 2) }}</h3>
                    <p>Total Due</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>৳{{ number_format($summary['total_tax'], 2) }}</h3>
                    <p>Total VAT/Tax</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="fas fa-list mr-2"></i> Sales Details
            </h4>
            <small class="text-muted">
                Showing {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
            </small>
        </div>
        <div>
            <a href="{{ route('sale-reports.export', request()->all()) }}" class="btn btn-success mr-2">
                <i class="fas fa-file-excel mr-2"></i> Export Excel
            </a>
            <button onclick="window.print()" class="btn btn-info">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items (with Variations)</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Due</th>
                            <th>Status</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>
                                    <strong>{{ $sale->invoice_number }}</strong>
                                </td>
                                <td>{{ $sale->sale_date->format('d M Y, h:i A') }}</td>
                                <td>
                                    {{ $sale->customer_name ?: 'Walk-in' }}<br>
                                    <small class="text-muted">{{ $sale->customer_phone }}</small>
                                </td>
                                <td>
                                    @foreach($sale->items as $item)
                                        <div class="mb-1">
                                            <strong>{{ $item->product_name }}</strong>
                                            @if($item->variation)
                                                <span class="badge badge-info ml-1">{{ $item->variation->variation_value }}</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                {{ $item->quantity }} x ৳{{ number_format($item->unit_price, 2) }} = ৳{{ number_format($item->total_price, 2) }}
                                            </small>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="text-right">
                                    <strong>৳{{ number_format($sale->total_amount, 2) }}</strong>
                                    @if($sale->discount_amount > 0)
                                        <br><small class="text-success">-৳{{ number_format($sale->discount_amount, 2) }} disc</small>
                                    @endif
                                </td>
                                <td class="text-right text-success">৳{{ number_format($sale->paid_amount, 2) }}</td>
                                <td class="text-right {{ $sale->due_amount > 0 ? 'text-danger' : '' }}">
                                    ৳{{ number_format($sale->due_amount, 2) }}
                                </td>
                                <td>
                                    @if($sale->payment_status == 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif($sale->payment_status == 'partial')
                                        <span class="badge badge-warning">Partial</span>
                                    @else
                                        <span class="badge badge-danger">Due</span>
                                    @endif
                                </td>
                                <td>{{ $sale->payment_method }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No sales found for the selected date range</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sales->hasPages())
            <div class="card-footer">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>
@stop

@section('css')
<style>
.small-box h3 {
    font-size: 1.5rem;
    font-weight: bold;
}
.badge {
    font-size: 0.85rem;
}
.table th {
    font-weight: 600;
}
</style>
@stop

@section('js')
<script>
function handleDateRangeChange() {
    var range = document.getElementById('date_range').value;
    if (range === 'custom') {
        document.getElementById('startDateContainer').style.display = 'block';
        document.getElementById('endDateContainer').style.display = 'block';
    } else {
        document.getElementById('startDateContainer').style.display = 'none';
        document.getElementById('endDateContainer').style.display = 'none';
        document.getElementById('filterForm').submit();
    }
}

function setCustomRange() {
    document.getElementById('date_range').value = 'custom';
}
</script>
@stop
