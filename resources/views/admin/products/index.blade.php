@extends('adminlte::page')

@section('title', 'Product List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#1e293b;">
        <i class="fas fa-box mr-2" style="color:#003366;"></i> Product Management
    </h1>
    <div>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mr-2 shadow-sm">
            <i class="fas fa-plus mr-1"></i> New Product
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm border shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card shadow-sm border-0" style="border-radius:8px;">
    <div class="card-header bg-white" style="border-bottom:1px solid #f1f5f9; padding:16px;">
        <h5 style="margin:0; font-weight:600; color:#1e293b;">
            <i class="fas fa-list mr-2" style="color:#003366;"></i>
            Product List
        </h5>
    </div>
    <div class="card-body" style="padding:20px;">
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group shadow-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control border-left-0" id="productSearch" placeholder="Search products by name or code...">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-outline-success btn-sm shadow-sm" onclick="exportProducts()">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="margin:0;">
                <thead>
                    <tr class="bg-light">
                        <th>Product Name</th>
                        <th>Code</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Purchase</th>
                        <th>Sale</th>
                        <th>Stock</th>
                        <th>Rack</th>
                        <th>Shelf</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 6px; margin-right: 10px; border:1px solid #e2e8f0;">
                                @else
                                    <div style="width: 32px; height: 32px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border-radius: 6px; margin-right: 10px; border:1px solid #e2e8f0;">
                                        <i class="fas fa-box text-muted" style="font-size: 14px;"></i>
                                    </div>
                                @endif
                                <span class="font-weight-500 text-dark">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td><span class="badge badge-light border text-dark">{{ $product->code }}</span></td>
                        <td class="text-muted">{{ $product->brand ?: '-' }}</td>
                        <td class="text-muted">{{ $product->category ?: '-' }}</td>
                        <td class="text-muted">{{ $product->unit ?: '-' }}</td>
                        <td class="text-dark font-weight-500">{{ number_format($product->purchase_price, 2) }}</td>
                        <td class="text-dark font-weight-500">{{ number_format($product->sale_price, 2) }}</td>
                        <td>
                            <span class="badge {{ $product->stock_quantity <= $product->min_stock_level ? 'badge-danger' : 'badge-success' }} px-2 py-1">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $product->rack ?: '-' }}</td>
                        <td class="text-muted">{{ $product->shelf ?: '-' }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-xs btn-outline-info shadow-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-xs btn-outline-primary shadow-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger shadow-sm" title="Delete" onclick="return confirm('Delete this product?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="fas fa-box fa-3x mb-3 text-light" style="display:block;"></i>
                            <h5 class="text-muted">No products found</h5>
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-2 shadow-sm">
                                <i class="fas fa-plus mr-2"></i> Create First Product
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products && $products->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 font-weight-bold text-dark">{{ $products ? $products->total() : 0 }}</h4>
                <small class="text-muted font-weight-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Total Products</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 font-weight-bold text-success">{{ $products ? $products->where('is_active', true)->count() : 0 }}</h4>
                <small class="text-muted font-weight-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Active Products</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 font-weight-bold text-danger">{{ $products ? $products->where('stock_quantity', '<=', 10)->count() : 0 }}</h4>
                <small class="text-muted font-weight-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Low Stock</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 font-weight-bold text-info">{{ $products ? $products->sum('stock_quantity') : 0 }}</h4>
                <small class="text-muted font-weight-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Total Stock</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('productSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Export products
function exportProducts() {
    window.location.href = '/admin/products/export';
}
</script>
@stop

@section('adminlte_css')
<style>
    .table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #64748b;
        padding: 1rem 0.75rem !important;
        border-top: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .table td {
        padding: 1rem 0.75rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 0.875rem;
    }
    .badge {
        font-weight: 600;
        font-size: 0.75rem;
        border-radius: 4px;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 4px;
    }
    .gap-2 { gap: 0.5rem; }
    .font-weight-500 { font-weight: 500; }
</style>
@stop
