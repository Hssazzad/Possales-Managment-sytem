@extends('adminlte::page')

@section('title', 'Product List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-box mr-2" style="color:#000000;"></i> Product Management
    </h1>
    <div>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mr-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-plus mr-1"></i> New Product
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
    <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
        <h5 style="margin:0; font-weight:600; color:#000000;">
            <i class="fas fa-list mr-2" style="color:#000000;"></i>
            Product List
        </h5>
    </div>
    <div class="card-body" style="padding:20px;">
        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <div class="input-group-prepend" style="background:#ffffff; border:2px solid #333333; border-right:none;">
                        <span class="input-group-text" style="background:#ffffff; border:none; color:#000000;">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control" id="productSearch" placeholder="Search products by name or code..." style="border:2px solid #333333; border-left:none; background:#ffffff; color:#000000;">
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-success btn-sm" onclick="exportProducts()" style="background:#ffffff; color:#000000; border:2px solid #333333;">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
            <table class="table" style="margin:0; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Product Name</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Code</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Brand</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Category</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Unit</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Purchase Price</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Sale Price</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Stock</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Rack</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Shelf</th>
                        <th style="padding:12px; font-weight:600; color:#000000;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <div class="d-flex align-items-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px; margin-right: 8px; border:1px solid #333333;">
                                @else
                                    <div style="width: 30px; height: 30px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-right: 8px; border:1px solid #333333;">
                                        <i class="fas fa-box" style="font-size: 12px; color:#000000;"></i>
                                    </div>
                                @endif
                                <span style="color:#000000;">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;"><strong style="color:#000000;">{{ $product->code }}</strong></td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->brand ?: '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->category ?: '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->unit ?: '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ number_format($product->purchase_price, 2) }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ number_format($product->sale_price, 2) }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:{{ $product->stock_quantity <= $product->min_stock_level ? '#dc3545' : '#28a745' }}; color:#ffffff;">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->rack ?: '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->shelf ?: '-' }}</td>
                        <td style="padding:12px; border-bottom:1px solid #333333;">
                            <div class="btn-group btn-group-sm" style="gap:4px;">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm" title="View" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4" style="color:#000000;">
                            <i class="fas fa-box fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                            <h5 style="color:#000000;">No products found</h5>
                            <p style="color:#666666;">Start by creating your first product.</p>
                            <a href="{{ route('products.create') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px;">
                                <i class="fas fa-plus mr-2"></i> Create Product
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products && $products->hasPages())
        <div class="d-flex justify-content-center">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $products ? $products->total() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Total Products</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $products ? $products->where('is_active', true)->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Active Products</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $products ? $products->where('stock_quantity', '<=', 10)->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Low Stock</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $products ? $products->sum('stock_quantity') : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Total Stock</small>
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

// Delete product
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        fetch(`/admin/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting product: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting product. Please try again.');
        });
    }
}

// Export products
function exportProducts() {
    window.location.href = '/admin/products/export';
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.table-responsive { max-height: 80vh; overflow-y: auto; }
.table th:nth-child(1), .table td:nth-child(1) { min-width: 250px; }
.table th:nth-child(2), .table td:nth-child(2) { width: 120px; }
.table th:nth-child(3), .table td:nth-child(3) { width: 120px; }
.table th:nth-child(4), .table td:nth-child(4) { width: 120px; }
.table th:nth-child(5), .table td:nth-child(5) { width: 100px; }
.table th:nth-child(6), .table td:nth-child(6) { width: 120px; }
.table th:nth-child(7), .table td:nth-child(7) { width: 120px; }
.table th:nth-child(8), .table td:nth-child(8) { width: 100px; }
.table th:nth-child(9), .table td:nth-child(9) { width: 100px; }
.table th:nth-child(10), .table td:nth-child(10) { width: 100px; }
.table th:nth-child(11), .table td:nth-child(11) { width: 150px; }
</style>
@stop
