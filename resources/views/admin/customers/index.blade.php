@extends('adminlte::page')

@section('title', 'Customers | PosSales')

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
                <input type="text" id="customerSearch" placeholder="Search customers by name, phone, or email...">
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="exportCustomers()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button type="button" class="btn-add-new" data-toggle="modal" data-target="#addCustomerModal">
                    <i class="fas fa-plus mr-1"></i> Add New Customer
                </button>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Email/Phone</th>
                        <th class="text-right">Total Orders</th>
                        <th class="text-right">Total Paid</th>
                        <th class="text-right">Due</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr class="customer-row">
                        <td>
                            <div class="font-weight-bold">{{ $customer->name }}</div>
                            @if($customer->address || $customer->city)
                                <div class="text-muted small">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $customer->address }}{{ $customer->address && $customer->city ? ', ' : '' }}{{ $customer->city }}
                                </div>
                            @elseif($customer->party_type)
                                <div class="text-muted small">{{ ucfirst($customer->party_type) }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $customer->email ?: '-' }}</div>
                            <div class="text-muted small">{{ $customer->phone }}</div>
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ $customer->sales->count() }}
                        </td>
                        <td class="text-right text-success font-weight-bold">
                            {{ $customer->formatted_total_purchases }}
                        </td>
                        <td class="text-right font-weight-bold {{ $customer->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $customer->formatted_due_amount }}
                        </td>
                        <td class="text-center">
                            @if($customer->is_active)
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
                                    <a class="dropdown-item" href="{{ route('customers.show', $customer) }}">
                                        <i class="fas fa-eye text-primary mr-2"></i> View
                                    </a>
                                    <button class="dropdown-item" onclick="openEditModal({{ $customer->id }}, '{{ $customer->name }}', '{{ $customer->phone }}', '{{ $customer->email }}', '{{ $customer->address }}', '{{ $customer->city }}')">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteCustomer({{ $customer->id }})">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3 text-light"></i>
                                <h5>No Customers Found</h5>
                                <p>Start by creating your first customer.</p>
                                <a href="{{ route('customers.create') }}" class="btn-add-new mt-2 d-inline-block">
                                    <i class="fas fa-plus mr-1"></i> Add New Customer
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Summary -->
        @if($customers && $customers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers
                <span class="ml-3 badge badge-light border text-primary">Total: {{ number_format($customers->sum('total_purchases'), 2) }}</span>
                <span class="ml-2 badge badge-light border text-danger">Due: {{ number_format($customers->sum('due_amount'), 2) }}</span>
            </div>
            <div>
                {{ $customers->links() }}
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #ff9800; color: white;">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="fas fa-user-plus mr-2"></i>Add New Customer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCustomerForm">
                @csrf
                <div class="modal-body">
                    <div id="addCustomerErrors" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="add_name">Customer Name <span class="text-danger">*</span></label>
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
                        <i class="fas fa-save mr-1"></i>Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #ff9800; color: white;">
                <h5 class="modal-title" id="editCustomerModalLabel">
                    <i class="fas fa-user-edit mr-2"></i>Edit Customer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCustomerForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_customer_id" name="customer_id">
                <div class="modal-body">
                    <div id="editCustomerErrors" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="edit_name">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        <small class="text-muted">Must be unique (except for this customer)</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
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
                        <i class="fas fa-save mr-1"></i>Update Customer
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
document.getElementById('customerSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.customer-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
});

// Export functionality
function exportCustomers() {
    window.location.href = '/admin/customers/export';
}

// Delete functionality
function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        fetch(`/admin/customers/${customerId}`, {
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
                showToast('Customer deleted successfully.');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Could not delete customer'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting customer. Please try again.');
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

// Open Edit Modal with customer data
function openEditModal(id, name, phone, email, address, city) {
    document.getElementById('edit_customer_id').value = id;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_phone').value = phone || '';
    document.getElementById('edit_email').value = email || '';
    document.getElementById('edit_address').value = address || '';
    document.getElementById('edit_city').value = city || '';
    hideErrors('editCustomerErrors');
    $('#editCustomerModal').modal('show');
}

// Add Customer Form Submission
document.getElementById('addCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors('addCustomerErrors');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('{{ route('customers.store') }}', {
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
            $('#addCustomerModal').modal('hide');
            document.getElementById('addCustomerForm').reset();
            showToast(data.message || 'Customer created successfully.');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            showErrors('addCustomerErrors', data.errors);
        } else {
            showErrors('addCustomerErrors', { general: [data.message || 'An error occurred'] });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors('addCustomerErrors', { general: ['Network error. Please try again.'] });
    });
});

// Edit Customer Form Submission
document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors('editCustomerErrors');

    const customerId = document.getElementById('edit_customer_id').value;
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch(`/admin/customers/${customerId}`, {
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
            $('#editCustomerModal').modal('hide');
            showToast(data.message || 'Customer updated successfully.');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            showErrors('editCustomerErrors', data.errors);
        } else {
            showErrors('editCustomerErrors', { general: [data.message || 'An error occurred'] });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors('editCustomerErrors', { general: ['Network error. Please try again.'] });
    });
});

// Clear errors when modal is hidden
$('#addCustomerModal').on('hidden.bs.modal', function() {
    hideErrors('addCustomerErrors');
    document.getElementById('addCustomerForm').reset();
});

$('#editCustomerModal').on('hidden.bs.modal', function() {
    hideErrors('editCustomerErrors');
});
</script>
@stop
