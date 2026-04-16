@extends('adminlte::page')

@section('title', 'Rack List | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-th-large mr-2" style="color:#000000;"></i> Rack Management
    </h1>
    <div>
        <a href="{{ route('racks.create') }}" class="btn btn-primary btn-sm mr-2" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-plus mr-1"></i> New Rack
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
            Rack List
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
                    <input type="text" class="form-control" id="rackSearch" placeholder="Search racks by name or code..." style="border:2px solid #333333; border-left:none; background:#ffffff; color:#000000;">
                </div>
            </div>
        </div>

        <!-- Racks Table -->
        <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
            <table class="table" style="margin:0; border-collapse:collapse;"
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">ID</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Code</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Name</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Location</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Shelves</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Products</th>
                        <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Status</th>
                        <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($racks as $rack)
                    <tr>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $rack->id }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;"><span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#6c757d; color:#ffffff;">{{ $rack->code }}</span></td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;"><strong style="color:#000000;">{{ $rack->name }}</strong></td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $rack->location ?? '-' }}</td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#17a2b8; color:#ffffff;">{{ $rack->shelves_count ?? 0 }}</span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:#17a2b8; color:#ffffff;">{{ $rack->products_count ?? 0 }}</span>
                        </td>
                        <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                            <span style="padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; background:{{ $rack->is_active ? '#28a745' : '#6c757d' }}; color:#ffffff;">
                                {{ $rack->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding:12px; border-bottom:1px solid #333333;">
                            <div class="btn-group btn-group-sm" style="gap:4px;">
                                <a href="{{ route('racks.edit', $rack->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteRack({{ $rack->id }})" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4" style="color:#000000;">
                            <i class="fas fa-th-large fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                            <h5 style="color:#000000;">No racks found</h5>
                            <p style="color:#666666;">Start by creating your first rack.</p>
                            <a href="{{ route('racks.create') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px;">
                                <i class="fas fa-plus mr-2"></i> Create Rack
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($racks && $racks->hasPages())
        <div class="d-flex justify-content-center">
            {{ $racks->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $racks ? $racks->total() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Total Racks</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-body" style="padding:20px; text-align:center;">
                <h4 class="mb-0" style="color:#000000; font-weight:700;">{{ $racks ? $racks->where('is_active', true)->count() : 0 }}</h4>
                <small style="color:#000000; font-weight:600;">Active Racks</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Search functionality
document.getElementById('rackSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete rack
function deleteRack(rackId) {
    if (confirm('Are you sure you want to delete this rack?')) {
        fetch(`/admin/racks/${rackId}`, {
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
                alert('Error deleting rack. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting rack. Please try again.');
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
