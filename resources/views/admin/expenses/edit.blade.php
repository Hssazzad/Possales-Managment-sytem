@extends('adminlte::page')

@section('title')
    Edit Expense | {{ config('app.name') }}
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-edit mr-2" style="color:#dc2626;"></i> Edit Expense
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
                    <i class="fas fa-edit mr-2" style="color:#dc2626;"></i> Edit Expense: {{ $expense->description }}
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('expenses.update', $expense->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description" class="form-label">Description *</label>
                                <input type="text" id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Enter expense description" required value="{{ old('description', $expense->description) }}">
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount" class="form-label">Amount *</label>
                                <input type="number" id="amount" name="amount" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00" required value="{{ old('amount', $expense->amount) }}">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category *</label>
                                <select id="category" name="category" class="form-control @error('category') is-invalid @enderror" required>
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ old('category', $expense->category) == $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_date" class="form-label">Expense Date *</label>
                                <input type="date" id="expense_date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select id="payment_method" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                                    <option value="">Select payment method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method }}" {{ old('payment_method', $expense->payment_method) == $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="receipt_number" class="form-label">Receipt Number</label>
                                <input type="text" id="receipt_number" name="receipt_number" class="form-control @error('receipt_number') is-invalid @enderror" placeholder="Enter receipt number (optional)" value="{{ old('receipt_number', $expense->receipt_number) }}">
                                @error('receipt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="Enter additional notes (optional)">{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Expense
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#dc2626;"></i> Current Details
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <div class="mb-2"><strong>Amount:</strong> ৳{{ number_format($expense->amount, 2) }}</div>
                    <div class="mb-2"><strong>Category:</strong> {{ $expense->category }}</div>
                    <div class="mb-2"><strong>Date:</strong> {{ $expense->formatted_date }}</div>
                    <div class="mb-0"><strong>Added:</strong> {{ $expense->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-calculator mr-2" style="color:#f59e0b;"></i> Quick Amounts
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <button type="button" onclick="setAmount(100)" class="btn btn-outline-secondary btn-sm" style="flex:1;">৳100</button>
                    <button type="button" onclick="setAmount(500)" class="btn btn-outline-secondary btn-sm" style="flex:1;">৳500</button>
                    <button type="button" onclick="setAmount(1000)" class="btn btn-outline-secondary btn-sm" style="flex:1;">৳1000</button>
                    <button type="button" onclick="setAmount(2000)" class="btn btn-outline-secondary btn-sm" style="flex:1;">৳2000</button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
function setAmount(amount) {
    document.querySelector('input[name="amount"]').value = amount;
}
</script>
@stop
