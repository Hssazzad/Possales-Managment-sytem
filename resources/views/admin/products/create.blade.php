@extends('adminlte::page')

@section('title', 'Register New Product | ClothBD')

@section('adminlte_css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .content-wrapper { background: #f4f7fa !important; }
    .select2-container--default .select2-selection--single { height:calc(2.25rem + 2px) !important; border:1px solid #ced4da !important; border-radius:0.25rem !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:2.25rem !important; font-size:0.875rem !important; padding-left:0.75rem !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:2.25rem !important; }
    .select2-container { width:100% !important; }
    .input-purchase { background:#fefce8 !important; }
    .input-sell { background:#f0fdf4 !important; border-color:#28a745 !important; color:#155724 !important; font-weight:700 !important; }
    .input-barcode { background:#f0f4ff !important; font-weight:bold !important; }
    .img-upload-zone { border:2px dashed #adb5bd; border-radius:0.25rem; display:flex; align-items:center; justify-content:center; cursor:pointer; background:#f8f9fa; font-size:0.875rem; color:#6c757d; position:relative; overflow:hidden; padding:1.5rem; text-align:center; }
    #previewImg { max-width:100%; max-height:120px; display:none; border-radius:0.25rem; margin-top:0.5rem; }
    .sticky-footer { position:sticky; bottom:0; background:#fff; border-top:2px solid #dee2e6; padding:0.75rem 1rem; z-index:100; }
    .variation-table { font-size:0.875rem; }
    .variation-table th { background:#f8f9fa; border-bottom:2px solid #dee2e6; white-space:nowrap; }
    .variation-table td { vertical-align:middle; }
    .variation-barcode { font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.78rem; letter-spacing:.03em; white-space:nowrap; }
    .btn-remove-var { color:#dc3545; background:transparent; border:none; cursor:pointer; }
    .btn-remove-var:hover { color:#bd2130; }
    .card-outline.card-navy .card-header { background:linear-gradient(135deg, #001f3f, #003366); color:#fff; }
    .card-outline.card-olive .card-header { background:linear-gradient(135deg, #3d9970, #2d6b50); color:#fff; }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-left" style="background:transparent; padding:0; margin:0;">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Product Registry</a></li>
            <li class="breadcrumb-item active">Register New Product</li>
        </ol>
    </div>
    <div class="col-sm-6 text-right">
        <a href="{{ route('products.index') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Failed to save product:</strong>
        {{ session('error') }}
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><i class="fas fa-check-circle mr-1"></i></strong>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Please fix the following errors:</strong>
        <ul class="mb-0 mt-1 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="productCreateForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="purchase_price" id="product_purchase_price" value="">
        <input type="hidden" name="sale_price" id="product_sale_price" value="">
        <input type="hidden" name="barcode" id="product_barcode" value="{{ old('barcode', $nextBarcode ?? 'BB250000001') }}">

        <div class="row">
            {{-- Main Column --}}
            <div class="col-md-8">
                {{-- Primary Information Card --}}
                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-box mr-2"></i> Primary Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Product Title <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name') }}" placeholder="e.g. Dell Laptop Inspiron" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Product Code</label>
                                    <input type="text" name="code" class="form-control form-control-sm" value="{{ $nextCode }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control form-control-sm select2">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <select name="brand_id" class="form-control form-control-sm select2">
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" id="supplier_id" class="form-control form-control-sm select2">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->company_name ? $supplier->company_name . ' - ' . $supplier->name : $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Base Unit <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-control form-control-sm select2">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Variation & Stock Card --}}
                <div class="card card-outline card-olive">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i> Variation & Stock</h3>
                    </div>
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Color</label>
                                            <select id="v_color" class="form-control form-control-sm select2">
                                                <option value="">Select Color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Size / Model</label>
                                            <select id="v_size" class="form-control form-control-sm select2">
                                                <option value="">Select Size</option>
                                                @foreach($sizes as $size)
                                                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Purchase Price <span class="text-danger">*</span></label>
                                            <input type="number" id="v_purchase_price" step="0.01" min="0" class="form-control form-control-sm input-purchase" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Selling Price <span class="text-danger">*</span></label>
                                            <input type="number" id="v_price" step="0.01" min="0" class="form-control form-control-sm input-sell" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Opening Stock</label>
                                            <input type="number" id="v_stock" value="0" min="0" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total Stock</label>
                                            <input type="number" name="stock_quantity" id="total_stock_display" value="0" readonly class="form-control form-control-sm" style="background:#e9ecef; font-weight:bold;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" style="padding-top:24px;">
                                            <button type="button" id="addVariation" class="btn btn-success btn-sm btn-block">
                                                <i class="fas fa-plus mr-1"></i> Add Variation
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height:220px; overflow-y:auto; border:1px solid #dee2e6; border-radius:0.25rem;">
                            <table class="table table-sm table-striped variation-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Buy Price</th>
                                        <th>Sell Price</th>
                                        <th>Barcode</th>
                                        <th width="100">Stock</th>
                                        <th width="50" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="variationBody">
                                    <tr id="emptyVariation">
                                        <td colspan="7" class="text-center text-muted py-3">No variations added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="col-md-4">
                {{-- Product Image Card --}}
                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image mr-2"></i> Product Image</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="img-upload-zone" onclick="document.getElementById('imageInput').click()">
                            <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;">
                            <span id="uploadText"><i class="fas fa-cloud-upload-alt fa-2x d-block mb-1"></i> Drag & drop or click to upload</span>
                        </div>
                        <img id="previewImg">
                    </div>
                </div>

                {{-- Storage & Tax Card --}}
                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog mr-2"></i> Storage & Tax</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Rack Location</label>
                            <select name="rack_id" class="form-control form-control-sm select2">
                                <option value="">N/A</option>
                                @foreach($racks as $rack)
                                    <option value="{{ $rack->id }}" {{ old('rack_id') == $rack->id ? 'selected' : '' }}>{{ $rack->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shelf Location</label>
                            <select name="shelf_id" class="form-control form-control-sm select2">
                                <option value="">N/A</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}" {{ old('shelf_id') == $shelf->id ? 'selected' : '' }}>{{ $shelf->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tax (%)</label>
                            <input type="number" name="tax_rate" step="0.01" class="form-control form-control-sm" value="{{ old('tax_rate', '0') }}">
                        </div>
                        <div class="form-group">
                            <label>Min Stock Alert</label>
                            <input type="number" name="min_stock_level" class="form-control form-control-sm" value="{{ old('min_stock_level', '5') }}">
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active Status</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky Footer --}}
        <div class="sticky-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted"><span class="text-danger">*</span> Marked fields are mandatory.</small>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('products.index') }}" class="btn btn-default mr-2">
                        <i class="fas fa-times mr-1"></i> Discard
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Add Product
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });

    let variationIndex = 0;
    let currentPurchasePrice = 0;
    let currentSellPrice = 0;

    $('#v_purchase_price').on('input change', function() {
        currentPurchasePrice = parseFloat($(this).val()) || 0;
    });

    $('#v_price').on('input change', function() {
        currentSellPrice = parseFloat($(this).val()) || 0;
    });

    function calculateTotals() {
        let totalStock = 0;
        $('.variation-stock-input').each(function() {
            totalStock += parseInt($(this).val()) || 0;
        });
        $('#total_stock_display').val(totalStock);
    }

    $('#addVariation').on('click', function() {
        const purchasePrice = currentPurchasePrice;
        const sellPrice = currentSellPrice;
        const colorId = $('#v_color').val();
        const colorName = $('#v_color option:selected').text();
        const sizeId = $('#v_size').val();
        const sizeName = $('#v_size option:selected').text();
        const stock = parseInt($('#v_stock').val()) || 0;
        if (purchasePrice <= 0) { alert('Please enter Purchase Price.'); return; }
        if (sellPrice <= 0) { alert('Please enter Selling Price.'); return; }
        if (!colorId && !sizeId) { alert('Please select at least a Color or a Size.'); return; }
        if (stock < 0) { alert('Stock cannot be negative.'); return; }

        var isDuplicate = false;
        $('#variationBody tr:not(#emptyVariation)').each(function() {
            if ($(this).find('input[name*="[color_id]"]').val() === colorId &&
                $(this).find('input[name*="[size_id]"]').val() === sizeId) {
                isDuplicate = true; return false;
            }
        });
        if (isDuplicate) { alert('This Color + Size combination already exists!'); return; }

        $('#emptyVariation').hide();

        const rowHtml = `
            <tr id="var_${Date.now()}">
                <td>${colorId ? colorName : '-'}<input type="hidden" name="stocks[${variationIndex}][color_id]" value="${colorId}"></td>
                <td>${sizeId ? sizeName : '-'}<input type="hidden" name="stocks[${variationIndex}][size_id]" value="${sizeId}"></td>
                <td>${purchasePrice.toFixed(2)} BDT<input type="hidden" name="stocks[${variationIndex}][purchase_price]" value="${purchasePrice}"></td>
                <td style="color:#155724; font-weight:700;">${sellPrice.toFixed(2)} BDT<input type="hidden" name="stocks[${variationIndex}][sell_price]" value="${sellPrice}"></td>
                <td class="variation-barcode"><input type="hidden" name="stocks[${variationIndex}][barcode]" value="">Generated on save</td>
                <td><input type="number" name="stocks[${variationIndex}][quantity]" value="${stock}"
                           class="form-control form-control-sm variation-stock-input" min="0" style="height:26px;">
                </td>
                <td class="text-center">
                    <button type="button" class="btn-remove-var remove-variation"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;

        $('#variationBody').append(rowHtml);
        variationIndex++;
        calculateTotals();

        $('#product_purchase_price').val(currentPurchasePrice);
        $('#product_sale_price').val(currentSellPrice);

        $('#v_color, #v_size').val('').trigger('change');
        $('#v_stock').val(0);
        $('#v_purchase_price').val('');
        $('#v_price').val('');
        currentPurchasePrice = 0;
        currentSellPrice = 0;
    });

    $(document).on('click', '.remove-variation', function() {
        $(this).closest('tr').remove();
        if ($('#variationBody tr:not(#emptyVariation)').length === 0) $('#emptyVariation').show();
        calculateTotals();
    });

    $(document).on('input', '.variation-stock-input', calculateTotals);

    $('#productCreateForm').on('submit', function(e) {
        const rows = $('#variationBody tr:not(#emptyVariation)');
        // NOTE: required-field checks live here (not HTML5 `required`) because
        // Select2 hides the original <select>, and browsers silently block
        // submit on hidden required controls with no visible message.
        if (!$('input[name="name"]').val().trim()) { e.preventDefault(); alert('Please enter a product title.'); return; }
        if (!$('#category_id').val()) { e.preventDefault(); alert('Please select a category.'); $('#category_id').select2('open'); return; }
        if (!$('#supplier_id').val()) { e.preventDefault(); alert('Please select a supplier.'); $('#supplier_id').select2('open'); return; }
        if (!$('#unit_id').val()) { e.preventDefault(); alert('Please select a base unit.'); $('#unit_id').select2('open'); return; }
        if (rows.length === 0) { e.preventDefault(); alert('Please add at least one variation.'); return; }

    });

    $('#imageInput').on('change', function () {
        const reader = new FileReader();
        reader.onload = (e) => {
            $('#previewImg').attr('src', e.target.result).show();
            $('#uploadText').text('Change image');
        }
        reader.readAsDataURL(this.files[0]);
    });
});
</script>
@stop
