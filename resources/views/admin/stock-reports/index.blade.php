@extends('adminlte::page')

@section('title', 'Stock Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">Stock Reports</h1>
    <span class="date-badge">
        {{ now()->format('l, d F Y') }}
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="summary-card total-products-card">
                <div class="summary-info">
                    <p class="summary-label">Total Products</p>
                    <h4 class="summary-value">{{ $totalProducts }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-stock-card">
                <div class="summary-info">
                    <p class="summary-label">Total Stock Quantity</p>
                    <h4 class="summary-value">{{ $totalStockQuantity }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card stock-value-card">
                <div class="summary-info">
                    <p class="summary-label">Total Stock Value</p>
                    <h4 class="summary-value">{{ number_format($totalStockValue, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card stock-status-card">
                <div class="summary-info">
                    <p class="summary-label">Stock Status</p>
                    <div class="status-breakdown">
                        <span class="status-item in-stock">In Stock: {{ $totalProducts - $outOfStockCount }}</span>
                        <span class="status-item out-stock">Out: {{ $outOfStockCount }}</span>
                        <span class="status-item low-stock">Low: {{ $lowStockCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('stock-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Category</label>
                            <select name="category" class="form-control custom-select-styled">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Stock Status</label>
                            <select name="stock_status" class="form-control custom-select-styled">
                                <option value="">All Status</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            </select>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary filter-btn">
                                Filter
                            </button>
                            <a href="{{ route('stock-reports.index') }}" class="btn btn-outline-secondary filter-btn">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    @if($categoryBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card category-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Category Breakdown</h5>
                    <div class="row">
                        @foreach($categoryBreakdown as $category)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="category-item">
                                <div class="category-header">
                                    <span class="category-name">{{ $category['category_name'] }}</span>
                                </div>
                                <div class="category-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Products:</span>
                                        <span class="stat-value">{{ $category['total_products'] }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Quantity:</span>
                                        <span class="stat-value">{{ $category['total_quantity'] }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Value:</span>
                                        <span class="stat-value">{{ number_format($category['total_value'], 2) }}</span>
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

    <!-- Stock Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table stock-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Min Stock</th>
                                    <th>Unit Cost</th>
                                    <th>Stock Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="product-info">
                                                <span class="product-name">{{ $product->name }}</span>
                                                @if($product->code)
                                                    <small class="product-code">{{ $product->code }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($product->barcode)
                                                <span class="barcode-badge">{{ $product->barcode }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="category-badge">{{ $product->category ? $product->category->name : 'Uncategorized' }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $product->stock_quantity }}</td>
                                        <td class="fw-semibold">{{ $product->min_stock_level ?? 0 }}</td>
                                        <td class="fw-semibold">{{ number_format($product->cost, 2) }}</td>
                                        <td class="fw-semibold">{{ number_format($product->stock_quantity * $product->cost, 2) }}</td>
                                        <td>
                                            @if($product->stock_quantity == 0)
                                                <span class="status-badge out-of-stock">Out of Stock</span>
                                            @elseif($product->stock_quantity <= $product->min_stock_level)
                                                <span class="status-badge low-stock">Low Stock</span>
                                            @else
                                                <span class="status-badge in-stock">In Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 empty-state">
                                            <p class="text-muted mb-0">No stock data found for the selected criteria.</p>
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
</div>
@endsection

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    /* ========== Base ========== */
    body, .content-wrapper {
        background: #f0f2f5 !important;
        font-family: 'DM Sans', sans-serif !important;
    }

    /* ========== Header ========== */
    .page-title { color: #111827; font-size: 1.25rem; }
    .date-badge {
        background: #f3f4f6;
        color: #6b7280;
        font-size: 0.8rem;
        padding: 5px 12px;
        border-radius: 20px;
        font-family: 'DM Mono', monospace;
    }

    /* ========== Filter Card ========== */
    .filter-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .filter-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: block;
    }
    .custom-select-styled {
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        color: #374151 !important;
        background-color: #f9fafb !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .custom-select-styled:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #3b82f6 !important; border-color: #3b82f6 !important; }
    .btn-primary:hover { background: #2563eb !important; border-color: #2563eb !important; }

    /* ========== Summary Cards ========== */
    .summary-card {
        padding: 18px 20px;
        border-radius: 14px;
        border: 1px solid transparent;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
    }
    .total-products-card { background: #eff6ff; border-color: #bfdbfe; }
    .total-stock-card   { background: #f0fdf4; border-color: #bbf7d0; }
    .stock-value-card   { background: #fef3c7; border-color: #fed7aa; }
    .stock-status-card  { background: #f8fafc; border-color: #e2e8f0; }
    .category-card      { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }

    .summary-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #6b7280;
        margin-bottom: 3px;
    }
    .summary-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
        font-family: 'DM Mono', monospace;
    }
    .status-breakdown {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .status-item {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        display: inline-block;
    }
    .in-stock { background: #d1fae5; color: #065f46; }
    .out-stock { background: #fee2e2; color: #991b1b; }
    .low-stock { background: #fef3c7; color: #92400e; }

    /* ========== Category Items ========== */
    .category-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s;
    }
    .category-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .category-header { margin-bottom: 8px; }
    .category-name { font-weight: 600; color: #374151; font-size: 0.9rem; }
    .category-stats { display: flex; gap: 16px; flex-wrap: wrap; }
    .stat-item { display: flex; align-items: center; gap: 4px; }
    .stat-label { font-size: 0.75rem; color: #6b7280; }
    .stat-value { font-weight: 600; color: #111827; font-size: 0.85rem; font-family: 'DM Mono', monospace; }

    /* ========== Main Card ========== */
    .main-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* ========== Table ========== */
    .stock-table { font-size: 0.875rem; }
    .stock-table thead tr { background: #f8fafc; }
    .stock-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .stock-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .stock-table tbody tr { transition: background 0.15s; }
    .stock-table tbody tr:hover { background: #fafbff !important; }

    /* Row animation */
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .table-row-animate {
        animation: fadeSlideIn 0.3s ease both;
    }

    /* Product Info */
    .product-info { }
    .product-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .product-code { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

    /* Barcode Badge */
    .barcode-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.78rem;
        font-weight: 500;
        background: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Category Badge */
    .category-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.75rem;
        font-weight: 600;
        background: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.in-stock      { background: #d1fae5; color: #065f46; }
    .status-badge.out-of-stock  { background: #fee2e2; color: #991b1b; }
    .status-badge.low-stock     { background: #fef3c7; color: #92400e; }

    /* Empty State */
    .empty-state { color: #9ca3af; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .stock-table { font-size: 0.8rem; }
        .stock-table thead th { font-size: 0.65rem; }
        .category-stats { flex-direction: column; gap: 4px; }
    }
</style>
@endsection

@section('js')
<script>
// Auto-focus on first filter
document.addEventListener('DOMContentLoaded', function() {
    const firstSelect = document.querySelector('select[name="category"]');
    if (firstSelect) {
        firstSelect.focus();
    }
});
</script>
@endsection
