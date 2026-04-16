@extends('adminlte::page')

@section('title', 'Model List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-cube mr-2" style="color:#6c757d;"></i> Model Management
    </h1>
    <div>
        <a href="{{ route('models.create') }}" class="btn btn-primary btn-sm mr-2">
            <i class="fas fa-plus mr-1"></i> New Model
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
            <i class="fas fa-list mr-2" style="color:#6c757d;"></i>
            Model List
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
                    <input type="text" class="form-control" id="modelSearch" placeholder="Search models by name...">
                </div>
            </div>
        </div>

        <!-- Models Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($models as $model)
                    <tr>
                        <td>{{ $model->id }}</td>
                        <td>
                            @if($model->image && file_exists(storage_path('app/public/' . $model->image)))
                                <img src="{{ asset('storage/' . $model->image) }}" alt="{{ $model->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            @else
                                <div style="width: 50px; height: 50px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                    <i class="fas fa-cube text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td><strong>{{ $model->name }}</strong></td>
                        <td>{{ $model->brand->name ?? '-' }}</td>
                        <td>{{ $model->slug }}</td>
                        <td>
                            <span class="badge badge-info">{{ $model->products_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $model->is_active ? 'success' : 'secondary' }}">
                                {{ $model->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('models.edit', $model->id) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteModel({{ $model->id }})" class="btn btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-cube fa-3x text-muted mb-3" style="display:block; opacity:0.3;"></i>
                            <h5 class="text-muted">No models found</h5>
                            <p class="text-muted">Start by creating your first model.</p>
                            <a href="{{ route('models.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i> Create Model
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($models && $models->hasPages())
        <div class="d-flex justify-content-center">
            {{ $models->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h4 class="mb-0">{{ $models ? $models->total() : 0 }}</h4>
                <small>Total Models</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h4 class="mb-0">{{ $models ? $models->where('is_active', true)->count() : 0 }}</h4>
                <small>Active Models</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('modelSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete model
function deleteModel(modelId) {
    if (confirm('Are you sure you want to delete this model?')) {
        fetch(`/admin/models/${modelId}`, {
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
                alert('Error deleting model. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting model. Please try again.');
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
.table th { background-color: #f8f9fa; }
.btn-group-sm > .btn { padding: 0.25rem 0.5rem; }
</style>
@stop
