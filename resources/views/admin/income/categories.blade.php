@extends('adminlte::page')

@section('title', 'Income Categories | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-tags mr-2" style="color:#000000;"></i> Income Categories
    </h1>
    <div>
        <a href="{{ route('income.categories.create') }}" class="btn btn-secondary btn-sm" style="background:#000000; color:#ffffff; border:2px solid #000000; margin-right:10px;">
            <i class="fas fa-plus mr-1"></i> Add new
        </a>
        <button onclick="window.printCategories()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333; margin-right:10px;">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <button onclick="window.exportToCSV()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </button>
        <a href="{{ route('income.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Income
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        {{-- Summary Cards --}}
        <div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Categories</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $categories->count() }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Income</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ number_format($categories->sum('total_income'), 2) }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Transactions</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $categories->sum('transaction_count') }}</div>
            </div>
        </div>

        {{-- Category Cards --}}
        <div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
            @foreach($categories as $category)
            <div style="flex:1; min-width:200px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="display:flex; align-items:center; margin-bottom:12px;">
                    <div style="width:12px; height:12px; border-radius:50%; background:{{ $category['color'] }}; margin-right:8px;"></div>
                    <h3 style="margin:0; font-size:1.1rem; font-weight:600; color:#000000;">{{ $category['name'] }}</h3>
                </div>
                <p style="margin:0 0 12px 0; color:#666666; font-size:14px;">{{ $category['description'] }}</p>
                <div style="border-top:1px solid #333333; padding-top:12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="color:#666666; font-size:14px;">Income:</span>
                        <span style="font-weight:600; color:#000000;">{{ number_format($category['total_income'], 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#666666; font-size:14px;">Transactions:</span>
                        <span style="font-weight:600; color:#000000;">{{ $category['transaction_count'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-tags mr-2" style="color:#000000;"></i> Income Categories Details
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
                    <table class="table" style="margin:0; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">SL.</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Category</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Description</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Total Income</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Transactions</th>
                                <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $loop->index + 1 }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                                    <div style="display:flex; align-items:center;">
                                        <div style="width:12px; height:12px; border-radius:50%; background:{{ $category['color'] }}; margin-right:8px;"></div>
                                        <span style="font-weight:600; color:#000000;">{{ $category['name'] }}</span>
                                    </div>
                                </td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $category['description'] }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ number_format($category['total_income'], 2) }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $category['transaction_count'] }}</td>
                                <td style="padding:12px; border-bottom:1px solid #333333;">
                                    <div class="btn-group btn-group-sm" style="gap:4px;">
                                        <a href="{{ route('income.categories.edit', $category['id']) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px; text-decoration:none;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('income.categories.destroy', $category['id']) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this income category?')" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#cc0000; border:2px solid #cc0000; padding:6px 10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color:#000000;">
                                    <i class="fas fa-tags fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                                    <h5 style="color:#000000;">No categories found</h5>
                                    <p style="color:#666666;">Start adding income categories to see them here.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Make functions globally available
window.exportToCSV = function() {
    try {
        const categories = @json($categories);

        if (!categories || categories.length === 0) {
            alert('No data available to export');
            return;
        }

        let csv = 'SL.,Category,Description,Total Income,Transactions\n';

        categories.forEach((category, index) => {
            const sl = index + 1;
            const name = (category.name || '').replace(/"/g, '""');
            const description = (category.description || '').replace(/"/g, '""');
            const income = category.total_income || 0;
            const transactions = category.transaction_count || 0;

            csv += `${sl},"${name}","${description}","${income}","${transactions}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'income_categories_' + new Date().toISOString().slice(0,10) + '.csv';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        console.log('CSV export completed');
    } catch (error) {
        console.error('CSV export error:', error);
        alert('Error exporting CSV: ' + error.message);
    }
};

window.printCategories = function() {
    try {
        console.log('Print function called');
        window.print();
    } catch (error) {
        console.error('Print error:', error);
        alert('Error printing: ' + error.message);
    }
};

window.editCategory = function(id) {
    alert('Edit functionality for category ID: ' + id + '\n\nThis would open an edit form to modify the category.');
};

window.deleteCategory = function(id) {
    if (confirm('Are you sure you want to delete this income category?\n\nThis action cannot be undone.')) {
        alert('Delete functionality for category ID: ' + id + '\n\nThis would send a delete request to the server and remove the category.');
    }
};

// Debug: Check if data is available
console.log('Categories data:', @json($categories));

document.addEventListener('DOMContentLoaded', function() {
    console.log('Categories page loaded');
    console.log('Functions available:', typeof window.exportToCSV, typeof window.printCategories, typeof window.editCategory, typeof window.deleteCategory);
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
@media print {
    body * { visibility: hidden; }
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .table-responsive { display: block !important; }
    .card { display: block !important; }
    .card-header { display: block !important; }
    .card-body { display: block !important; }
    table { display: block !important; }
    thead { display: table-header-group !important; }
    tbody { display: table-row-group !important; }
    tr { display: table-row !important; }
    th, td {
        display: table-cell !important;
        border: 1px solid #000 !important;
        padding: 8px !important;
        vertical-align: top !important;
    }
    th {
        background: #f5f5f5 !important;
        font-weight: bold !important;
        color: #000 !important;
    }
}
</style>
@stop
