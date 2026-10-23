@extends('adminlte::page')

@section('title', 'Purchase Return Reports | PosSales')

@section('adminlte_css')
    <!-- আমাদের গ্লোবাল লিস্ট CSS ফাইল -->
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}?v={{ time() }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        
        <!-- Top Toolbar with Filters -->
        <div class="top-toolbar border-bottom pb-3 mb-3">
            <form method="GET" action="{{ route('purchase-return-reports.index') }}" class="d-flex align-items-center w-100 flex-wrap" style="gap: 15px;">
                
                <!-- Date Filter -->
                <div class="d-flex align-items-center bg-white border rounded px-2" style="height: 38px; border-color: #d1d5db;">
                    <i class="fas fa-calendar-alt text-muted mr-2"></i>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-0 bg-transparent text-muted" style="outline: none; font-size: 13px;">
                    <span class="mx-2 text-muted" style="font-size: 13px;">to</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-0 bg-transparent text-muted" style="outline: none; font-size: 13px;">
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <button type="submit" class="btn-export border-0 m-0" style="height: 38px; cursor: pointer;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('purchase-return-reports.index') }}" class="text-muted" title="Reset" style="padding: 8px 12px; background: #f3f4f6; border-radius: 6px;">
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
                        <th>Invoice</th>
                        <th>Supplier Info</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-right">Refund Amount</th>
                        <th class="text-center">Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseReturns as $index => $purchaseReturn)
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $purchaseReturn->created_at->format('d M, Y') }}</div>
                        </td>
                        <td>
                            <span class="badge badge-light border text-muted">{{ $purchaseReturn->invoice_number }}</span>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $purchaseReturn->supplier->name ?? 'Unknown Supplier' }}</div>
                            <div class="text-muted small">ID: {{ $purchaseReturn->supplier_id }}</div>
                        </td>
                        <td class="text-right font-weight-bold">
                            ৳{{ number_format($purchaseReturn->total_amount, 2) }}
                        </td>
                        <td class="text-right text-success font-weight-bold">
                            ৳{{ number_format($purchaseReturn->paid_amount, 2) }}
                        </td>
                        <td class="text-center">
                            @if($purchaseReturn->payment_status == 'pending')
                                <span class="status-badge status-inactive">Pending</span>
                            @else
                                <span class="status-badge status-active">{{ ucfirst($purchaseReturn->payment_status ?? 'N/A') }}</span>
                            @endif
                            <div class="text-muted" style="font-size: 10px; margin-top: 2px;">{{ ucfirst($purchaseReturn->payment_method ?? '') }}</div>
                        </td>
                        <td class="text-muted small">
                            {{ Str::limit($purchaseReturn->notes ?? '-', 30) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-undo-alt fa-3x mb-3 text-light"></i>
                                <h5>No Purchase Returns Found</h5>
                                <p>There are no purchase returns for the selected period.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Supplier Summary & Grand Totals -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 flex-wrap" style="border-top: 1px solid #e5e7eb; gap: 15px;">
            <div class="text-muted text-sm">
                Showing <strong>{{ $totalReturns }}</strong> return records from <strong>{{ $supplierReturns->count() }}</strong> suppliers
            </div>
            <div>
                <span class="badge badge-light border text-warning mr-2" style="font-size: 13px; padding: 6px 10px;">
                    Return Value: <strong>৳{{ number_format($totalReturnAmount, 2) }}</strong>
                </span>
                <span class="badge badge-light border text-success" style="font-size: 13px; padding: 6px 10px;">
                    Refunded: <strong>৳{{ number_format($totalRefundAmount, 2) }}</strong>
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