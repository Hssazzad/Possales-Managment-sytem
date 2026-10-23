@extends('adminlte::page')

@section('title', 'Suppliers | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <!-- Search Box -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="supplierSearch" placeholder="Search suppliers by name, phone, or company...">
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="exportSuppliers()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button type="button" class="btn-add-new" data-toggle="modal" data-target="#addSupplierModal">
                    <i class="fas fa-plus mr-1"></i> Add New Supplier
                </button>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Email/Phone</th>
                        <th>Company</th>
                        <th class="text-right">Due Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr class="supplier-row">
                        <td>
                            <div class="font-weight-bold">{{ $supplier->name }}</div>
                            @if($supplier->supplier_type)
                                <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $supplier->supplier_type)) }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $supplier->email ?: '-' }}</div>
                            <div class="text-muted small">{{ $supplier->phone }}</div>
                        </td>
                        <td>
                            @if($supplier->company_name)
                                <div class="font-weight-bold">{{ $supplier->company_name }}</div>
                                @if($supplier->city)
                                    <div class="text-muted small"><i class="fas fa-map-marker-alt mr-1"></i>{{ $supplier->city }}</div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold {{ ($supplier->due_amount ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                            ৳{{ number_format($supplier->due_amount ?? 0, 2) }}
                        </td>
                        <td class="text-center">
                            @if($supplier->is_active)
                                <span class="status-badge status-active">Active</span>
                            @else
                                <span class="status-badge status-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <!-- Three Dot Menu -->
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <a class="dropdown-item" href="{{ route('suppliers.show', $supplier) }}">
                                        <i class="fas fa-eye text-primary mr-2"></i> View
                                    </a>
                                    <button class="dropdown-item" onclick="openEditModal({{ $supplier->id }}, '{{ $supplier->name }}', '{{ $supplier->phone }}', '{{ $supplier->email }}', '{{ $supplier->company_name }}', '{{ $supplier->address }}', '{{ $supplier->city }}')">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteSupplier({{ $supplier->id }})">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-truck fa-3x mb-3 text-light"></i>
                                <h5>No Suppliers Found</h5>
                                <p>Start by creating your first supplier.</p>
                                <button type="button" class="btn-add-new mt-2 d-inline-block" data-toggle="modal" data-target="#addSupplierModal">
                                    <i class="fas fa-plus mr-1"></i> Add New Supplier
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Summary -->
        @if($suppliers && $suppliers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                Showing {{ $suppliers->firstItem() }} to {{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers
                <span class="ml-3 badge badge-light border text-primary">Total: ৳{{ number_format($suppliers->sum('total_purchases'), 2) }}</span>
                <span class="ml-2 badge badge-light border text-danger">Due: ৳{{ number_format($suppliers->sum('due_amount'), 2) }}</span>
            </div>
            <div>
                {{ $suppliers->links() }}
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #ff9800; color: white;">
                <h5 class="modal-title" id="addSupplierModalLabel">
                    <i class="fas fa-truck mr-2"></i>Add New Supplier
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSupplierForm">
                @csrf
                <div class="modal-body">
                    <div id="addSupplierErrors" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="add_name">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_phone">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_phone" name="phone" required>
                        <small class="text-muted">Must be unique</small>
                    </div>
                    <div class="form-group">
                        <label for="add_email">Email</label>
                        <input type="email" class="form-control" id="add_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="add_company_name">Company Name</label>
                        <input type="text" class="form-control" id="add_company_name" name="company_name">
                    </div>
                    <div class="form-group">
                        <label for="add_address">Address</label>
                        <textarea class="form-control" id="add_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_city">City</label>
                        <input type="text" class="form-control" id="add_city" name="city">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: #ff9800; color: white;">
                        <i class="fas fa-save mr-1"></i>Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #ff9800; color: white;">
                <h5 class="modal-title" id="editSupplierModalLabel">
                    <i class="fas fa-truck mr-2"></i>Edit Supplier
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSupplierForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_supplier_id" name="supplier_id">
                <div class="modal-body">
                    <div id="editSupplierErrors" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="edit_name">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        <small class="text-muted">Must be unique (except for this supplier)</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit_company_name">Company Name</label>
                        <input type="text" class="form-control" id="edit_company_name" name="company_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_city">City</label>
                        <input type="text" class="form-control" id="edit_city" name="city">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: #ff9800; color: white;">
                        <i class="fas fa-save mr-1"></i>Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
        <div class="toast-header" style="background: #d1fae5; color: #065f46;">
            <i class="fas fa-check-circle mr-2"></i>
            <strong class="mr-auto">Success</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="successToastMessage">
            Operation completed successfully.
        </div>
    </div>
</div>

@stop

@section('adminlte_js')
<script>
// Search functionality (Client-side)
document.getElementById('supplierSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.supplier-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
});

// Export functionality
function exportSuppliers() {
    window.location.href = '/admin/suppliers/export';
}

// Delete functionality
function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
        fetch(`/admin/suppliers/${supplierId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Supplier deleted successfully.');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Could not delete supplier'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting supplier. Please try again.');
        });
    }
}

// Toast notification
function showToast(message) {
    document.getElementById('successToastMessage').textContent = message;
    $('#successToast').toast('show');
}

// Display validation errors
function showErrors(containerId, errors) {
    const container = document.getElementById(containerId);
    let errorHtml = '<ul class="mb-0">';
    for (const field in errors) {
        errors[field].forEach(error => {
            errorHtml += `<li>${error}</li>`;
        });
    }
    errorHtml += '</ul>';
    container.innerHTML = errorHtml;
    container.classList.remove('d-none');
}

// Hide errors
function hideErrors(containerId) {
    document.getElementById(containerId).classList.add('d-none');
}

// Open Edit Modal with supplier data
function openEditModal(id, name, phone, email, companyName, address, city) {
    document.getElementById('edit_supplier_id').value = id;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_phone').value = phone || '';
    document.getElementById('edit_email').value = email || '';
    document.getElementById('edit_company_name').value = companyName || '';
    document.getElementById('edit_address').value = address || '';
    document.getElementById('edit_city').value = city || '';
    hideErrors('editSupplierErrors');
    $('#editSupplierModal').modal('show');
}

// Add Supplier Form Submission
document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors('addSupplierErrors');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('{{ route('suppliers.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#addSupplierModal').modal('hide');
            document.getElementById('addSupplierForm').reset();
            showToast(data.message || 'Supplier created successfully.');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            showErrors('addSupplierErrors', data.errors);
        } else {
            showErrors('addSupplierErrors', { general: [data.message || 'An error occurred'] });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors('addSupplierErrors', { general: ['Network error. Please try again.'] });
    });
});

// Edit Supplier Form Submission
document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors('editSupplierErrors');

    const supplierId = document.getElementById('edit_supplier_id').value;
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch(`/admin/suppliers/${supplierId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#editSupplierModal').modal('hide');
            showToast(data.message || 'Supplier updated successfully.');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            showErrors('editSupplierErrors', data.errors);
        } else {
            showErrors('editSupplierErrors', { general: [data.message || 'An error occurred'] });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors('editSupplierErrors', { general: ['Network error. Please try again.'] });
    });
});

// Clear errors when modal is hidden
$('#addSupplierModal').on('hidden.bs.modal', function() {
    hideErrors('addSupplierErrors');
    document.getElementById('addSupplierForm').reset();
});

$('#editSupplierModal').on('hidden.bs.modal', function() {
    hideErrors('editSupplierErrors');
});
</script>
@stop
