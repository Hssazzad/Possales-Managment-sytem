@extends('adminlte::page')

@section('title')
    Expense Reports | {{ config('app.name') }}
@stop

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Expenses</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($totalExpenses, 2) }}</h3>
                                <small class="text-white-50">{{ $totalExpenseCount }} transactions</small>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-money-bill-wave text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Categories</p>
                                <h3 class="mb-0 font-weight-bold">{{ $categoryBreakdown->count() }}</h3>
                                <small class="text-white-50">Different types</small>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tags text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Payment Methods</p>
                                <h3 class="mb-0 font-weight-bold">{{ $paymentMethodBreakdown->count() }}</h3>
                                <small class="text-white-50">Different methods</small>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-credit-card text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Average Expense</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($totalExpenseCount > 0 ? $totalExpenses / $totalExpenseCount : 0, 2) }}</h3>
                                <small class="text-white-50">Per transaction</small>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chart-line text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Toolbar with Filter -->
        <div class="top-toolbar">
            <form method="GET" action="{{ route('expense-reports.index') }}" class="search-box" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; border: none; padding: 0; box-shadow: none; background: transparent;">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 2px; display: block;">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" style="width: 160px;" value="{{ request('start_date') }}">
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 2px; display: block;">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" style="width: 160px;" value="{{ request('end_date') }}">
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 2px; display: block;">Category</label>
                    <select name="category" class="form-control form-control-sm" style="width: 150px;">
                        <option value="">All Categories</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 2px; display: block;">Payment</label>
                    <select name="payment_method" class="form-control form-control-sm" style="width: 150px;">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>{{ ucfirst($method) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-export" style="margin-bottom: 0;">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('expense-reports.index') }}" class="btn btn-sm btn-export" style="margin-bottom: 0;">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </form>
        </div>

        <!-- Category Breakdown -->
        @if($categoryBreakdown->count() > 0)
        <div class="row mb-4 mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3" style="font-size: 1rem; font-weight: 600;">
                            <i class="fas fa-tags mr-2 text-warning"></i> Category Breakdown
                        </h5>
                        <div class="row">
                            @foreach($categoryBreakdown as $category)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-folder text-warning mr-2"></i>
                                        <span style="font-weight: 600; color: #374151; font-size: 0.9rem;">{{ $category['category_name'] }}</span>
                                    </div>
                                    <div class="d-flex" style="gap: 16px;">
                                        <div class="d-flex align-items-center" style="gap: 4px;">
                                            <span style="font-size: 0.75rem; color: #6b7280;">Amount:</span>
                                            <span style="font-weight: 600; color: #111827; font-size: 0.85rem;">৳{{ number_format($category['total'], 2) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap: 4px;">
                                            <span style="font-size: 0.75rem; color: #6b7280;">Count:</span>
                                            <span style="font-weight: 600; color: #111827; font-size: 0.85rem;">{{ $category['count'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Monthly Expenses -->
        @if(count($monthlyExpenses) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3" style="font-size: 1rem; font-weight: 600;">
                            <i class="fas fa-calendar-alt mr-2 text-primary"></i> Monthly Expenses
                        </h5>
                        <div class="row">
                            @foreach($monthlyExpenses as $monthly)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-chart-bar text-primary mr-2"></i>
                                        <span style="font-weight: 600; color: #374151; font-size: 0.9rem;">{{ $monthly['month'] }}</span>
                                    </div>
                                    <div class="d-flex" style="gap: 16px;">
                                        <div class="d-flex align-items-center" style="gap: 4px;">
                                            <span style="font-size: 0.75rem; color: #6b7280;">Amount:</span>
                                            <span style="font-weight: 600; color: #111827; font-size: 0.85rem;">৳{{ number_format($monthly['total'], 2) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap: 4px;">
                                            <span style="font-size: 0.75rem; color: #6b7280;">Count:</span>
                                            <span style="font-weight: 600; color: #111827; font-size: 0.85rem;">{{ $monthly['count'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Expense Details Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>SL.</th>
                        <th>Date</th>
                        <th>Receipt #</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Payment</th>
                        <th>Added By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $index => $expense)
                    <tr class="expense-row">
                        <td class="font-weight-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-weight-bold">{{ $expense->expense_date->format('d M, Y') }}</div>
                        </td>
                        <td>
                            @if($expense->receipt_number)
                                <span class="status-badge" style="background-color: #f3f4f6; color: #374151;">{{ $expense->receipt_number }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge" style="background: #fef3c7; color: #92400e;">
                                {{ $expense->expenseCategory->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $expense->description }}</div>
                        </td>
                        <td class="text-right font-weight-bold text-danger">
                            ৳{{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="status-badge" style="background-color: #f3f4f6; color: #374151;">{{ ucfirst($expense->payment_method ?? 'N/A') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span style="width: 28px; height: 28px; border-radius: 6px; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-weight: 700; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">{{ strtoupper(substr($expense->user->name ?? 'A', 0, 1)) }}</span>
                                <span style="font-weight: 500; color: #374151; font-size: 0.8rem;">{{ $expense->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($expense->notes)
                                <span class="text-muted" style="font-size: 0.85rem;">{{ Str::limit($expense->notes, 50) }}</span>
                            @else
                                <span class="text-muted">No notes</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-light"></i>
                                <h5>No Expenses Found</h5>
                                <p>No expenses found for the selected period.</p>
                                <a href="{{ route('expenses.create') }}" class="btn-add-new mt-2 d-inline-block">
                                    <i class="fas fa-plus mr-1"></i> Add Expense
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
