@extends('adminlte::page')

@section('title', 'Menu Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-bars mr-2" style="color:#c0392b;"></i> Menu Management
    </h1>
    <div>
        <a href="{{ route('menus.create') }}" class="btn btn-danger btn-sm mr-2">
            <i class="fas fa-plus mr-1"></i> Add Menu
        </a>
        <button onclick="toggleBulkDelete()" class="btn btn-warning btn-sm mr-2" id="bulkDeleteBtn" style="display:none;">
            <i class="fas fa-trash mr-1"></i> Delete Selected
        </button>
    </div>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:0.875rem;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th class="pl-4 py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">
                        <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                    </th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Menu Name</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Icon</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">URL</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Status</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td class="pl-4">
                        <input type="checkbox" class="menu-checkbox" value="{{ $menu->id }}" onchange="showBulkDeleteBtn()">
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <i class="{{ $menu->icon }} mr-2" style="color:#c0392b;"></i>
                        <strong>{{ $menu->text }}</strong>
                    </td>
                    <td><code>{{ $menu->icon }}</code></td>
                    <td><code>{{ $menu->url }}</code></td>
                    <td>
                        <form action="{{ route('menus.toggle', $menu) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $menu->is_active ? 'btn-success' : 'btn-secondary' }}" style="font-size:0.75rem; padding:2px 10px;">
                                {{ $menu->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-sm btn-info" onclick="viewMenuDetails({{ $menu->id }})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this menu?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                {{-- Submenu rows --}}
                @foreach($menu->children as $child)
                <tr style="background:#fafafa;">
                    <td class="pl-4">
                        <input type="checkbox" class="menu-checkbox" value="{{ $child->id }}" onchange="showBulkDeleteBtn()">
                    </td>
                    <td class="pl-4"></td>
                    <td class="pl-5">
                        <i class="{{ $child->icon }} mr-2" style="color:#adb5bd;"></i>
                        <span style="color:#6c757d;">↳ {{ $child->text }}</span>
                    </td>
                    <td><code>{{ $child->icon }}</code></td>
                    <td><code>{{ $child->url }}</code></td>
                    <td>
                        <form action="{{ route('menus.toggle', $child) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $child->is_active ? 'btn-success' : 'btn-secondary' }}" style="font-size:0.75rem; padding:2px 10px;">
                                {{ $child->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('menus.edit', $child) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-sm btn-info" onclick="viewMenuDetails({{ $child->id }})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <form action="{{ route('menus.destroy', $child) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this menu?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-inbox fa-2x d-block mb-2" style="color:#dee2e6;"></i>
                        <span class="text-muted">No menus found</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop

@section('adminlte_js')
<script>
function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.menu-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    showBulkDeleteBtn();
}

function showBulkDeleteBtn() {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    if (checkboxes.length > 0) {
        bulkDeleteBtn.style.display = 'inline-block';
    } else {
        bulkDeleteBtn.style.display = 'none';
    }
}

function toggleBulkDelete() {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    const menuIds = Array.from(checkboxes).map(cb => cb.value);

    if (menuIds.length === 0) {
        alert('Please select at least one menu to delete.');
        return;
    }

    if (confirm(`Are you sure you want to delete ${menuIds.length} menu(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("menus.bulk-delete") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const menuIdsInput = document.createElement('input');
        menuIdsInput.type = 'hidden';
        menuIdsInput.name = 'menu_ids[]';
        menuIdsInput.value = menuIds.join(',');
        form.appendChild(menuIdsInput);

        document.body.appendChild(form);
        form.submit();
    }
}

function viewMenuDetails(menuId) {
    // You can add a modal or redirect to view details
    window.location.href = `/admin/menus/${menuId}`;
}
</script>
@endsection

@section('adminlte_css')
<style>
    .main-sidebar, .main-sidebar::before { background-color: #2d3436 !important; }
    .brand-link { background-color: #232b2b !important; border-bottom: 1px solid #1a1f1f !important; }
    .brand-link .brand-text { color: #fff !important; font-weight:700 !important; }
    .nav-sidebar .nav-item > .nav-link { color: rgba(255,255,255,0.80) !important; }
    .nav-sidebar .nav-item > .nav-link .nav-icon { color: rgba(255,255,255,0.50) !important; }
    .nav-sidebar .nav-item > .nav-link:hover { background-color: rgba(255,255,255,0.08) !important; color:#fff !important; }
    .nav-sidebar .nav-item > .nav-link.active { background-color: #c0392b !important; color:#fff !important; border-radius:4px !important; }
    .nav-sidebar .nav-item > .nav-link.active .nav-icon { color:#fff !important; }
    .nav-treeview { background-color: rgba(0,0,0,0.18) !important; }
    .nav-sidebar .nav-treeview .nav-link { color: rgba(255,255,255,0.65) !important; }
    .nav-sidebar .nav-treeview .nav-link:hover { background-color: rgba(255,255,255,0.08) !important; color:#fff !important; }
    .nav-sidebar .nav-treeview .nav-link.active { background-color: #c0392b !important; color:#fff !important; }
    .main-header.navbar { background-color: #fff !important; border-bottom: 1px solid #e9ecef !important; }
    .main-header.navbar .nav-link { color: #6c757d !important; }
</style>
@stop
