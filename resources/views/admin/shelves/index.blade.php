@extends('adminlte::page')

@section('title', 'Shelf List | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
    <style>
    /* ── Modal Overlay ─────────────────────────────── */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: flex-start;
        padding-top: 40px;
    }
    .modal-overlay.active { display: flex; }

    .category-modal {
        background: #ffffff;
        border-radius: 8px;
        width: 680px;
        max-width: 95vw;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalIn .2s ease;
    }
    @keyframes modalIn {
        from { transform: translateY(-20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .modal-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .modal-header-custom h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #111;
    }
    .modal-close-btn {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #e24b4a;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-body-custom { padding: 24px; }

    .modal-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }
    .modal-form-group { display: flex; flex-direction: column; gap: 5px; }
    .modal-form-group.full { grid-column: 1 / -1; }
    .modal-form-group label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    .modal-form-group label .req { color: #e24b4a; }
    .modal-form-group select,
    .modal-form-group input,
    .modal-form-group textarea {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        color: #111;
        background: #fff;
        outline: none;
        transition: border-color .15s;
        width: 100%;
    }
    .modal-form-group select:focus,
    .modal-form-group input:focus,
    .modal-form-group textarea:focus {
        border-color: #1a3a6b;
        box-shadow: 0 0 0 3px rgba(26,58,107,.08);
    }

    /* Toggle switch */
    .toggle-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 6px;
    }
    .toggle-switch {
        position: relative;
        width: 42px; height: 22px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #d1d5db;
        border-radius: 22px;
        transition: .3s;
    }
    .toggle-slider:before {
        content: "";
        position: absolute;
        width: 16px; height: 16px;
        left: 3px; bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: .3s;
    }
    .toggle-switch input:checked + .toggle-slider { background: #1a3a6b; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }

    /* Footer */
    .modal-footer-custom {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
        margin-top: 8px;
    }
    .btn-modal-save {
        padding: 9px 22px;
        background: #1a3a6b;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }
    .btn-modal-save:hover { background: #14306a; }
    .btn-modal-cancel {
        padding: 9px 18px;
        background: #fff;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    .btn-modal-cancel:hover { background: #f3f4f6; }
    </style>
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="shelfSearch" placeholder="Search shelves...">
            </div>
            <div class="action-buttons">
                <select id="statusFilter" class="form-control d-inline-block w-auto mr-2" style="border-radius:6px; border:1px solid #e5e7eb; color:#374151; font-size:14px;">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button onclick="openShelfModal()" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> Add New
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)">
                        </th>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Shelf Name</th>
                        <th>Rack</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shelves as $shelf)
                    <tr class="shelf-row" data-status="{{ $shelf->is_active ? 'active' : 'inactive' }}">
                        <td><input type="checkbox" class="shelf-checkbox" value="{{ $shelf->id }}"></td>
                        <td class="text-muted">#{{ $shelf->id }}</td>
                        <td><span class="variation-tag">{{ $shelf->code }}</span></td>
                        <td class="font-weight-bold">{{ $shelf->name }}</td>
                        <td>{{ $shelf->rack->name ?? '-' }}</td>
                        <td class="text-center">{{ $shelf->products_count ?? 0 }}</td>
                        <td class="text-center">
                            @if($shelf->is_active)
                                <span class="status-badge status-active">Active</span>
                            @else
                                <span class="status-badge status-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <button class="dropdown-item" onclick="openEditModal(
                                        {{ $shelf->id }},
                                        '{{ addslashes($shelf->name) }}',
                                        '{{ addslashes($shelf->code) }}',
                                        {{ $shelf->rack_id ?? 'null' }},
                                        '{{ addslashes($shelf->description ?? '') }}',
                                        {{ $shelf->is_active ? 'true' : 'false' }}
                                    )">
                                        <i class="fas fa-edit text-info mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteShelf({{ $shelf->id }}, '{{ addslashes($shelf->name) }}')">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-th fa-3x mb-3 text-light"></i>
                                <h5>No Shelves Found</h5>
                                <p>There are currently no shelves in the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($shelves && $shelves->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted text-sm">
                Showing {{ $shelves->firstItem() }} to {{ $shelves->lastItem() }} of {{ $shelves->total() }} entries
            </div>
            <div>{{ $shelves->links() }}</div>
        </div>
        @endif

    </div>
</div>

{{-- ✅ Shelf Modal (Add + Edit) --}}
<div class="modal-overlay" id="shelfModalOverlay" onclick="closeOnBackdrop(event)">
    <div class="category-modal">

        <div class="modal-header-custom">
            <h4 id="modalTitle"><i class="fas fa-plus-square mr-2"></i> Add New Shelf</h4>
            <button class="modal-close-btn" onclick="closeShelfModal()">×</button>
        </div>

        <div class="modal-body-custom">
            <form id="shelfForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="shelf_id" id="shelfId" value="">

                <div class="modal-form-grid">
                    <!-- Name -->
                    <div class="modal-form-group">
                        <label>Shelf Name <span class="req">*</span></label>
                        <input type="text" name="name" id="shelfName" placeholder="e.g. Shelf A1" required>
                    </div>
                    <!-- Code -->
                    <div class="modal-form-group">
                        <label>Code <span class="req">*</span></label>
                        <input type="text" name="code" id="shelfCode" placeholder="e.g. SH-A1" required>
                    </div>
                    <!-- Rack -->
                    <div class="modal-form-group full">
                        <label>Rack</label>
                        <select name="rack_id" id="shelfRack">
                            <option value="">Select Rack</option>
                            @foreach($racks as $rack)
                                <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Description -->
                    <div class="modal-form-group full">
                        <label>Description</label>
                        <textarea name="description" id="shelfDescription" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <!-- Status -->
                    <div class="modal-form-group full">
                        <label>Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="shelfStatus" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span id="statusLabel" style="font-size:13px; color:#374151;">Active</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-modal-cancel" onclick="closeShelfModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn-modal-save" id="saveBtn">
                        <i class="fas fa-save mr-1"></i> Save Shelf
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
const STORE_URL  = "{{ route('shelves.store') }}";
const UPDATE_URL = "{{ url('admin/shelves') }}";

// ── Open Add Modal ───────────────────────────────────
function openShelfModal() {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-square mr-2"></i> Add New Shelf';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('shelfForm').action  = STORE_URL;
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Shelf';
    document.getElementById('shelfModalOverlay').classList.add('active');
}

// ── Open Edit Modal ──────────────────────────────────
function openEditModal(id, name, code, rackId, description, isActive) {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Shelf';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('shelfForm').action  = UPDATE_URL + '/' + id;
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Shelf';

    document.getElementById('shelfId').value          = id;
    document.getElementById('shelfName').value        = name;
    document.getElementById('shelfCode').value        = code;
    document.getElementById('shelfDescription').value = description;

    const rackSel = document.getElementById('shelfRack');
    if (rackId) rackSel.value = rackId;

    const statusChk = document.getElementById('shelfStatus');
    statusChk.checked = isActive;
    document.getElementById('statusLabel').textContent = isActive ? 'Active' : 'Inactive';

    document.getElementById('shelfModalOverlay').classList.add('active');
}

// ── Close ────────────────────────────────────────────
function closeShelfModal() {
    document.getElementById('shelfModalOverlay').classList.remove('active');
}
function closeOnBackdrop(e) {
    if (e.target.id === 'shelfModalOverlay') closeShelfModal();
}

// ── Reset Form ───────────────────────────────────────
function resetForm() {
    document.getElementById('shelfForm').reset();
    document.getElementById('shelfId').value = '';
    document.getElementById('statusLabel').textContent = 'Active';
}

// ── ESC key ──────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeShelfModal();
});

// ── Status label ─────────────────────────────────────
document.getElementById('shelfStatus').addEventListener('change', function() {
    document.getElementById('statusLabel').textContent = this.checked ? 'Active' : 'Inactive';
});

// ── Search ───────────────────────────────────────────
document.getElementById('shelfSearch').addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('.shelf-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(s) ? '' : 'none';
    });
});

// ── Status Filter ────────────────────────────────────
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    document.querySelectorAll('.shelf-row').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
});

// ── Checkboxes ───────────────────────────────────────
function toggleAllCheckboxes(checkbox) {
    document.querySelectorAll('.shelf-checkbox').forEach(cb => cb.checked = checkbox.checked);
}
document.querySelectorAll('.shelf-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const all = document.querySelectorAll('.shelf-checkbox').length;
        const chk = document.querySelectorAll('.shelf-checkbox:checked').length;
        document.getElementById('selectAll').checked = (all === chk);
    });
});

// ── Delete ───────────────────────────────────────────
function deleteShelf(shelfId, shelfName) {
    if (!confirm(`Are you sure you want to delete "${shelfName}"?`)) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) return;
    fetch(`/admin/shelves/${shelfId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) location.reload();
        else alert('Error deleting shelf.');
    })
    .catch(error => console.error('Error:', error));
}
</script>
@stop