@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-4">
                <i class="fas fa-file-invoice-dollar"></i> Create Invoice
            </h3>
        </div>
    </div>

    <div class="row">
        <!-- LEFT PANEL: Select Investigations -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> Select Investigations
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Admission Type Dropdown -->
                    <div class="form-group mb-3">
                        <label for="mainCategory" class="form-label">
                            <strong>Admission Type</strong>
                        </label>
                        <select class="form-control form-control-lg" id="mainCategory">
                            <option value="">-- Select Admission Type --</option>
                        </select>
                        <small class="form-text text-muted">Select a main category to view investigations</small>
                    </div>

                    <!-- Sub Categories (Investigations) -->
                    <div class="form-group mb-3" id="subCategoriesContainer" style="display: none;">
                        <label><strong>Investigations</strong></label>
                        <div id="subCategoriesList" class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            <!-- Checkboxes will be loaded here -->
                        </div>
                    </div>

                    <!-- Add Button -->
                    <div class="form-group">
                        <button type="button" class="btn btn-success btn-block btn-lg" id="addInvestigationsBtn" disabled>
                            <i class="fas fa-plus"></i> Add Selected
                        </button>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" style="display: none;" class="text-center mt-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading investigations...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Selected Items & Patient Info -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt"></i> Invoice Summary
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Patient Search -->
                    <div class="form-group mb-3">
                        <label for="patientSearch" class="form-label">
                            <strong>Search Patient</strong>
                        </label>
                        <input type="text" class="form-control form-control-lg"
                               id="patientSearch" placeholder="Enter patient code or name...">
                        <div id="patientSearchResults" class="list-group mt-2" style="display: none; position: absolute; width: 95%; z-index: 1000;">
                            <!-- Results will appear here -->
                        </div>
                    </div>

                    <!-- Selected Patient Details -->
                    <div id="patientDetailsContainer" style="display: none;" class="mb-4 p-3 bg-light rounded border">
                        <h6><strong>Patient Details</strong></h6>
                        <p class="mb-1"><strong>Name:</strong> <span id="patientName"></span></p>
                        <p class="mb-1"><strong>Code:</strong> <span id="patientCode"></span></p>
                        <p class="mb-0"><strong>Contact:</strong> <span id="patientContact"></span></p>
                    </div>

                    <!-- Selected Items -->
                    <div class="form-group mb-3">
                        <label><strong>Selected Investigations</strong></label>
                        <div id="selectedItemsList" class="border rounded p-3" style="max-height: 300px; overflow-y: auto; min-height: 100px;">
                            <p class="text-muted text-center">No items selected yet</p>
                        </div>
                    </div>

                    <!-- Amount Calculation -->
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="totalAmount" class="form-label"><strong>Total</strong></label>
                                <input type="text" class="form-control form-control-lg"
                                       id="totalAmount" value="0" readonly style="background-color: #f0f0f0;">
                            </div>
                            <div class="col-md-6">
                                <label for="paidAmount" class="form-label"><strong>Paid Amount</strong></label>
                                <input type="number" class="form-control form-control-lg"
                                       id="paidAmount" value="0" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Due Amount -->
                    <div class="form-group mb-4">
                        <div class="alert alert-warning" id="dueAmountContainer" style="display: none;">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle"></i> Due Amount
                            </h5>
                            <p class="mb-0"><strong>Amount Due: <span id="dueAmount">0</span></strong></p>
                        </div>
                    </div>

                    <!-- Generate Invoice Button -->
                    <button type="button" class="btn btn-primary btn-block btn-lg"
                            id="generateInvoiceBtn" disabled>
                        <i class="fas fa-check-circle"></i> Generate Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Invoice Generated
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Bill Number:</strong> <span id="billNumberDisplay"></span></p>
                <p class="mb-2"><strong>Total Amount:</strong> <span id="totalDisplay"></span></p>
                <p class="mb-2"><strong>Paid Amount:</strong> <span id="paidDisplay"></span></p>
                <p class="mb-0"><strong>Due Amount:</strong> <span id="dueDisplay"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="printInvoiceBtn" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print"></i> Print Invoice
                </a>
                <button type="button" class="btn btn-success" id="newInvoiceBtn">
                    <i class="fas fa-plus"></i> New Invoice
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    let selectedPatientCode = null;
    let selectedItems = [];

    // ==================== MAIN CATEGORY DROPDOWN ====================
    function loadMainCategories() {
        $.ajax({
            url: '{{ route("invoice.getMain") }}',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#mainCategory');
                    select.find('option').not(':first').remove();

                    response.data.forEach(category => {
                        select.append(
                            `<option value="${category.code}">${category.name}</option>`
                        );
                    });
                }
            },
            error: function() {
                showAlert('Failed to load admission types', 'danger');
            }
        });
    }

    // ==================== SUB CATEGORIES (INVESTIGATIONS) ====================
    $('#mainCategory').on('change', function() {
        const mainCode = $(this).val();

        if (!mainCode) {
            $('#subCategoriesContainer').hide();
            $('#subCategoriesList').html('');
            $('#addInvestigationsBtn').prop('disabled', true);
            return;
        }

        $('#loadingSpinner').show();
        $('#subCategoriesList').html('');

        $.ajax({
            url: '{{ route("invoice.getSub") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                main_code: mainCode
            },
            success: function(response) {
                $('#loadingSpinner').hide();

                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(sub => {
                        html += `
                            <div class="form-check">
                                <input class="form-check-input sub-checkbox" type="checkbox"
                                       value="${sub.code}" data-name="${sub.name}"
                                       data-amount="${sub.amount}" id="sub_${sub.code}">
                                <label class="form-check-label w-100" for="sub_${sub.code}">
                                    <strong>${sub.name}</strong>
                                    <span class="float-right badge badge-primary">
                                        $${parseFloat(sub.amount).toFixed(2)}
                                    </span>
                                </label>
                            </div>
                        `;
                    });

                    $('#subCategoriesList').html(html);
                    $('#subCategoriesContainer').show();
                    $('#addInvestigationsBtn').prop('disabled', false);
                } else {
                    $('#subCategoriesContainer').hide();
                    showAlert('No investigations found for this type', 'info');
                }
            },
            error: function() {
                $('#loadingSpinner').hide();
                showAlert('Failed to load investigations', 'danger');
            }
        });
    });

    // ==================== ADD SELECTED INVESTIGATIONS ====================
    $('#addInvestigationsBtn').on('click', function() {
        if (!selectedPatientCode) {
            showAlert('Please select a patient first', 'warning');
            return;
        }

        const selectedSubs = $('.sub-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedSubs.length === 0) {
            showAlert('Please select at least one investigation', 'warning');
            return;
        }

        const mainCode = $('#mainCategory').val();

        $.ajax({
            url: '{{ route("invoice.addTmp") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                main_code: mainCode,
                patient_code: selectedPatientCode,
                sub_codes: selectedSubs
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');

                    // Uncheck all checkboxes
                    $('.sub-checkbox').prop('checked', false);

                    // Reload temporary items
                    loadTmpItems();
                } else {
                    showAlert(response.message || 'Failed to add items', 'danger');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                showAlert('Failed to add items: ' + Object.values(errors).flat().join(', '), 'danger');
            }
        });
    });

    // ==================== PATIENT SEARCH ====================
    let searchTimeout;
    $('#patientSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 1) {
            $('#patientSearchResults').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("invoice.searchPatients") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    q: query
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(patient => {
                            html += `
                                <a href="#" class="list-group-item list-group-item-action patient-option"
                                   data-code="${patient.code}">
                                    <strong>${patient.name}</strong><br>
                                    <small class="text-muted">Code: ${patient.code}</small>
                                </a>
                            `;
                        });

                        $('#patientSearchResults').html(html).show();
                    } else {
                        $('#patientSearchResults').html(
                            '<div class="list-group-item text-muted">No patients found</div>'
                        ).show();
                    }
                },
                error: function() {
                    showAlert('Failed to search patients', 'danger');
                }
            });
        }, 300);
    });

    // ==================== SELECT PATIENT ====================
    $(document).on('click', '.patient-option', function(e) {
        e.preventDefault();

        const patientCode = $(this).data('code');
        selectPatient(patientCode);
    });

    function selectPatient(patientCode) {
        selectedPatientCode = patientCode;

        $.ajax({
            url: '{{ route("invoice.getPatientDetails") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                patient_code: patientCode
            },
            success: function(response) {
                if (response.success) {
                    const patient = response.data;

                    $('#patientName').text(patient.name);
                    $('#patientCode').text(patient.code);
                    $('#patientContact').text(patient.phone || patient.email || 'N/A');
                    $('#patientDetailsContainer').show();
                    $('#patientSearchResults').hide();
                    $('#patientSearch').val(patient.name);

                    // Load existing temporary items
                    loadTmpItems();
                    $('#generateInvoiceBtn').prop('disabled', false);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to load patient details', 'danger');
            }
        });
    }

    // ==================== LOAD TEMPORARY ITEMS ====================
    function loadTmpItems() {
        if (!selectedPatientCode) {
            return;
        }

        $.ajax({
            url: '{{ route("invoice.getTmp") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                patient_code: selectedPatientCode
            },
            success: function(response) {
                if (response.success) {
                    selectedItems = response.data;
                    displayTmpItems(response.data);
                    updateAmounts(response.total);
                }
            },
            error: function() {
                showAlert('Failed to load items', 'danger');
            }
        });
    }

    function displayTmpItems(items) {
        if (items.length === 0) {
            $('#selectedItemsList').html(
                '<p class="text-muted text-center">No items selected yet</p>'
            );
            return;
        }

        let html = '<ul class="list-group list-group-flush">';
        items.forEach(item => {
            html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">$${parseFloat(item.amount).toFixed(2)}</small>
                    </div>
                    <button class="btn btn-sm btn-danger remove-item" data-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </li>
            `;
        });
        html += '</ul>';

        $('#selectedItemsList').html(html);
    }

    // ==================== REMOVE ITEM ====================
    $(document).on('click', '.remove-item', function() {
        const itemId = $(this).data('id');

        if (confirm('Are you sure you want to remove this item?')) {
            $.ajax({
                url: '{{ route("invoice.removeTmp") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    id: itemId
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        loadTmpItems();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Failed to remove item', 'danger');
                }
            });
        }
    });

    // ==================== CALCULATE AMOUNTS ====================
    function updateAmounts(total) {
        const total_num = parseFloat(total) || 0;
        const paid = parseFloat($('#paidAmount').val()) || 0;
        const due = total_num - paid;

        $('#totalAmount').val(total_num.toFixed(2));

        if (due > 0) {
            $('#dueAmount').text(due.toFixed(2));
            $('#dueAmountContainer').show();
        } else {
            $('#dueAmountContainer').hide();
        }
    }

    $('#paidAmount').on('change', function() {
        const total = parseFloat($('#totalAmount').val()) || 0;
        updateAmounts(total);
    });

    // ==================== GENERATE INVOICE ====================
    $('#generateInvoiceBtn').on('click', function() {
        if (!selectedPatientCode) {
            showAlert('Please select a patient', 'warning');
            return;
        }

        if (selectedItems.length === 0) {
            showAlert('Please add at least one investigation', 'warning');
            return;
        }

        const paidAmount = $('#paidAmount').val();

        if (paidAmount === '' || parseFloat(paidAmount) < 0) {
            showAlert('Please enter a valid paid amount', 'warning');
            return;
        }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span>Processing...');

        $.ajax({
            url: '{{ route("invoice.store") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                patient_code: selectedPatientCode,
                paid_amount: paidAmount
            },
            success: function(response) {
                $('#generateInvoiceBtn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Generate Invoice');

                if (response.success) {
                    // Display success modal
                    $('#billNumberDisplay').text(response.bill_no);
                    $('#totalDisplay').text('$' + parseFloat(response.total).toFixed(2));
                    $('#paidDisplay').text('$' + parseFloat(response.paid).toFixed(2));
                    $('#dueDisplay').text('$' + parseFloat(response.due).toFixed(2));
                    $('#printInvoiceBtn').attr('href', '/invoice/print/' + response.bill_no);

                    $('#successModal').modal('show');
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function(xhr) {
                $('#generateInvoiceBtn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Generate Invoice');

                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    showAlert('Failed: ' + Object.values(errors).flat().join(', '), 'danger');
                } else {
                    showAlert('Failed to generate invoice', 'danger');
                }
            }
        });
    });

    // ==================== NEW INVOICE ====================
    $('#newInvoiceBtn').on('click', function() {
        // Reset form
        selectedPatientCode = null;
        selectedItems = [];

        $('#mainCategory').val('').trigger('change');
        $('#patientSearch').val('');
        $('#patientDetailsContainer').hide();
        $('#paidAmount').val('0');
        $('#totalAmount').val('0');
        $('#dueAmountContainer').hide();
        $('#generateInvoiceBtn').prop('disabled', true);

        $('#selectedItemsList').html('<p class="text-muted text-center">No items selected yet</p>');
        $('#successModal').modal('hide');
    });

    // ==================== HELPER FUNCTIONS ====================
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        // Add alert to top of page
        $('main').prepend(alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // ==================== INITIALIZATION ====================
    loadMainCategories();
});
</script>
@endsection

@section('styles')
<style>
    .form-check-label {
        cursor: pointer;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .form-check-label:last-child {
        border-bottom: none;
    }

    .list-group-item {
        border-left: 3px solid #007bff;
    }

    #patientSearchResults {
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        border-radius: 4px;
    }

    .card-header {
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    #subCategoriesList::-webkit-scrollbar {
        width: 6px;
    }

    #subCategoriesList::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #subCategoriesList::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    #subCategoriesList::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endsection
