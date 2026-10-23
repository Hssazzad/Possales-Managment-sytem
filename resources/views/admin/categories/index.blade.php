@extends('adminlte::page')

@section('title', 'Category List | PosSales')

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

    .variation-checks {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 4px;
    }
    .variation-checks label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        color: #374151;
        font-weight: 400;
        cursor: pointer;
        background: #f3f4f6;
        padding: 5px 10px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        transition: all .15s;
    }
    .variation-checks label:has(input:checked) {
        background: #e6f1fb;
        border-color: #1a3a6b;
        color: #1a3a6b;
    }
    .variation-checks input[type="checkbox"] { margin: 0; }

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
                <input type="text" id="categorySearch" placeholder="Search categories...">
            </div>
            <div class="action-buttons">
                <select id="statusFilter" class="form-control d-inline-block w-auto mr-2" style="border-radius:6px; border:1px solid #e5e7eb; color:#374151; font-size:14px;">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button onclick="exportCategories('csv')" class="btn-export">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>
                <button onclick="openCategoryModal()" class="btn-add-new">
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
                        <th>Icon</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Brand</th>
                        <th>Variations</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="category-row" data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                        <td><input type="checkbox" class="category-checkbox" value="{{ $category->id }}"></td>
                        <td class="text-muted">#{{ $category->id }}</td>
                        <td>
                            <div class="icon-wrapper">
                                <i class="fas {{ $category->icon ?? 'fa-folder' }}"></i>
                            </div>
                        </td>
                        <td class="font-weight-bold">{{ $category->name }}</td>
                        <td class="text-muted">{{ $category->slug }}</td>
                        <td>{{ $category->brand->name ?? '-' }}</td>
                        <td>
                            @if($category->variations && count($category->variations) > 0)
                                @foreach(array_slice($category->variations, 0, 2) as $variation)
                                    <span class="variation-tag">{{ $variation }}</span>
                                @endforeach
                                @if(count($category->variations) > 2)
                                    <span class="variation-tag">+{{ count($category->variations) - 2 }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $category->products_count ?? 0 }}</td>
                        <td class="text-center">
                            @if($category->is_active)
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
                                        {{ $category->id }},
                                        '{{ addslashes($category->name) }}',
                                        '{{ addslashes($category->slug) }}',
                                        '{{ addslashes($category->icon ?? '') }}',
                                        {{ $category->brand_id ?? 'null' }},
                                        {{ json_encode($category->variations ?? []) }},
                                        '{{ addslashes($category->description ?? '') }}',
                                        {{ $category->is_active ? 'true' : 'false' }}
                                    )">
                                        <i class="fas fa-edit text-info mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}')">
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
                                <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                                <h5>No Categories Found</h5>
                                <p>There are currently no categories in the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories && $categories->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted text-sm">
                Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} entries
            </div>
            <div>{{ $categories->links() }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Modal --}}
<div class="modal-overlay" id="categoryModalOverlay" onclick="closeOnBackdrop(event)">
    <div class="category-modal">

        <div class="modal-header-custom">
            <h4 id="modalTitle"><i class="fas fa-folder-plus mr-2"></i> Add New Category</h4>
            <button class="modal-close-btn" onclick="closeCategoryModal()">×</button>
        </div>

        <div class="modal-body-custom">
            <form id="categoryForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="category_id" id="categoryId" value="">

                <div class="modal-form-grid">
                    <div class="modal-form-group">
                        <label>Category Name <span class="req">*</span></label>
                        <input type="text" name="name" id="catName" placeholder="e.g. Electronics" required>
                    </div>
                    <div class="modal-form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="catSlug" placeholder="auto-generated">
                    </div>
                    <div class="modal-form-group">
                        <label>Icon Class</label>
                        <input type="text" name="icon" id="catIcon" placeholder="fa-folder">
                    </div>
                    <div class="modal-form-group">
                        <label>Brand</label>
                        <select name="brand_id" id="catBrand">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-form-group full">
                        <label>Description</label>
                        <textarea name="description" id="catDescription" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <!-- Variations -->
                    <div class="modal-form-group full">
                        <label>Variations</label>
                        <div class="variation-checks">
                            @foreach(['Capacity','Color','Size','Type','Weight'] as $v)
                            <label>
                                <input type="checkbox" name="variations[]" value="{{ $v }}" class="var-check">
                                {{ $v }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-form-group full">
                        <label>Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="catStatus" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span id="statusLabel" style="font-size:13px; color:#374151;">Active</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-modal-cancel" onclick="closeCategoryModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="button" class="btn-modal-save" id="saveBtn" onclick="submitCategoryForm()">
                        <i class="fas fa-save mr-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
const STORE_URL  = "{{ route('categories.store') }}";
const UPDATE_URL = "{{ url('admin/categories') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";

// ── Auto slug ────────────────────────────────────────
document.getElementById('catName').addEventListener('input', function() {
    const slug = document.getElementById('catSlug');
    if (!slug.dataset.manual) {
        slug.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    }
});
document.getElementById('catSlug').addEventListener('input', function() {
    this.dataset.manual = this.value ? 'true' : '';
});

// ── Toggle label ─────────────────────────────────────
document.getElementById('catStatus').addEventListener('change', function() {
    document.getElementById('statusLabel').textContent = this.checked ? 'Active' : 'Inactive';
});

// ── Open Add Modal ───────────────────────────────────
function openCategoryModal() {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-folder-plus mr-2"></i> Add New Category';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Category';
    document.getElementById('categoryModalOverlay').classList.add('active');
}

// ── Open Edit Modal ──────────────────────────────────
function openEditModal(id, name, slug, icon, brandId, variations, description, isActive) {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Category';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Category';

    document.getElementById('categoryId').value      = id;
    document.getElementById('catName').value         = name;
    document.getElementById('catSlug').value         = slug;
    document.getElementById('catSlug').dataset.manual = 'true';
    document.getElementById('catIcon').value         = icon;
    document.getElementById('catDescription').value  = description;

    const brandSel = document.getElementById('catBrand');
    if (brandId) brandSel.value = brandId;

    // ✅ Variations — array check করে checkbox tick করো
    const varArray = Array.isArray(variations) ? variations : [];
    document.querySelectorAll('.var-check').forEach(cb => {
        cb.checked = varArray.includes(cb.value);
    });

    const statusChk = document.getElementById('catStatus');
    statusChk.checked = isActive;
    document.getElementById('statusLabel').textContent = isActive ? 'Active' : 'Inactive';

    document.getElementById('categoryModalOverlay').classList.add('active');
}

// ── AJAX Submit ──────────────────────────────────────
function submitCategoryForm() {
    const method   = document.getElementById('formMethod').value;
    const catId    = document.getElementById('categoryId').value;
    const url      = (method === 'PUT') ? UPDATE_URL + '/' + catId : STORE_URL;

    // Collect variations
    const variations = [];
    document.querySelectorAll('.var-check:checked').forEach(cb => {
        variations.push(cb.value);
    });

    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('_method', method);
    formData.append('name',        document.getElementById('catName').value);
    formData.append('slug',        document.getElementById('catSlug').value);
    formData.append('icon',        document.getElementById('catIcon').value);
    formData.append('brand_id',    document.getElementById('catBrand').value);
    formData.append('description', document.getElementById('catDescription').value);
    formData.append('is_active',   document.getElementById('catStatus').checked ? '1' : '0');

    // ✅ variations array properly append করো
    variations.forEach(v => formData.append('variations[]', v));

    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeCategoryModal();
            location.reload();
        } else {
            alert(data.message || 'Something went wrong!');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Server error. Please try again.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Category';
    });
}

// ── Close ────────────────────────────────────────────
function closeCategoryModal() {
    document.getElementById('categoryModalOverlay').classList.remove('active');
}
function closeOnBackdrop(e) {
    if (e.target.id === 'categoryModalOverlay') closeCategoryModal();
}

// ── Reset ────────────────────────────────────────────
function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('catSlug').dataset.manual = '';
    document.getElementById('statusLabel').textContent = 'Active';
    document.querySelectorAll('.var-check').forEach(cb => cb.checked = false);
}

// ── ESC ──────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCategoryModal();
});

// ── Search ───────────────────────────────────────────
document.getElementById('categorySearch').addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('.category-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(s) ? '' : 'none';
    });
});

// ── Status Filter ────────────────────────────────────
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    document.querySelectorAll('.category-row').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
});

// ── Checkboxes ───────────────────────────────────────
function toggleAllCheckboxes(checkbox) {
    document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = checkbox.checked);
}
document.querySelectorAll('.category-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const all = document.querySelectorAll('.category-checkbox').length;
        const chk = document.querySelectorAll('.category-checkbox:checked').length;
        document.getElementById('selectAll').checked = (all === chk);
    });
});

// ── Export ───────────────────────────────────────────
function exportCategories(format) {
    const ids = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(cb => cb.value);
    if (ids.length === 0) { alert('Please select at least one category to export.'); return; }
    const params = new URLSearchParams();
    ids.forEach(id => params.append('ids[]', id));
    params.append('format', format);
    window.location.href = `/admin/categories/export?${params.toString()}`;
}

// ── Delete ───────────────────────────────────────────
function deleteCategory(categoryId, categoryName) {
    if (!confirm(`Are you sure you want to delete "${categoryName}"?`)) return;
    fetch(`/admin/categories/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) location.reload();
        else alert('Error deleting category.');
    })
    .catch(error => console.error('Error:', error));
}
</script>
@stop