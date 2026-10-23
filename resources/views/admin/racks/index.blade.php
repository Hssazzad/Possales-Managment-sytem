@extends('adminlte::page')

@section('title', 'Rack List | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
    <style>
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

    /* Created date display in modal */
    .created-info-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 13px;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .created-info-box i {
        color: #9ca3af;
        font-size: 13px;
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
                <input type="text" id="rackSearch" placeholder="Search racks...">
            </div>
            <div class="action-buttons">
                <select id="statusFilter" class="form-control d-inline-block w-auto mr-2" style="border-radius:6px; border:1px solid #e5e7eb; color:#374151; font-size:14px;">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button onclick="openRackModal()" class="btn-add-new">
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
                        <th>Rack Name</th>
                        <th>Location</th>
                        <th class="text-center">Shelves</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Created</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($racks as $rack)
                    <tr class="rack-row" data-status="{{ $rack->is_active ? 'active' : 'inactive' }}">
                        <td><input type="checkbox" class="rack-checkbox" value="{{ $rack->id }}"></td>
                        <td class="text-muted">#{{ $rack->id }}</td>
                        <td>
                            <span style="padding:3px 8px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; font-size:12px; font-weight:600; color:#374151;">
                                {{ $rack->code }}
                            </span>
                        </td>
                        <td class="font-weight-bold">{{ $rack->name }}</td>
                        <td class="text-muted">{{ $rack->location ?? '—' }}</td>
                        <td class="text-center">{{ $rack->shelves_count ?? 0 }}</td>
                        <td class="text-center">{{ $rack->products_count ?? 0 }}</td>
                        <td class="text-center text-muted" style="font-size:13px;">
                            <i class="fas fa-calendar-alt mr-1" style="color:#9ca3af;"></i>
                            {{ $rack->created_at->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            @if($rack->is_active)
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
                                        {{ $rack->id }},
                                        '{{ addslashes($rack->name) }}',
                                        '{{ addslashes($rack->code) }}',
                                        '{{ addslashes($rack->location ?? '') }}',
                                        '{{ addslashes($rack->description ?? '') }}',
                                        {{ $rack->is_active ? 'true' : 'false' }},
                                        '{{ $rack->created_at->format('d M Y') }}'
                                    )">
                                        <i class="fas fa-edit text-info mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteRack({{ $rack->id }}, '{{ addslashes($rack->name) }}')">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-th-large fa-3x mb-3 text-light"></i>
                                <h5>No Racks Found</h5>
                                <p>There are currently no racks in the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($racks && $racks->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted text-sm">
                Showing {{ $racks->firstItem() }} to {{ $racks->lastItem() }} of {{ $racks->total() }} entries
            </div>
            <div>{{ $racks->links() }}</div>
        </div>
        @endif

    </div>
</div>

{{-- Rack Modal (Add + Edit) --}}
<div class="modal-overlay" id="rackModalOverlay" onclick="closeOnBackdrop(event)">
    <div class="category-modal">

        <div class="modal-header-custom">
            <h4 id="modalTitle"><i class="fas fa-plus-square mr-2"></i> Add New Rack</h4>
            <button class="modal-close-btn" onclick="closeRackModal()">×</button>
        </div>

        <div class="modal-body-custom">
            <form id="rackForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="rack_id" id="rackId" value="">

                <div class="modal-form-grid">
                    <!-- Name -->
                    <div class="modal-form-group">
                        <label>Rack Name <span class="req">*</span></label>
                        <input type="text" name="name" id="rackName" placeholder="e.g. Rack A1" required>
                    </div>
                    <!-- Code -->
                    <div class="modal-form-group">
                        <label>Code <span class="req">*</span></label>
                        <input type="text" name="code" id="rackCode" placeholder="e.g. RK-001" required>
                    </div>
                    <!-- Location -->
                    <div class="modal-form-group full">
                        <label>Location</label>
                        <input type="text" name="location" id="rackLocation" placeholder="e.g. Warehouse Section B">
                    </div>
                    <!-- Description -->
                    <div class="modal-form-group full">
                        <label>Description</label>
                        <textarea name="description" id="rackDescription" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <!-- Status -->
                    <div class="modal-form-group">
                        <label>Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="rackStatus" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span id="statusLabel" style="font-size:13px; color:#374151;">Active</span>
                        </div>
                    </div>
                    <!-- Created At (only shown in Edit mode) -->
                    <div class="modal-form-group" id="createdAtGroup" style="display:none;">
                        <label><i class="fas fa-calendar-alt mr-1"></i> Created At</label>
                        <div class="created-info-box">
                            <i class="fas fa-clock"></i>
                            <span id="modalCreatedAt">—</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-modal-cancel" onclick="closeRackModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn-modal-save" id="saveBtn">
                        <i class="fas fa-save mr-1"></i> Save Rack
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
const STORE_URL  = "{{ route('racks.store') }}";
const UPDATE_URL = "{{ url('admin/racks') }}";

// ── Toggle label ─────────────────────────────────────
document.getElementById('rackStatus').addEventListener('change', function() {
    document.getElementById('statusLabel').textContent = this.checked ? 'Active' : 'Inactive';
});

// ── Open Add Modal ───────────────────────────────────
function openRackModal() {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-square mr-2"></i> Add New Rack';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('rackForm').action  = STORE_URL;
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Rack';
    document.getElementById('createdAtGroup').style.display = 'none';
    document.getElementById('rackModalOverlay').classList.add('active');
}

// ── Open Edit Modal ──────────────────────────────────
function openEditModal(id, name, code, location, description, isActive, createdAt) {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Rack';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('rackForm').action  = UPDATE_URL + '/' + id + '?_method=PUT';
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Rack';

    document.getElementById('rackId').value          = id;
    document.getElementById('rackName').value        = name;
    document.getElementById('rackCode').value        = code;
    document.getElementById('rackLocation').value    = location;
    document.getElementById('rackDescription').value = description;

    // Show created date
    document.getElementById('modalCreatedAt').textContent = createdAt;
    document.getElementById('createdAtGroup').style.display = 'flex';

    const statusChk = document.getElementById('rackStatus');
    statusChk.checked = isActive;
    document.getElementById('statusLabel').textContent = isActive ? 'Active' : 'Inactive';

    document.getElementById('rackModalOverlay').classList.add('active');
}

// ── Close ────────────────────────────────────────────
function closeRackModal() {
    document.getElementById('rackModalOverlay').classList.remove('active');
}
function closeOnBackdrop(e) {
    if (e.target.id === 'rackModalOverlay') closeRackModal();
}

// ── Reset Form ───────────────────────────────────────
function resetForm() {
    document.getElementById('rackForm').reset();
    document.getElementById('rackId').value = '';
    document.getElementById('statusLabel').textContent = 'Active';
    document.getElementById('createdAtGroup').style.display = 'none';
    document.getElementById('modalCreatedAt').textContent = '—';
}

// ── ESC key ──────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeRackModal();
});

// ── Search ───────────────────────────────────────────
document.getElementById('rackSearch').addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('.rack-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(s) ? '' : 'none';
    });
});

// ── Status Filter ────────────────────────────────────
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    document.querySelectorAll('.rack-row').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
});

// ── Checkboxes ───────────────────────────────────────
function toggleAllCheckboxes(checkbox) {
    document.querySelectorAll('.rack-checkbox').forEach(cb => cb.checked = checkbox.checked);
}
document.querySelectorAll('.rack-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const all = document.querySelectorAll('.rack-checkbox').length;
        const chk = document.querySelectorAll('.rack-checkbox:checked').length;
        document.getElementById('selectAll').checked = (all === chk);
    });
});

// ── Delete ───────────────────────────────────────────
function deleteRack(rackId, rackName) {
    if (!confirm(`Are you sure you want to delete "${rackName}"?`)) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) return;
    fetch(`/admin/racks/${rackId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) location.reload();
        else alert('Error deleting rack.');
    })
    .catch(error => console.error('Error:', error));
}
</script>
@stop