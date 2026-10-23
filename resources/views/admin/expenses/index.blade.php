@extends('adminlte::page')

@section('title')
    Expenses | {{ config('app.name') }}
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
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff5722 0%, #d84315 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Today's Expense</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($todayExpenses, 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-day text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">This Month's Expense</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($monthlyExpenses, 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-alt text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #795548 0%, #5d4037 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Operations Cost</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($totalOperationsCost, 2) }}</h3>
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
            <!-- Search Box -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="expenseSearch" placeholder="Search expenses..." onkeyup="filterExpenses()">
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="exportToCSV()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <a href="{{ route('expenses.create') }}" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> Add Expense
                </a>
            </div>
        </div>

        <!-- Expense Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Expense Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    @php
                        $categoryColor = $categoryColors[$expense->category] ?? '#6c757d';
                    @endphp
                    <tr class="expense-row">
                        <td>
                            <div class="font-weight-bold">{{ $expense->formatted_date }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $categoryColor }}; margin-right: 10px;"></div>
                                <span class="status-badge" style="background-color: {{ $categoryColor }}20; color: {{ $categoryColor }};">
                                    {{ $expense->category }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $expense->description }}</div>
                            @if($expense->payment_method)
                                <div class="text-muted small">
                                    <i class="fas fa-{{ $expense->payment_method == 'Cash' ? 'money-bill-wave' : ($expense->payment_method == 'Bank Transfer' ? 'university' : 'credit-card') }} mr-1"></i>
                                    {{ $expense->payment_method }}
                                </div>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold" style="font-size: 1.1rem; color: #c62828;">
                            ৳{{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="text-center">
                            <!-- Three Dot Menu -->
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <a class="dropdown-item" href="{{ route('expenses.show', $expense->id) }}">
                                        <i class="fas fa-eye text-info mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item" href="{{ route('expenses.edit', $expense->id) }}">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this expense?')">
                                            <i class="fas fa-trash-alt mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 text-light"></i>
                                <h5>No Expenses Found</h5>
                                <p>Start adding expenses to see them here.</p>
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

        <!-- Summary Footer -->
        @if($expenses->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                <span class="mr-3"><i class="fas fa-receipt text-warning mr-1"></i> Total Records: <strong>{{ $expenses->count() }}</strong></span>
                <span class="mr-3"><i class="fas fa-calendar-day text-danger mr-1"></i> Today: <strong>৳{{ number_format($todayExpenses, 2) }}</strong></span>
                <span><i class="fas fa-wallet text-success mr-1"></i> Total: <strong>৳{{ number_format($totalOperationsCost, 2) }}</strong></span>
            </div>
        </div>
        @endif

    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
function filterExpenses() {
    const searchTerm = document.getElementById('expenseSearch').value.toLowerCase();
    document.querySelectorAll('.expense-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
}

// Export to CSV
function exportToCSV() {
    let expenses = @json($expenses);
    let csv = 'Date,Category,Description,Amount,Payment Method\n';

    expenses.forEach((expense) => {
        const date = expense.formatted_date || '';
        const category = (expense.category || '').replace(/"/g, '""');
        const description = (expense.description || '').replace(/"/g, '""');
        const amount = expense.amount || 0;
        const paymentMethod = (expense.payment_method || '').replace(/"/g, '""');

        csv += `"${date}","${category}","${description}","${amount}","${paymentMethod}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'expenses_' + new Date().toISOString().slice(0,10) + '.csv';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
@stop

