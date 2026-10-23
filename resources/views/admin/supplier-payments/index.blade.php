@extends('adminlte::page')

@section('title', 'Supplier Payments | ClothBD')

@section('adminlte_css')
<style>
    .payment-list-page {
        background: #f4f7fb;
        min-height: calc(100vh - 80px);
        padding: 20px;
    }

    .payment-list-header {
        align-items: center;
        background: #123866;
        border-bottom: 4px solid #d8a821;
        color: #fff;
        display: flex;
        gap: 14px;
        justify-content: space-between;
        margin-bottom: 16px;
        padding: 16px 18px;
    }

    .payment-list-header h1 {
        font-size: 21px;
        font-weight: 700;
        margin: 0;
    }

    .payment-list-header a {
        align-items: center;
        background: #fff;
        border-radius: 4px;
        color: #123866;
        display: inline-flex;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        min-height: 38px;
        padding: 8px 14px;
        text-decoration: none;
    }

    .supplier-account-wrap,
    .payment-filter,
    .payment-table-wrap {
        background: #fff;
        border: 1px solid #d7dee8;
        border-radius: 6px;
        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.06);
    }

    .supplier-account-wrap {
        margin-bottom: 16px;
        overflow-x: auto;
    }

    .supplier-account-title {
        border-bottom: 1px solid #e5e7eb;
        color: #123866;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        padding: 14px;
    }

    .supplier-account-table {
        margin: 0;
        width: 100%;
    }

    .supplier-account-table th {
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
    }

    .supplier-account-table th,
    .supplier-account-table td {
        border-bottom: 1px solid #e5e7eb;
        padding: 10px 12px;
        vertical-align: middle;
    }

    .supplier-due {
        color: #dc2626;
        font-weight: 700;
    }

    .supplier-balance {
        color: #047857;
        font-weight: 700;
    }

    .pay-link {
        background: #123866;
        border-radius: 4px;
        color: #fff;
        display: inline-flex;
        font-size: 12px;
        font-weight: 700;
        min-height: 32px;
        padding: 7px 10px;
        text-decoration: none;
    }

    .pay-link:hover {
        color: #fff;
    }

    .payment-filter {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 180px 180px auto;
        gap: 12px;
        margin-bottom: 16px;
        padding: 14px;
    }

    .payment-filter label {
        color: #334155;
        display: block;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .payment-filter select,
    .payment-filter input {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        min-height: 38px;
        padding: 7px 9px;
        width: 100%;
    }

    .payment-filter button {
        align-self: end;
        background: #123866;
        border: 0;
        border-radius: 4px;
        color: #fff;
        font-weight: 700;
        min-height: 38px;
        padding: 8px 14px;
    }

    .payment-table {
        margin: 0;
        width: 100%;
    }

    .payment-table th {
        background: #eef2f7;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
    }

    .payment-table th,
    .payment-table td {
        border-bottom: 1px solid #e5e7eb;
        padding: 11px 12px;
        vertical-align: middle;
    }

    .payment-amount {
        color: #047857;
        font-weight: 700;
    }

    .payment-muted {
        color: #64748b;
        font-size: 12px;
    }

    @media (max-width: 900px) {
        .payment-list-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .payment-filter {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop

@section('content')
<div class="payment-list-page">
    <div class="payment-list-header">
        <div>
            <h1><i class="fas fa-money-bill-wave mr-2"></i>Supplier Payments</h1>
            <div class="payment-muted" style="color:#dbe7f5;">Payment history and due adjustment records</div>
        </div>
        <a href="{{ route('supplier-payments.quick-create') }}"><i class="fas fa-plus"></i> New Payment</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="supplier-account-wrap">
        <h2 class="supplier-account-title"><i class="fas fa-industry mr-2"></i>Supplier Account Summary</h2>
        <table class="supplier-account-table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th class="text-right">Total Purchase</th>
                    <th class="text-right">Due</th>
                    <th class="text-right">Balance</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <strong>{{ $supplier->name }}</strong>
                            @if($supplier->company_name)
                                <div class="payment-muted">{{ $supplier->company_name }}</div>
                            @endif
                        </td>
                        <td class="text-right">Tk {{ number_format((float) ($supplier->total_purchases ?? 0), 2) }}</td>
                        <td class="text-right supplier-due">Tk {{ number_format((float) ($supplier->due_amount ?? 0), 2) }}</td>
                        <td class="text-right supplier-balance">Tk {{ number_format((float) ($supplier->balance ?? 0), 2) }}</td>
                        <td class="text-center">
                            <a class="pay-link" href="{{ route('supplier-payments.quick-create', ['supplier_id' => $supplier->id]) }}">
                                Pay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-3">
                            <span class="payment-muted">No suppliers found.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form class="payment-filter" method="GET" action="{{ route('supplier-payments.index') }}">
        <div>
            <label>Supplier</label>
            <select name="supplier_id">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ (string) request('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->company_name ? $supplier->company_name . ' - ' . $supplier->name : $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div>
            <label>To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}">
        </div>
        <button type="submit"><i class="fas fa-filter mr-1"></i> Filter</button>
    </form>

    <div class="payment-table-wrap table-responsive">
        <table class="payment-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Purchase Bill</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                    <th>Note</th>
                    <th class="text-center" style="width:100px;">Invoice</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ optional($payment->payment_date)->format('M d, Y') ?? optional($payment->created_at)->format('M d, Y') }}</td>
                        <td>
                            <strong>{{ $payment->supplier?->name ?? 'Unknown Supplier' }}</strong>
                            @if($payment->supplier?->company_name)
                                <div class="payment-muted">{{ $payment->supplier->company_name }}</div>
                            @endif
                        </td>
                        <td>
                            @if($payment->purchase)
                                {{ $payment->purchase->invoice_number ?? 'Purchase #' . $payment->purchase->id }}
                            @else
                                <span class="payment-muted">General payment</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td class="text-right payment-amount">Tk {{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ $payment->note ?? '-' }}</td>
                        <td class="text-center">
                            <a class="pay-link" style="background:#047857;" href="{{ route('supplier-payments.invoice-pdf', $payment) }}" target="_blank" rel="noopener">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="payment-muted">No supplier payments found.</div>
                            <a href="{{ route('supplier-payments.quick-create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus mr-1"></i> Create Payment
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@stop

@section('js')
@if(session('open_supplier_payment_pdf'))
<script>
window.open(@json(route('supplier-payments.invoice-pdf', (int) session('open_supplier_payment_pdf'))), '_blank', 'noopener');
</script>
@endif
@endsection
