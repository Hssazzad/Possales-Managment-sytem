@extends('adminlte::page')

@section('plugins.Sweetalert2', true)

@section('title', 'Size Management | PosSales')

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
        width: 500px;
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
        grid-template-columns: 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }
    .modal-form-group { display: flex; flex-direction: column; gap: 5px; }
    .modal-form-group label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    .modal-form-group label .req { color: #e24b4a; }
    .modal-form-group input {
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
    .modal-form-group input:focus {
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
        <div class="top-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="sizeSearch" placeholder="Search sizes...">
            </div>
            <div class="action-buttons">
                <button onclick="openSizeModal()" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> Add New Size
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table" id="sizeTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Size Name</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Created At</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sizes as $size)
                    <tr class="size-row" id="row-{{ $size->id }}">
                        <td class="text-muted">#{{ $size->id }}</td>
                        <td class="font-weight-bold">{{ $size->name }}</td>
                        <td class="text-center">
                            <div class="toggle-wrap justify-content-center">
                                <label class="toggle-switch">
                                    <input type="checkbox" onchange="toggleStatus({{ $size->id }})" {{ $size->is_active ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </td>
                        <td class="text-center text-muted">
                            {{ $size->created_at->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <button class="dropdown-item" onclick="openEditModal({{ $size->id }})">
                                        <i class="fas fa-edit text-info mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteSize({{ $size->id }}, '{{ addslashes($size->name) }}')">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="no-data">
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-ruler fa-3x mb-3 text-light"></i>
                                <h5>No Sizes Found</h5>
                                <p>There are currently no sizes in the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Size Modal --}}
<div class="modal-overlay" id="sizeModalOverlay">
    <div class="category-modal">
        <div class="modal-header-custom">
            <h4 id="modalTitle">Add New Size</h4>
            <button class="modal-close-btn" onclick="closeSizeModal()">×</button>
        </div>
        <div class="modal-body-custom">
            <form id="sizeForm">
                @csrf
                <input type="hidden" name="size_id" id="sizeId">
                <div class="modal-form-grid">
                    <div class="modal-form-group">
                        <label>Size Name <span class="req">*</span></label>
                        <input type="text" name="name" id="sizeName" placeholder="e.g. XL, 42, Large" required>
                    </div>
                    <div class="modal-form-group">
                        <label>Status</label>
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="sizeStatus" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span id="statusLabel" style="font-size:13px; color:#374151;">Active</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn-modal-cancel" onclick="closeSizeModal()">Cancel</button>
                    <button type="submit" class="btn-modal-save" id="saveBtn">Save Size</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
const BASE_URL = "{{ url('admin/sizes') }}";

// Toggle label
$('#sizeStatus').change(function() {
    $('#statusLabel').text(this.checked ? 'Active' : 'Inactive');
});

function openSizeModal() {
    $('#sizeForm')[0].reset();
    $('#sizeId').val('');
    $('#modalTitle').text('Add New Size');
    $('#saveBtn').text('Save Size');
    $('#sizeStatus').prop('checked', true);
    $('#statusLabel').text('Active');
    $('#sizeModalOverlay').addClass('active');
}

function closeSizeModal() {
    $('#sizeModalOverlay').removeClass('active');
}

function openEditModal(id) {
    $.ajax({
        url: `${BASE_URL}/${id}`,
        method: 'GET',
        success: function(data) {
            $('#sizeId').val(data.id);
            $('#sizeName').val(data.name);
            $('#sizeStatus').prop('checked', data.is_active == 1);
            $('#statusLabel').text(data.is_active == 1 ? 'Active' : 'Inactive');
            $('#modalTitle').text('Edit Size');
            $('#saveBtn').text('Update Size');
            $('#sizeModalOverlay').addClass('active');
        },
        error: function() {
            Swal.fire('Error', 'Failed to fetch size data', 'error');
        }
    });
}

$('#sizeForm').submit(function(e) {
    e.preventDefault();
    let id = $('#sizeId').val();
    let url = id ? `${BASE_URL}/${id}` : BASE_URL;
    
    let formData = new FormData(this);
    if (id) {
        formData.append('_method', 'PUT');
    }
    
    // Explicitly handle is_active checkbox for boolean consistency
    formData.set('is_active', $('#sizeStatus').is(':checked') ? '1' : '0');

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            let errorMsg = 'Something went wrong';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                errorMsg = '';
                for (let field in errors) {
                    errorMsg += errors[field][0] + '\n';
                }
            }
            Swal.fire('Error', errorMsg, 'error');
        }
    });
});

function deleteSize(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Delete "${name}" size?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `${BASE_URL}/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}

function toggleStatus(id) {
    $.ajax({
        url: `${BASE_URL}/${id}/toggle-status`,
        method: 'PATCH',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to update status', 'error');
        }
    });
}

// Search functionality
$('#sizeSearch').on('input', function() {
    let s = $(this).val().toLowerCase();
    $('.size-row').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(s));
    });
});
</script>
@stop
