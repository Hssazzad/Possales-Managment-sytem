@extends('adminlte::page')

@section('title', 'Unit List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-ruler mr-2" style="color:#6f42c1;"></i> Unit Management
    </h1>
    <div>
        <a href="{{ route('units.create') }}" class="btn btn-primary btn-sm mr-2">
            <i class="fas fa-plus mr-1"></i> New Unit
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
            <i class="fas fa-list mr-2" style="color:#6f42c1;"></i>
            Unit List
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
                    <input type="text" class="form-control" id="unitSearch" placeholder="Search units by name...">
                </div>
            </div>
        </div>

        <!-- Units Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr>
                        <td>{{ $unit->id }}</td>
                        <td><strong>{{ $unit->name }}</strong></td>
                        <td>{{ $unit->slug }}</td>
                        <td>
                            <span class="badge badge-info">{{ $unit->products_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $unit->is_active ? 'success' : 'secondary' }}">
                                {{ $unit->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteUnit({{ $unit->id }})" class="btn btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-ruler fa-3x text-muted mb-3" style="display:block; opacity:0.3;"></i>
                            <h5 class="text-muted">No units found</h5>
                            <p class="text-muted">Start by creating your first unit.</p>
                            <a href="{{ route('units.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i> Create Unit
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($units && $units->hasPages())
        <div class="d-flex justify-content-center">
            {{ $units->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-purple text-white" style="background-color: #6f42c1;">
            <div class="card-body">
                <h4 class="mb-0">{{ $units ? $units->total() : 0 }}</h4>
                <small>Total Units</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h4 class="mb-0">{{ $units ? $units->where('is_active', true)->count() : 0 }}</h4>
                <small>Active Units</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('unitSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete unit
function deleteUnit(unitId) {
    if (confirm('Are you sure you want to delete this unit?')) {
        fetch(`/admin/units/${unitId}`, {
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
                alert('Error deleting unit. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting unit. Please try again.');
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
.bg-purple { background-color: #6f42c1 !important; }
.table th { background-color: #f8f9fa; }
.btn-group-sm > .btn { padding: 0.25rem 0.5rem; }
</style>
@stop
