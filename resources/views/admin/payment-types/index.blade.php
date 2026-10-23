@extends('adminlte::page')

@section('title', 'Payment Types | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">
        Payment Type List
    </h1>
    <button class="btn add-payment-btn" data-toggle="modal" data-target="#addPaymentModal">
        <i class="fas fa-plus-circle mr-2"></i> Add Payment Type
    </button>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:10px; font-size:0.875rem;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-radius:10px; font-size:0.875rem;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- Main Card -->
    <div class="main-card">
        <div class="card-body p-0">

            <!-- Table Controls -->
            <div class="d-flex align-items-center justify-content-between flex-wrap px-3 pt-3 pb-2" style="gap:10px;">
                <div class="d-flex align-items-center" style="gap:8px;">
                    <span class="control-label">Show-</span>
                    <div class="show-select-wrap">
                        <select class="show-select" id="perPageSelect" onchange="changePerPage(this.value)">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <i class="fas fa-chevron-down show-select-arrow"></i>
                    </div>
                </div>
                <div class="search-wrap">
                    <input type="text" id="tableSearch" class="search-input" placeholder="Search...">
                    <button class="search-icon-btn"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table payment-table mb-0" id="paymentTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" class="custom-check" id="checkAll" onclick="toggleAll(this)">
                            </th>
                            <th>SL.</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th class="text-right pr-4">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($paymentTypes as $index => $paymentType)
                        <tr class="table-row-animate" style="animation-delay: {{ $index * 0.04 }}s">
                            <td>
                                <input type="checkbox" class="custom-check row-check">
                            </td>
                            <td class="sl-num">{{ $index + 1 }}</td>
                            <td class="payment-name-cell">{{ $paymentType->name }}</td>
                            <td>
                                <form method="POST" action="{{ route('payment-types.updateStatus', $paymentType) }}" class="d-inline toggle-status-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="toggle-switch" title="{{ $paymentType->active ? 'Click to Deactivate' : 'Click to Activate' }}">
                                        <input type="checkbox" {{ $paymentType->active ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td class="text-right pr-4">
                                <div class="dropdown">
                                    <button class="dots-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right action-menu">
                                        <a class="dropdown-item action-item edit-item"
                                           href="#"
                                           data-toggle="modal"
                                           data-target="#editPaymentModal"
                                           data-id="{{ $paymentType->id }}"
                                           data-name="{{ $paymentType->name }}">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <div class="dropdown-divider my-1"></div>
                                        <form method="POST" action="{{ route('payment-types.destroy', $paymentType) }}"
                                              onsubmit="return confirm('Delete \'{{ $paymentType->name }}\'? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item action-item delete-item">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 empty-state">
                                <div class="empty-icon-wrap">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <p class="empty-title">No payment types yet</p>
                                <p class="empty-sub">Click <strong>"Add Payment Type"</strong> to create your first one.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="table-footer px-3 py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap:6px;">
                <span class="footer-info" id="showingInfo">
                    Showing <strong>1</strong> to <strong>{{ min(10, count($paymentTypes)) }}</strong> of <strong>{{ count($paymentTypes) }}</strong> entries
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ===== Add Payment Type Modal ===== -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
        <div class="modal-content custom-modal">
            <div class="modal-header custom-modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon-wrap">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="addPaymentModalLabel">Add Payment Type</h5>
                        <p class="modal-subtitle">Create a new payment method</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('payment-types.store') }}">
                @csrf
                <div class="modal-body custom-modal-body">
                    <div class="form-group mb-0">
                        <label class="modal-label">Payment Type Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               id="addPaymentName"
                               class="modal-input @error('name') is-invalid @enderror"
                               placeholder="e.g. bKash, Nagad, Cash, Card..."
                               required
                               autocomplete="off">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer custom-modal-footer">
                    <button type="button" class="btn modal-cancel-btn" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn modal-submit-btn">
                        <i class="fas fa-plus mr-1"></i> Add Payment Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Edit Payment Type Modal ===== -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
        <div class="modal-content custom-modal">
            <div class="modal-header custom-modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon-wrap edit-icon-wrap">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment Type</h5>
                        <p class="modal-subtitle">Update payment method name</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="editPaymentForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body custom-modal-body">
                    <div class="form-group mb-0">
                        <label class="modal-label">Payment Type Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               id="editPaymentName"
                               class="modal-input"
                               placeholder="Enter payment type name..."
                               required
                               autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer custom-modal-footer">
                    <button type="button" class="btn modal-cancel-btn" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn modal-submit-btn edit-submit-btn">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    /* ========== Base ========== */
    body, .content-wrapper {
        background: #f0f2f5 !important;
        font-family: 'DM Sans', sans-serif !important;
    }

    /* ========== Header ========== */
    .page-title {
        color: #111827;
        font-size: 1.3rem;
        font-weight: 700;
    }
    .add-payment-btn {
        background: #e53935 !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        padding: 9px 20px !important;
        box-shadow: 0 3px 10px rgba(229,57,53,0.25);
        transition: all 0.2s;
    }
    .add-payment-btn:hover {
        background: #c62828 !important;
        box-shadow: 0 4px 16px rgba(229,57,53,0.35);
        transform: translateY(-1px);
    }

    /* ========== Main Card ========== */
    .main-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: visible;
    }
    .table-responsive {
        overflow: visible;
    }

    /* ========== Controls ========== */
    .control-label { font-size: 0.85rem; color: #6b7280; font-weight: 500; }
    .show-select-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .show-select {
        appearance: none;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        padding: 5px 32px 5px 12px;
        font-size: 0.85rem;
        font-family: 'DM Sans', sans-serif;
        color: #374151;
        background: #f9fafb;
        cursor: pointer;
        outline: none;
        transition: border-color 0.2s;
    }
    .show-select:focus { border-color: #6366f1; }
    .show-select-arrow {
        position: absolute; right: 10px;
        color: #9ca3af; font-size: 0.65rem;
        pointer-events: none;
    }
    .search-wrap {
        display: flex;
        align-items: center;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background: #f9fafb;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .search-wrap:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .search-input {
        border: none;
        background: transparent;
        padding: 6px 12px;
        font-size: 0.85rem;
        font-family: 'DM Sans', sans-serif;
        color: #374151;
        width: 200px;
        outline: none;
    }
    .search-icon-btn {
        border: none;
        background: transparent;
        padding: 6px 10px;
        color: #9ca3af;
        cursor: pointer;
        font-size: 0.8rem;
    }

    /* ========== Table ========== */
    .payment-table { font-size: 0.875rem; }
    .payment-table thead tr { background: #f8fafc; }
    .payment-table thead th {
        font-size: 0.8rem;
        font-weight: 700;
        color: #374151;
        border: none !important;
        padding: 12px 16px;
        white-space: nowrap;
    }
    .payment-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 13px 16px;
        color: #374151;
    }
    .payment-table tbody tr {
        position: relative;
        transition: background 0.15s;
    }
    .payment-table tbody tr:hover { background: #fafbff !important; }
    .payment-table tbody tr.dropdown-open,
    .payment-table tbody tr:has(.show) {
        z-index: 50;
    }

    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: none; }
    }
    .table-row-animate {
        animation: fadeSlideIn 0.25s ease;
    }

    .sl-num { font-weight: 600; color: #6b7280; width: 60px; }
    .payment-name-cell { font-weight: 500; color: #111827; font-size: 0.9rem; }

    /* ========== Checkbox ========== */
    .custom-check {
        width: 16px; height: 16px;
        accent-color: #e53935;
        cursor: pointer;
    }

    /* ========== Toggle Switch ========== */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin: 0;
        cursor: pointer;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        inset: 0;
        background: #d1d5db;
        border-radius: 34px;
        transition: 0.3s;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 3px; bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .toggle-switch input:checked + .toggle-slider { background: #e53935; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(22px); }

    /* ========== Dots Menu ========== */
    .dots-btn {
        background: none;
        border: none;
        width: 32px; height: 32px;
        border-radius: 6px;
        color: #6b7280;
        font-size: 0.95rem;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .dots-btn:hover { background: #f3f4f6; color: #374151; }
    .action-menu {
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
        min-width: 140px;
        padding: 6px !important;
        z-index: 1060 !important;
    }
    .action-item {
        border-radius: 6px !important;
        font-size: 0.82rem !important;
        padding: 7px 12px !important;
        color: #374151 !important;
        font-weight: 500 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s;
    }
    .action-item:hover { background: #f3f4f6 !important; text-decoration: none; }
    .edit-item:hover { background: #eff6ff !important; color: #1d4ed8 !important; }
    .delete-item:hover { background: #fff1f2 !important; color: #dc2626 !important; }
    .dropdown-divider { border-color: #f3f4f6; margin: 3px 0; }

    /* ========== Empty State ========== */
    .empty-state { padding: 40px 0; }
    .empty-icon-wrap {
        width: 60px; height: 60px;
        border-radius: 16px;
        background: #f3f4f6;
        color: #9ca3af;
        font-size: 1.5rem;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }
    .empty-title { font-size: 0.95rem; font-weight: 600; color: #374151; margin: 0 0 4px; }
    .empty-sub { font-size: 0.82rem; color: #9ca3af; margin: 0; }

    /* ========== Table Footer ========== */
    .table-footer { border-top: 1px solid #f3f4f6; background: #fafafa; }
    .footer-info { font-size: 0.82rem; color: #6b7280; }

    /* ========== MODAL ========== */
    .custom-modal {
        border: none !important;
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
    }
    .custom-modal-header {
        background: #fff;
        border-bottom: 1px solid #f3f4f6 !important;
        padding: 20px 24px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .modal-icon-wrap {
        width: 42px; height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, #e53935, #ef5350);
        color: #fff;
        font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(229,57,53,0.3);
    }
    .edit-icon-wrap {
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        box-shadow: 0 4px 12px rgba(99,102,241,0.3) !important;
    }
    .modal-title {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin: 0 0 2px !important;
    }
    .modal-subtitle {
        font-size: 0.78rem;
        color: #9ca3af;
        margin: 0;
    }
    .modal-close-btn {
        background: #f3f4f6;
        border: none;
        width: 32px; height: 32px;
        border-radius: 8px;
        color: #6b7280;
        font-size: 0.8rem;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .modal-close-btn:hover { background: #fee2e2; color: #dc2626; }
    .custom-modal-body {
        padding: 24px !important;
        background: #fff;
    }
    .modal-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .modal-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
        font-family: 'DM Sans', sans-serif;
        color: #111827;
        background: #f9fafb;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .modal-input:focus {
        border-color: #e53935;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(229,57,53,0.1);
    }
    .modal-input.is-invalid { border-color: #dc2626; }
    .invalid-feedback { font-size: 0.78rem; margin-top: 5px; }
    .custom-modal-footer {
        background: #f9fafb;
        border-top: 1px solid #f3f4f6 !important;
        padding: 16px 24px !important;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .modal-cancel-btn {
        background: #fff !important;
        border: 1.5px solid #e5e7eb !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: #6b7280 !important;
        padding: 8px 20px !important;
        transition: all 0.2s;
    }
    .modal-cancel-btn:hover {
        background: #f3f4f6 !important;
        border-color: #d1d5db !important;
        color: #374151 !important;
    }
    .modal-submit-btn {
        background: #e53935 !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: #fff !important;
        padding: 8px 22px !important;
        box-shadow: 0 3px 10px rgba(229,57,53,0.25);
        transition: all 0.2s;
    }
    .modal-submit-btn:hover {
        background: #c62828 !important;
        box-shadow: 0 4px 14px rgba(229,57,53,0.4);
        transform: translateY(-1px);
    }
    .edit-submit-btn {
        background: #6366f1 !important;
        box-shadow: 0 3px 10px rgba(99,102,241,0.25) !important;
    }
    .edit-submit-btn:hover {
        background: #4f46e5 !important;
        box-shadow: 0 4px 14px rgba(99,102,241,0.4) !important;
    }

    /* Modal backdrop blur */
    .modal-backdrop.show { backdrop-filter: blur(2px); }

    /* Modal animation */
    .modal.fade .modal-dialog { transform: scale(0.95) translateY(-10px); transition: transform 0.2s ease; }
    .modal.show .modal-dialog { transform: scale(1) translateY(0); }

    /* ========== Scrollbar ========== */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #f9fafb; }
    ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }

    /* ========== Print ========== */
    @media print {
        .main-sidebar, .main-header, .content-header,
        .add-payment-btn, .dots-btn, .toggle-switch { display: none !important; }
        body, .content-wrapper { background: #fff !important; }
        .main-card { box-shadow: none !important; border: none !important; }
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Keep row on top when dropdown is open ----
    $(document).on('show.bs.dropdown', '.payment-table .dropdown', function () {
        $(this).closest('tr').addClass('dropdown-open');
    });
    $(document).on('hidden.bs.dropdown', '.payment-table .dropdown', function () {
        $(this).closest('tr').removeClass('dropdown-open');
    });

    // ---- Auto-open modal if validation error ----
    @if($errors->any())
        $('#addPaymentModal').modal('show');
    @endif

    // ---- Focus input when modal opens ----
    $('#addPaymentModal').on('shown.bs.modal', function () {
        document.getElementById('addPaymentName').focus();
    });
    $('#editPaymentModal').on('shown.bs.modal', function () {
        document.getElementById('editPaymentName').focus();
    });

    // ---- Edit modal: populate data ----
    document.querySelectorAll('.edit-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const name = this.dataset.name;
            document.getElementById('editPaymentName').value = name;
            document.getElementById('editPaymentForm').action = '{{ route("payment-types.update", ":id") }}'.replace(':id', id);
        });
    });

    // ---- Live search ----
    const allRows = Array.from(document.querySelectorAll('#tableBody tr'));
    let currentPerPage = 10;
    let currentSearch  = '';

    function getFiltered() {
        return allRows.filter(r => r.textContent.toLowerCase().includes(currentSearch));
    }

    function renderRows() {
        const filtered = getFiltered();
        allRows.forEach(r => r.style.display = 'none');
        filtered.slice(0, currentPerPage).forEach(r => r.style.display = '');

        const showing = Math.min(currentPerPage, filtered.length);
        const total   = allRows.length;
        document.getElementById('showingInfo').innerHTML =
            `Showing <strong>1</strong> to <strong>${showing}</strong> of <strong>${total}</strong> entries` +
            (currentSearch ? ` <span style="color:#9ca3af;">(filtered)</span>` : '');
    }

    document.getElementById('tableSearch').addEventListener('input', function () {
        currentSearch = this.value.toLowerCase().trim();
        renderRows();
    });

    window.changePerPage = function (val) {
        currentPerPage = parseInt(val);
        renderRows();
    };

    renderRows();

    // ---- Check all ----
    window.toggleAll = function (source) {
        document.querySelectorAll('.row-check').forEach(c => c.checked = source.checked);
    };
});
</script>
@stop
