@extends('adminlte::page')

@section('title', 'Category List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-folder mr-2" style="color:#000000;"></i> Category Management
    </h1>
    <div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm mr-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-plus mr-1"></i> New Category
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
            Category List
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
                    <input type="text" class="form-control" id="categorySearch" placeholder="Search categories by name..." style="border:2px solid #333333; border-left:none; background:#ffffff; color:#000000;">
                </div>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
            <table class="table" style="margin:0; border-collapse:collapse;"
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">ID</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Icon</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Name</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Slug</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Brand</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Variations</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Products</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Status</th>
                        <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $category->id }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($category->icon)
                                <i class="fas {{ $category->icon }}" style="color:#000000;"></i>
                            @else
                                <i class="fas fa-folder" style="color:#000000;"></i>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;"><strong style="color:#000000;">{{ $category->name }}</strong></td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $category->slug }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $category->brand->name ?? '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            @if($category->variations && count($category->variations) > 0)
                                @foreach($category->variations as $variation)
                                    <span style="padding:2px 6px; border-radius:3px; font-size:0.75rem; font-weight:600; background:#17a2b8; color:#ffffff; margin-right:4px;">{{ $variation }}</span>
                                @endforeach
                            @else
                                <span style="color:#666666;">-</span>
                            @endif
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#ffc107; color:#212529;">{{ $category->products_count ?? 0 }}</span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:{{ $category->is_active ? '#28a745' : '#6c757d' }}; color:#ffffff;">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding:12px; border-bottom:1px solid #333333;">
                            <div class="btn-group btn-group-sm" style="gap:4px;">
                                <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteCategory({{ $category->id }})" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4" style="color:#000000;">
                            <i class="fas fa-folder fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                            <h5 style="color:#000000;">No categories found</h5>
                            <p style="color:#666666;">Start by creating your first category.</p>
                            <a href="{{ route('categories.create') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px;">
                                <i class="fas fa-plus mr-2"></i> Create Category
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($categories && $categories->hasPages())
        <div class="d-flex justify-content-center">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $categories ? $categories->total() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Total Categories</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $categories ? $categories->where('is_active', true)->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Active Categories</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('categorySearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete category
function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category?')) {
        fetch(`/admin/categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error deleting category. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting category. Please try again.');
        });
    }
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
</style>
@stop
