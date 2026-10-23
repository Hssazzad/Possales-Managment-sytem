@extends('adminlte::page')

@section('title')
    Income Categories | {{ config('app.name') }}
@stop

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Categories</p>
                                <h3 class="mb-0 font-weight-bold">{{ $categories->count() }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tags text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Income</p>
                                <h3 class="mb-0 font-weight-bold">৳{{ number_format($categories->sum('total_income'), 2) }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-money-bill-wave text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size: 0.85rem; font-weight: 500;">Total Transactions</p>
                                <h3 class="mb-0 font-weight-bold">{{ $categories->sum('transaction_count') }}</h3>
                            </div>
                            <div class="icon-wrapper" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exchange-alt text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Summary Cards -->
        <div class="row mb-4">
            @foreach($categories as $category)
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div style="width: 16px; height: 16px; border-radius: 50%; background: {{ $category['color'] }}; margin-right: 12px;"></div>
                            <h5 class="mb-0 font-weight-bold">{{ $category['name'] }}</h5>
                        </div>
                        <p class="text-muted small mb-3">{{ $category['description'] ?: 'No description' }}</p>
                        <div class="d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid #e5e7eb;">
                            <span class="text-muted">Income:</span>
                            <span class="font-weight-bold text-success">৳{{ number_format($category['total_income'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted">Transactions:</span>
                            <span class="font-weight-bold">{{ $category['transaction_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <!-- Search Box -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="categorySearch" placeholder="Search categories...">
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('income.index') }}" class="btn-export">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Income
                </a>
                <button onclick="exportToCSV()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button type="button" class="btn-add-new" data-toggle="modal" data-target="#addCategoryModal">
                    <i class="fas fa-plus mr-1"></i> Add Category
                </button>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Total Income</th>
                        <th class="text-center">Transactions</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="category-row">
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $category['color'] }}; margin-right: 10px;"></div>
                                <span class="font-weight-bold">{{ $category['name'] }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $category['description'] ?: '-' }}</span>
                        </td>
                        <td class="text-right font-weight-bold text-success">
                            ৳{{ number_format($category['total_income'], 2) }}
                        </td>
                        <td class="text-center">
                            <span class="variation-tag">{{ $category['transaction_count'] }}</span>
                        </td>
                        <td class="text-center">
                            <!-- Three Dot Menu -->
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <button class="dropdown-item" onclick='openEditModal({{ $category['id'] }}, @json($category['name']), @json($category['description'] ?? ""), @json($category['color']))'>
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('income.categories.destroy', $category['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this income category?')">
                                            <i class="fas fa-trash-alt mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-tags fa-3x mb-3 text-light"></i>
                                <h5>No Categories Found</h5>
                                <p>Start by creating your first income category.</p>
                                <button type="button" class="btn-add-new mt-2 d-inline-block" data-toggle="modal" data-target="#addCategoryModal">
                                    <i class="fas fa-plus mr-1"></i> Add Category
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Footer -->
        @if($categories->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="text-muted text-sm">
                <span class="mr-3"><i class="fas fa-tags text-warning mr-1"></i> Categories: <strong>{{ $categories->count() }}</strong></span>
                <span class="mr-3"><i class="fas fa-money-bill-wave text-success mr-1"></i> Total Income: <strong>৳{{ number_format($categories->sum('total_income'), 2) }}</strong></span>
                <span><i class="fas fa-exchange-alt text-primary mr-1"></i> Transactions: <strong>{{ $categories->sum('transaction_count') }}</strong></span>
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #ff9800; color: white;">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <i class="fas fa-tags mr-2"></i>Add New Category
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div id="addCategoryErrors" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="add_name">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_description">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_color">Color <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center">
                            <input type="color" class="form-control" id="add_color" name="color" value="#ff9800" style="width: 60px; height: 40px; padding: 2px;">
                            <span class="ml-3 text-muted">Select a color for this category</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="add_is_active" name="is_active" checked>
                            <label class="custom-control-label" for="add_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: #ff9800; color: white;">
                        <i class="fas fa-save mr-1"></i>Save Category
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #ff9800; color: white;">
                <h5 class="modal-title" id="editCategoryModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Category
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-body">
                    <div id="editCategoryErrors" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="edit_name">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_color">Color <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center">
                            <input type="color" class="form-control" id="edit_color" name="color" style="width: 60px; height: 40px; padding: 2px;">
                            <span class="ml-3 text-muted">Select a color for this category</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active">
                            <label class="custom-control-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: #ff9800; color: white;">
                        <i class="fas fa-save mr-1"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('adminlte_js')
<script>
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

// Search functionality
document.getElementById('categorySearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.category-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
});

// Add Category Form Submission
document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors('addCategoryErrors');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.is_active = document.getElementById('add_is_active').checked;

    fetch('{{ route('income.categories.store') }}', {
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
            $('#addCategoryModal').modal('hide');
            document.getElementById('addCategoryForm').reset();
            showToast(data.message || 'Category created successfully.');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            showErrors('addCategoryErrors', data.errors);
        } else {
            showErrors('addCategoryErrors', { general: [data.message || 'An error occurred'] });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors('addCategoryErrors', { general: ['Network error. Please try again.'] });
    });
});

// Open Edit Modal with category data
function openEditModal(id, name, description, color) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_color').value = color || '#ff9800';
    document.getElementById('edit_is_active').checked = true;
    hideErrors('editCategoryErrors');
    $('#editCategoryModal').modal('show');
}

// Edit Category Form Submission
document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors('editCategoryErrors');

    const categoryId = document.getElementById('edit_category_id').value;
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.is_active = document.getElementById('edit_is_active').checked;

    fetch(`/admin/income/categories/${categoryId}`, {
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
            $('#editCategoryModal').modal('hide');
            showToast(data.message || 'Category updated successfully.');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            showErrors('editCategoryErrors', data.errors);
        } else {
            showErrors('editCategoryErrors', { general: [data.message || 'An error occurred'] });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors('editCategoryErrors', { general: ['Network error. Please try again.'] });
    });
});

// Clear errors when modal is hidden
$('#addCategoryModal').on('hidden.bs.modal', function() {
    hideErrors('addCategoryErrors');
    document.getElementById('addCategoryForm').reset();
});

$('#editCategoryModal').on('hidden.bs.modal', function() {
    hideErrors('editCategoryErrors');
});

// Export to CSV
function exportToCSV() {
    const categories = @json($categories);

    if (!categories || categories.length === 0) {
        alert('No data available to export');
        return;
    }

    let csv = 'Category,Description,Total Income,Transactions\n';

    categories.forEach((category) => {
        const name = (category.name || '').replace(/"/g, '""');
        const description = (category.description || '').replace(/"/g, '""');
        const income = category.total_income || 0;
        const transactions = category.transaction_count || 0;

        csv += `"${name}","${description}","${income}","${transactions}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'income_categories_' + new Date().toISOString().slice(0,10) + '.csv';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
@stop

