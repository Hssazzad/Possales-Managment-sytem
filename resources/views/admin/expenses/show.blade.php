@extends('adminlte::page')

@section('title')
    Expense Details | {{ config('app.name') }}
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-eye mr-2" style="color:#dc2626;"></i> Expense Details
    </h1>
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Expenses
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-receipt mr-2" style="color:#dc2626;"></i> {{ $expense->description }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:140px;">Description</th>
                                <td>{{ $expense->description }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td class="font-weight-bold" style="font-size:1.1rem;">৳{{ number_format($expense->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td><span class="badge badge-warning">{{ $expense->category }}</span></td>
                            </tr>
                            <tr>
                                <th>Expense Date</th>
                                <td>{{ $expense->formatted_date }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:140px;">Payment Method</th>
                                <td>{{ $expense->payment_method ?? 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <th>Receipt Number</th>
                                <td>{{ $expense->receipt_number ?? 'No receipt' }}</td>
                            </tr>
                            <tr>
                                <th>Added By</th>
                                <td>{{ $expense->user->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $expense->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($expense->notes)
                <div class="form-group mt-3">
                    <label class="form-label font-weight-bold">Notes</label>
                    <div class="p-3 bg-light rounded">{{ $expense->notes }}</div>
                </div>
                @endif

                <div class="form-group mt-4">
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit mr-2"></i> Edit Expense
                    </a>
                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this expense?')">
                            <i class="fas fa-trash mr-2"></i> Delete Expense
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-bolt mr-2" style="color:#dc2626;"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column" style="gap:8px;">
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-edit mr-2"></i> Edit Expense
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-print mr-2"></i> Print Details
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-calculator mr-2" style="color:#f59e0b;"></i> Expense Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Amount:</span>
                        <strong>৳{{ number_format($expense->amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Category:</span>
                        <strong>{{ $expense->category }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-0">
                        <span>Date:</span>
                        <strong>{{ $expense->formatted_date }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_css')
<style>
@media print {
    .btn { display: none !important; }
    .card { border: 1px solid #dee2e6 !important; }
}
</style>
@stop
