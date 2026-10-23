@extends('adminlte::page')

@section('title', 'Sale Return Reports | PosSales')

@section('adminlte_css')
    <!-- আমাদের গ্লোবাল লিস্ট CSS ফাইল -->
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}?v={{ time() }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        
        <!-- Top Toolbar with Filters -->
        <div class="top-toolbar border-bottom pb-3 mb-3">
            <form method="GET" action="{{ route('sale-return-reports.index') }}" class="d-flex align-items-center w-100 flex-wrap" style="gap: 15px;">
                
                <!-- Date Filter -->
                <div class="d-flex align-items-center bg-white border rounded px-2" style="height: 38px; border-color: #d1d5db;">
                    <i class="fas fa-calendar-alt text-muted mr-2"></i>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-0 bg-transparent text-muted" style="outline: none; font-size: 13px;">
                    <span class="mx-2 text-muted" style="font-size: 13px;">to</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-0 bg-transparent text-muted" style="outline: none; font-size: 13px;">
                </div>

                <!-- Status Filter -->
                <div class="search-box m-0" style="width: auto;">
                    <select name="status" class="border-0 bg-transparent text-muted" style="outline: none; font-size: 13px; width: 130px;">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <button type="submit" class="btn-export border-0 m-0" style="height: 38px; cursor: pointer;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('sale-return-reports.index') }}" class="text-muted" title="Reset" style="padding: 8px 12px; background: #f3f4f6; border-radius: 6px;">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Return Date</th>
                        <th>Customer Info</th>
                        <th>Product Details</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Refund Amount</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnItems as $index => $returnItem)
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $returnItem->return_date->format('d M, Y') }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $returnItem->customer->name ?? 'Walk-in Customer' }}</div>
                            <div class="text-muted small">ID: {{ $returnItem->customer_id ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $returnItem->product->name ?? 'N/A' }}</div>
                            @if($returnItem->sale)
                                <div class="text-muted small">Sale Ref: {{ $returnItem->sale->invoice_number }}</div>
                            @endif
                        </td>
                        <td class="text-center font-weight-bold">{{ $returnItem->quantity }}</td>
                        <td class="text-right text-danger font-weight-bold">
                            ৳{{ number_format($returnItem->refund_amount, 2) }}
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ ucfirst($returnItem->refund_method ?? 'N/A') }}</span>
                        </td>
                        <td class="text-center">
                            @if($returnItem->status == 'pending')
                                <span class="status-badge status-inactive">Pending</span>
                            @elseif($returnItem->status == 'completed' || $returnItem->status == 'approved')
                                <span class="status-badge status-active">{{ ucfirst($returnItem->status) }}</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($returnItem->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-undo fa-3x mb-3 text-light"></i>
                                <h5>No Sale Returns Found</h5>
                                <p>There are no sale returns for the selected period.</p>
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
                Showing <strong>{{ $totalReturns }}</strong> return records
            </div>
            <div>
                <span class="badge badge-light border text-info mr-2" style="font-size: 13px; padding: 6px 10px;">
                    Total Quantity: <strong>{{ $totalQuantity }}</strong>
                </span>
                <span class="badge badge-light border text-danger" style="font-size: 13px; padding: 6px 10px;">
                    Total Refund: <strong>৳{{ number_format($totalRefundAmount, 2) }}</strong>
                </span>
            </div>
        </div>

    </div>
</div>
@stop

@section('adminlte_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (!startDate.value && !endDate.value) {
        const today = new Date().toISOString().split('T')[0];
        startDate.value = today;
        endDate.value = today;
    }
});
</script>
@stop