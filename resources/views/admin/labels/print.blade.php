@extends('adminlte::page')

@section('title', 'Barcode Generate | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-barcode mr-2" style="color:#17a2b8;"></i> Barcode Generate
    </h1>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <!-- Product Selection -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-list mr-2"></i>
                    Select Product
                </h5>
            </div>
            <div class="card-body">
                <!-- Search -->
                <form action="{{ route('labels.search') }}" method="GET" class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Products Table -->
                <form id="labelForm" action="{{ route('labels.generate') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Items</th>
                                    <th>Code</th>
                                    <th>Batch</th>
                                    <th>Available Stock</th>
                                    <th>Qty / No of label</th>
                                    <th>Packing Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                                    </td>
                                    <td>{{ $product->code }}</td>
                                    <td>-</td>
                                    <td>{{ $product->stock_quantity }}</td>
                                    <td>
                                        <input type="number" name="products[{{ $loop->index }}][qty]" class="form-control form-control-sm" value="1" min="1" max="100" style="width: 70px;">
                                    </td>
                                    <td>
                                        <input type="date" name="products[{{ $loop->index }}][packing_date]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" onclick="toggleProduct({{ $loop->index }}, this)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <p class="text-muted">No products found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($products && $products->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $products->links() }}
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Label Settings -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-cog mr-2"></i>
                    Information to show in labels
                </h5>
            </div>
            <div class="card-body">
                <form id="settingsForm" action="{{ route('labels.generate') }}" method="POST" target="_blank">
                    @csrf
                    
                    <!-- Hidden field for selected products -->
                    <div id="selectedProducts"></div>

                    <!-- Business Name -->
                    <div class="form-group row">
                        <label class="col-sm-6 col-form-label">Business Name</label>
                        <div class="col-sm-6">
                            <input type="number" name="business_name_size" class="form-control form-control-sm" value="15" min="8" max="20">
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="business_name" class="form-control" value="NBS TRADERS" placeholder="Enter business name">
                    </div>

                    <!-- Product Name -->
                    <div class="form-group row">
                        <label class="col-sm-6 col-form-label">Product Name</label>
                        <div class="col-sm-6">
                            <input type="number" name="product_name_size" class="form-control form-control-sm" value="15" min="8" max="20">
                        </div>
                    </div>

                    <!-- Product Price -->
                    <div class="form-group row">
                        <label class="col-sm-6 col-form-label">Product Price</label>
                        <div class="col-sm-6">
                            <input type="number" name="price_size" class="form-control form-control-sm" value="14" min="8" max="20">
                        </div>
                    </div>

                    <!-- Product Code -->
                    <div class="form-group row">
                        <label class="col-sm-6 col-form-label">Product Code</label>
                        <div class="col-sm-6">
                            <input type="number" name="code_size" class="form-control form-control-sm" value="14" min="8" max="20">
                        </div>
                    </div>

                    <!-- Print packing date -->
                    <div class="form-group row">
                        <label class="col-sm-6 col-form-label">Print packing date</label>
                        <div class="col-sm-6">
                            <input type="number" name="date_size" class="form-control form-control-sm" value="12" min="8" max="20">
                        </div>
                    </div>

                    <!-- Show Price Toggle -->
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="show_price" id="show_price" value="1" checked>
                            <label class="form-check-label" for="show_price">Show Price</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="inc_tax" id="inc_tax" value="1">
                            <label class="form-check-label" for="inc_tax">Inc. tax</label>
                        </div>
                    </div>

                    <!-- Barcode Type -->
                    <div class="form-group">
                        <label for="barcode_type">Barcode Type *</label>
                        <select name="barcode_type" id="barcode_type" class="form-control" required>
                            <option value="C128" selected>C128</option>
                            <option value="C39">C39</option>
                            <option value="EAN13">EAN13</option>
                        </select>
                    </div>

                    <!-- Barcode Print Paper Setting -->
                    <div class="form-group">
                        <label for="paper_size">Barcode Print Paper Setting</label>
                        <select name="paper_size" id="paper_size" class="form-control">
                            <option value="Labels Roll-Label Size 2&quot;x1&quot;, 50mmx25mm, Gap:3.1mm" selected>
                                Labels Roll-Label Size 2"x1", 50mmx25mm, Gap:3.1mm
                            </option>
                            <option value="Labels Roll-Label Size 2&quot;x0.5&quot;, 50mmx12.7mm, Gap:3.1mm">
                                Labels Roll-Label Size 2"x0.5", 50mmx12.7mm, Gap:3.1mm
                            </option>
                            <option value="A4 Sheet - 30 Labels per sheet">
                                A4 Sheet - 30 Labels per sheet
                            </option>
                        </select>
                    </div>

                    <!-- Preview & Print Buttons -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-info btn-block" onclick="return validateAndSubmit('preview')">
                            <i class="fas fa-eye mr-2"></i> Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
let selectedProducts = [];

function toggleProduct(index, btn) {
    const row = btn.closest('tr');
    const inputs = row.querySelectorAll('input');
    const productId = inputs[0].value;
    const qty = inputs[1].value;
    const packingDate = inputs[2].value;

    if (btn.classList.contains('btn-success')) {
        // Add to selection
        btn.classList.remove('btn-success');
        btn.classList.add('btn-danger');
        btn.innerHTML = '<i class="fas fa-minus"></i>';
        row.style.backgroundColor = '#d4edda';
        
        selectedProducts.push({
            index: index,
            id: productId,
            qty: qty,
            packing_date: packingDate
        });
    } else {
        // Remove from selection
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="fas fa-plus"></i>';
        row.style.backgroundColor = '';
        
        selectedProducts = selectedProducts.filter(p => p.index !== index);
    }

    updateSelectedProductsInput();
}

function updateSelectedProductsInput() {
    const container = document.getElementById('selectedProducts');
    container.innerHTML = '';
    
    selectedProducts.forEach((product, idx) => {
        container.innerHTML += `
            <input type="hidden" name="products[${idx}][id]" value="${product.id}">
            <input type="hidden" name="products[${idx}][qty]" value="${product.qty}">
            <input type="hidden" name="products[${idx}][packing_date]" value="${product.packing_date}">
        `;
    });
}

function validateAndSubmit(action) {
    if (selectedProducts.length === 0) {
        alert('Please select at least one product');
        return false;
    }
    return true;
}
</script>
@stop

@section('adminlte_css')
<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
}
.col-form-label {
    font-size: 0.9rem;
    padding-top: calc(0.375rem + 1px);
}
</style>
@stop
