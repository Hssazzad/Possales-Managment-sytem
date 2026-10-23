@extends('adminlte::page')

@section('title')
    Add Expense | {{ config('app.name') }}
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-plus mr-2" style="color:#dc2626;"></i> Add Expense
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
                    <i class="fas fa-plus mr-2" style="color:#dc2626;"></i> Add New Expense
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('expenses.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description" class="form-label">Description *</label>
                                <input type="text" id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Enter expense description" required value="{{ old('description') }}">
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount" class="form-label">Amount *</label>
                                <input type="number" id="amount" name="amount" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00" required value="{{ old('amount') }}">
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
                                        <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
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
                                <input type="date" id="expense_date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required>
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
                                        <option value="{{ $method }}" {{ old('payment_method') == $method ? 'selected' : '' }}>{{ $method }}</option>
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
                                <input type="text" id="receipt_number" name="receipt_number" class="form-control @error('receipt_number') is-invalid @enderror" placeholder="Enter receipt number (optional)" value="{{ old('receipt_number') }}">
                                @error('receipt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="Enter additional notes (optional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Expense
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
                    <i class="fas fa-info-circle mr-2" style="color:#dc2626;"></i> Tips
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <ul class="mb-0">
                        <li>Enter a clear description for the expense</li>
                        <li>Choose appropriate category for better tracking</li>
                        <li>Keep receipts for documentation</li>
                        <li>Add notes for additional context</li>
                        <li>Record expense date accurately</li>
                    </ul>
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
