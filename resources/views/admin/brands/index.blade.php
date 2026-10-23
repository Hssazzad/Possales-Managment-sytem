@extends('adminlte::page')

@section('title', 'Brand List | PosSales')

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

    /* Image preview */
    .image-preview-wrap {
        margin-top: 8px;
    }
    .image-preview-wrap img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    .image-preview-placeholder {
        width: 80px; height: 80px;
        background: #f3f4f6;
        border: 1px dashed #d1d5db;
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        color: #9ca3af;
        font-size: 24px;
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
                <input type="text" id="brandSearch" placeholder="Search brands...">
            </div>
            <div class="action-buttons">
                <select id="statusFilter" class="form-control d-inline-block w-auto mr-2" style="border-radius:6px; border:1px solid #e5e7eb; color:#374151; font-size:14px;">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button onclick="openBrandModal()" class="btn-add-new">
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
                        <th>Image</th>
                        <th>Brand Name</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Created</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr class="brand-row" data-status="{{ $brand->is_active ? 'active' : 'inactive' }}">
                        <td><input type="checkbox" class="brand-checkbox" value="{{ $brand->id }}"></td>
                        <td class="text-muted">#{{ $brand->id }}</td>
                        <td>
                            @if($brand->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($brand->image))
                                <img src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name }}"
                                     style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #e5e7eb;">
                            @else
                                <div style="width:40px; height:40px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                                    <i class="fas fa-copyright"></i>
                                </div>
                            @endif
                        </td>
                        <td class="font-weight-bold">{{ $brand->name }}</td>
                        <td class="text-center">{{ $brand->products_count ?? 0 }}</td>
                        <td class="text-center text-muted" style="font-size:13px;">
                            <i class="fas fa-calendar-alt mr-1" style="color:#9ca3af;"></i>
                            {{ $brand->created_at->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            @if($brand->is_active)
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
                                    <button type="button" class="dropdown-item btn-edit-brand"
                                        data-id="{{ $brand->id }}"
                                        data-name="{{ $brand->name }}"
                                        data-description="{{ Str::limit(preg_replace('/\s+/', ' ', strip_tags($brand->description ?? '')), 500) }}"
                                        data-image-url="{{ $brand->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($brand->image) ? asset('storage/' . $brand->image) : '' }}"
                                        data-active="{{ $brand->is_active ? '1' : '0' }}"
                                        data-created="{{ $brand->created_at->format('d M Y') }}">
                                        <i class="fas fa-edit text-info mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteBrand({{ $brand->id }}, '{{ addslashes($brand->name) }}')">
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
                                <i class="fas fa-copyright fa-3x mb-3 text-light"></i>
                                <h5>No Brands Found</h5>
                                <p>There are currently no brands in the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($brands && $brands->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted text-sm">
                Showing {{ $brands->firstItem() }} to {{ $brands->lastItem() }} of {{ $brands->total() }} entries
            </div>
            <div>{{ $brands->links() }}</div>
        </div>
        @endif

    </div>
</div>

{{-- Brand Modal (Add + Edit) --}}
<div class="modal-overlay" id="brandModalOverlay" onclick="closeOnBackdrop(event)">
    <div class="category-modal">

        <div class="modal-header-custom">
            <h4 id="modalTitle"><i class="fas fa-plus-square mr-2"></i> Add New Brand</h4>
            <button class="modal-close-btn" onclick="closeBrandModal()">×</button>
        </div>

        <div class="modal-body-custom">
            <form id="brandForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="brand_id" id="brandId" value="">
                <input type="hidden" name="is_active" value="0">

                <div class="modal-form-grid">
                    <!-- Name -->
                    <div class="modal-form-group full">
                        <label>Brand Name <span class="req">*</span></label>
                        <input type="text" name="name" id="brandName" placeholder="e.g. Nike" required>
                    </div>
                    <!-- Description -->
                    <div class="modal-form-group full">
                        <label>Description</label>
                        <textarea name="description" id="brandDescription" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <!-- Image Upload -->
                    <div class="modal-form-group full">
                        <label>Brand Image</label>
                        <input type="file" name="image" id="brandImage" accept="image/*" onchange="previewImage(event)">
                        <div class="image-preview-wrap" id="imagePreviewWrap">
                            <div class="image-preview-placeholder" id="imagePlaceholder">
                                <i class="fas fa-image"></i>
                            </div>
                            <img id="imagePreview" src="" alt="Preview" style="display:none;">
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="modal-form-group">
                        <label>Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="brandStatus" value="1" checked>
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
                    <button type="button" class="btn-modal-cancel" onclick="closeBrandModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn-modal-save" id="saveBtn">
                        <i class="fas fa-save mr-1"></i> Save Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
const STORE_URL  = "{{ route('brands.store') }}";
const UPDATE_URL = "{{ url('admin/brands') }}";

// ── Toggle label ─────────────────────────────────────
document.getElementById('brandStatus').addEventListener('change', function() {
    document.getElementById('statusLabel').textContent = this.checked ? 'Active' : 'Inactive';
});

// ── Image Preview ────────────────────────────────────
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('imagePlaceholder').style.display = 'none';
        const preview = document.getElementById('imagePreview');
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

// ── Open Add Modal ───────────────────────────────────
function openBrandModal() {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-square mr-2"></i> Add New Brand';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('brandForm').action  = STORE_URL;
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Brand';
    document.getElementById('createdAtGroup').style.display = 'none';
    document.getElementById('brandModalOverlay').classList.add('active');
}

// ── Open Edit Modal ──────────────────────────────────
function openEditModal(id, name, description, imageUrl, isActive, createdAt) {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Brand';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('brandForm').action  = UPDATE_URL + '/' + id;
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Brand';

    document.getElementById('brandId').value          = id;
    document.getElementById('brandName').value        = name;
    document.getElementById('brandDescription').value = description || '';

    // Show created date
    document.getElementById('modalCreatedAt').textContent = createdAt;
    document.getElementById('createdAtGroup').style.display = 'flex';

    // Show existing image
    if (imageUrl) {
        document.getElementById('imagePlaceholder').style.display = 'none';
        const preview = document.getElementById('imagePreview');
        preview.src = imageUrl;
        preview.style.display = 'block';
    }

    const statusChk = document.getElementById('brandStatus');
    statusChk.checked = !!isActive;
    document.getElementById('statusLabel').textContent = statusChk.checked ? 'Active' : 'Inactive';

    document.getElementById('brandModalOverlay').classList.add('active');
}

// ── Close ────────────────────────────────────────────
function closeBrandModal() {
    document.getElementById('brandModalOverlay').classList.remove('active');
}
function closeOnBackdrop(e) {
    if (e.target.id === 'brandModalOverlay') closeBrandModal();
}

// ── Reset Form ───────────────────────────────────────
function resetForm() {
    document.getElementById('brandForm').reset();
    document.getElementById('brandId').value = '';
    document.getElementById('statusLabel').textContent = 'Active';
    document.getElementById('imagePlaceholder').style.display = 'flex';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('imagePreview').src = '';
    document.getElementById('createdAtGroup').style.display = 'none';
    document.getElementById('modalCreatedAt').textContent = '—';
}

// ── ESC key ──────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeBrandModal();
});

// ── Search ───────────────────────────────────────────
document.getElementById('brandSearch').addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('.brand-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(s) ? '' : 'none';
    });
});

// ── Status Filter ────────────────────────────────────
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    document.querySelectorAll('.brand-row').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
});

// ── Checkboxes ───────────────────────────────────────
function toggleAllCheckboxes(checkbox) {
    document.querySelectorAll('.brand-checkbox').forEach(cb => cb.checked = checkbox.checked);
}
document.querySelectorAll('.brand-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const all = document.querySelectorAll('.brand-checkbox').length;
        const chk = document.querySelectorAll('.brand-checkbox:checked').length;
        document.getElementById('selectAll').checked = (all === chk);
    });
});

// ── Edit: open modal from data attributes (safe for special chars in name / image URL)
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-edit-brand');
    if (!btn) return;
    e.preventDefault();
    openEditModal(
        btn.getAttribute('data-id'),
        btn.getAttribute('data-name') || '',
        btn.getAttribute('data-description') || '',
        btn.getAttribute('data-image-url') || '',
        btn.getAttribute('data-active') === '1',
        btn.getAttribute('data-created') || ''
    );
});

// ── Delete ───────────────────────────────────────────
function deleteBrand(brandId, brandName) {
    if (!confirm(`Are you sure you want to delete "${brandName}"?`)) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) return;
    fetch(`/admin/brands/${brandId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) location.reload();
        else alert('Error deleting brand.');
    })
    .catch(error => console.error('Error:', error));
}
</script>
@stop