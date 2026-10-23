@extends('adminlte::page')

@section('title')
    Expense Categories | {{ config('app.name') }}
@stop

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #6f42c1 0%, #553098 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Categories</p>
                                <h3 class="mb-0 font-weight-bold">{{ $categories->count() }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tags text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Expenses</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($categories->sum('total_expense'), 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-money-bill-wave text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Transactions</p>
                                <h3 class="mb-0 font-weight-bold">{{ $categories->sum('transaction_count') }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-receipt text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="categorySearch" placeholder="Search categories..." onkeyup="filterCategories()">
            </div>
            <div class="action-buttons">
                <button onclick="exportToCSV()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button onclick="window.print()" class="btn-export">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('expenses.categories.create') }}" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> Add Category
                </a>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="row mb-4" id="categoriesGrid">
            @forelse($categories as $category)
            <div class="col-md-6 col-lg-4 mb-3 category-card-item">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div style="width:16px; height:16px; border-radius:50%; background:{{ $category['color'] }}; margin-right:10px; flex-shrink:0;"></div>
                            <h5 class="mb-0 font-weight-bold">{{ $category['name'] }}</h5>
                        </div>
                        <p class="text-muted" style="font-size:0.9rem; min-height:40px;">{{ $category['description'] ?: 'No description' }}</p>
                        <div class="d-flex justify-content-between align-items-center pt-3" style="border-top:1px solid #e5e7eb;">
                            <div>
                                <div class="text-muted" style="font-size:0.75rem;">Total Expense</div>
                                <div class="font-weight-bold">৳{{ number_format($category['total_expense'], 2) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-muted" style="font-size:0.75rem;">Transactions</div>
                                <div class="font-weight-bold">{{ $category['transaction_count'] }}</div>
                            </div>
                            <div>
                                <a href="{{ route('expenses.categories.edit', $category['id']) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('expenses.categories.destroy', $category['id']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this expense category?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-tags fa-3x mb-3 text-light"></i>
                    <h5>No Expense Categories Found</h5>
                    <p>Start adding expense categories to see them here.</p>
                    <a href="{{ route('expenses.categories.create') }}" class="btn-add-new mt-2 d-inline-block">
                        <i class="fas fa-plus mr-1"></i> Add Category
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Categories Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>SL.</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th class="text-right">Total Expense</th>
                        <th class="text-center">Transactions</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                    <tr class="category-row">
                        <td class="text-muted font-weight-bold">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="width:12px; height:12px; border-radius:50%; background:{{ $category['color'] }}; margin-right:8px;"></div>
                                <span class="font-weight-bold">{{ $category['name'] }}</span>
                            </div>
                        </td>
                        <td>{{ $category['description'] ?: 'No description' }}</td>
                        <td class="text-right font-weight-bold">৳{{ number_format($category['total_expense'], 2) }}</td>
                        <td class="text-center">{{ $category['transaction_count'] }}</td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <a class="dropdown-item" href="{{ route('expenses.categories.edit', $category['id']) }}">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('expenses.categories.destroy', $category['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this expense category?')">
                                            <i class="fas fa-trash-alt mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-tags fa-3x mb-3 text-light"></i>
                                <h5>No Expense Categories Found</h5>
                                <p>Start adding expense categories to see them here.</p>
                                <a href="{{ route('expenses.categories.create') }}" class="btn-add-new mt-2 d-inline-block">
                                    <i class="fas fa-plus mr-1"></i> Add Category
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
function filterCategories() {
    const searchTerm = document.getElementById('categorySearch').value.toLowerCase();
    document.querySelectorAll('.category-card-item').forEach(card => {
        card.style.display = card.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
    document.querySelectorAll('.category-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
}

function exportToCSV() {
    let categories = @json($categories);
    let csv = 'SL,Category Name,Description,Total Expense,Transactions\n';
    categories.forEach((category, index) => {
        csv += `${index + 1},"${category.name}","${category.description}","${category.total_expense}","${category.transaction_count}"\n`;
    });
    let blob = new Blob([csv], { type: 'text/csv' });
    let url = window.URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = 'expense_categories_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@stop
