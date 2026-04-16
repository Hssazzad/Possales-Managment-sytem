@extends('adminlte::page')

@section('title', 'Shelf List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-th mr-2" style="color:#20c997;"></i> Shelf Management
    </h1>
    <div>
        <a href="{{ route('shelves.create') }}" class="btn btn-primary btn-sm mr-2">
            <i class="fas fa-plus mr-1"></i> New Shelf
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h5 style="margin:0; font-weight:600;">
            <i class="fas fa-list mr-2" style="color:#20c997;"></i>
            Shelf List
        </h5>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control" id="shelfSearch" placeholder="Search shelves by name or code...">
                </div>
            </div>
        </div>

        <!-- Shelves Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Rack</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shelves as $shelf)
                    <tr>
                        <td>{{ $shelf->id }}</td>
                        <td><span class="badge badge-secondary">{{ $shelf->code }}</span></td>
                        <td><strong>{{ $shelf->name }}</strong></td>
                        <td>{{ $shelf->rack->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-info">{{ $shelf->products_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $shelf->is_active ? 'success' : 'secondary' }}">
                                {{ $shelf->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('shelves.edit', $shelf->id) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteShelf({{ $shelf->id }})" class="btn btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-th fa-3x text-muted mb-3" style="display:block; opacity:0.3;"></i>
                            <h5 class="text-muted">No shelves found</h5>
                            <p class="text-muted">Start by creating your first shelf.</p>
                            <a href="{{ route('shelves.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i> Create Shelf
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($shelves && $shelves->hasPages())
        <div class="d-flex justify-content-center">
            {{ $shelves->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-teal text-white" style="background-color: #20c997;">
            <div class="card-body">
                <h4 class="mb-0">{{ $shelves ? $shelves->total() : 0 }}</h4>
                <small>Total Shelves</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h4 class="mb-0">{{ $shelves ? $shelves->where('is_active', true)->count() : 0 }}</h4>
                <small>Active Shelves</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('shelfSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete shelf
function deleteShelf(shelfId) {
    if (confirm('Are you sure you want to delete this shelf?')) {
        fetch(`/admin/shelves/${shelfId}`, {
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
                alert('Error deleting shelf. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting shelf. Please try again.');
        });
    }
}
</script>
@stop

@section('adminlte_css')
<style>
.badge-success { background-color: #28a745; }
.badge-secondary { background-color: #6c757d; }
.badge-info { background-color: #17a2b8; }
.bg-teal { background-color: #20c997 !important; }
.table th { background-color: #f8f9fa; }
.btn-group-sm > .btn { padding: 0.25rem 0.5rem; }
</style>
@stop
