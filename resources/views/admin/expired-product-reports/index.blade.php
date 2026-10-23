@extends('adminlte::page')

@section('title', 'Expired Product Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">Expired Product Reports</h1>
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
            <div class="summary-card expired-card">
                <div class="summary-info">
                    <p class="summary-label">Expired Products</p>
                    <h4 class="summary-value">{{ $expiredProducts->count() }}</h4>
                    <small class="text-muted">Value: {{ number_format($totalExpiredValue, 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card expiring-soon-card">
                <div class="summary-info">
                    <p class="summary-label">Expiring Soon</p>
                    <h4 class="summary-value">{{ $expiringSoonProducts->count() }}</h4>
                    <small class="text-muted">Value: {{ number_format($totalExpiringSoonValue, 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card not-expired-card">
                <div class="summary-info">
                    <p class="summary-label">Not Expired</p>
                    <h4 class="summary-value">{{ $notExpiredProducts->count() }}</h4>
                    <small class="text-muted">Value: {{ number_format($totalNotExpiredValue, 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-card">
                <div class="summary-info">
                    <p class="summary-label">Total Products</p>
                    <h4 class="summary-value">{{ $products->count() }}</h4>
                    <small class="text-muted">With expiry dates</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('expired-product-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control custom-select-styled" value="{{ request('start_date') }}">
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">End Date</label>
                            <input type="date" name="end_date" class="form-control custom-select-styled" value="{{ request('end_date') }}">
                        </div>
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
                            <label class="filter-label">Status</label>
                            <select name="status" class="form-control custom-select-styled">
                                <option value="">All Status</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                <option value="not_expired" {{ request('status') == 'not_expired' ? 'selected' : '' }}>Not Expired</option>
                            </select>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary filter-btn">
                                Filter
                            </button>
                            <a href="{{ route('expired-product-reports.index') }}" class="btn btn-outline-secondary filter-btn">
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
            <div class="summary-card category-breakdown-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Expired Products by Category</h5>
                    <div class="row">
                        @foreach($categoryBreakdown as $category)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="category-item">
                                <div class="category-header">
                                    <span class="category-name">{{ $category['category_name'] }}</span>
                                    <span class="category-count">{{ $category['total_products'] }}</span>
                                </div>
                                <div class="category-stats">
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

    <!-- Expired Products Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table expired-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Product Name</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Stock Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Stock Value</th>
                                    <th>Manufacturing Date</th>
                                    <th>Expiry Date</th>
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
                                        <td class="fw-semibold">{{ number_format($product->cost, 2) }}</td>
                                        <td class="fw-semibold">{{ number_format($product->stock_quantity * $product->cost, 2) }}</td>
                                        <td>
                                            @if($product->manufacturing_date)
                                                <span class="date-badge">{{ $product->manufacturing_date->format('d M, Y') }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->expire_date)
                                                <span class="expiry-badge {{ $product->expire_date->isPast() ? 'expired' : ($product->expire_date->diffInDays(now()) <= 30 ? 'expiring-soon' : 'valid') }}">
                                                    {{ $product->expire_date->format('d M, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->expire_date)
                                                @if($product->expire_date->isPast())
                                                    <span class="status-badge status-expired">Expired</span>
                                                @elseif($product->expire_date->diffInDays(now()) <= 30)
                                                    <span class="status-badge status-expiring-soon">Expiring Soon</span>
                                                @else
                                                    <span class="status-badge status-valid">Valid</span>
                                                @endif
                                            @else
                                                <span class="text-muted">No Date</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 empty-state">
                                            <p class="text-muted mb-0">No products with expiry dates found for the selected criteria.</p>
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
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #dc2626 !important; border-color: #dc2626 !important; }
    .btn-primary:hover { background: #b91c1c !important; border-color: #b91c1c !important; }

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
    .expired-card        { background: #fef2f2; border-color: #fecaca; }
    .expiring-soon-card  { background: #fef3c7; border-color: #fed7aa; }
    .not-expired-card    { background: #f0fdf4; border-color: #bbf7d0; }
    .total-card          { background: #eff6ff; border-color: #bfdbfe; }
    .category-breakdown-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }

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
    .category-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 8px; 
    }
    .category-name { font-weight: 600; color: #374151; font-size: 0.9rem; }
    .category-count {
        font-size: 0.75rem;
        font-weight: 600;
        background: #f3f4f6;
        color: #6b7280;
        padding: 2px 6px;
        border-radius: 10px;
        display: inline-block;
    }
    .category-stats { display: flex; flex-direction: column; gap: 4px; }
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
    .expired-table { font-size: 0.875rem; }
    .expired-table thead tr { background: #f8fafc; }
    .expired-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .expired-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .expired-table tbody tr { transition: background 0.15s; }
    .expired-table tbody tr:hover { background: #fafbff !important; }

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

    /* Date Badge */
    .date-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.78rem;
        font-weight: 500;
        background: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Expiry Badge */
    .expiry-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .expiry-badge.expired      { background: #fee2e2; color: #991b1b; }
    .expiry-badge.expiring-soon { background: #fef3c7; color: #92400e; }
    .expiry-badge.valid        { background: #d1fae5; color: #065f46; }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.status-expired      { background: #fee2e2; color: #991b1b; }
    .status-badge.status-expiring-soon { background: #fef3c7; color: #92400e; }
    .status-badge.status-valid        { background: #d1fae5; color: #065f46; }

    /* Empty State */
    .empty-state { color: #9ca3af; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .expired-table { font-size: 0.8rem; }
        .expired-table thead th { font-size: 0.65rem; }
    }
</style>
@endsection

@section('js')
<script>
// Set today's date as default for empty filters
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
@endsection
