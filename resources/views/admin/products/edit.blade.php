@extends('adminlte::page')

@section('title', 'Edit Product | BongoBay')

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
    .img-upload-zone { border:2px dashed #adb5bd; border-radius:0.25rem; display:flex; align-items:center; justify-content:center; cursor:pointer; background:#f8f9fa; font-size:0.875rem; color:#6c757d; position:relative; overflow:hidden; padding:1.5rem; text-align:center; min-height:130px; }
    #previewImg { max-width:100%; max-height:180px; display:none; border-radius:0.25rem; margin-top:0.5rem; }
    #currentImg { max-width:100%; max-height:180px; border-radius:0.25rem; border:1px solid #dee2e6; }
    .sticky-footer { position:sticky; bottom:0; background:#fff; border-top:2px solid #dee2e6; padding:0.75rem 1rem; z-index:100; }
    .variation-table { font-size:0.875rem; }
    .variation-table th { background:#f8f9fa; border-bottom:2px solid #dee2e6; white-space:nowrap; }
    .variation-table td { vertical-align:middle; }
    .btn-remove-var { color:#dc3545; background:transparent; border:none; cursor:pointer; }
    .btn-remove-var:hover { color:#bd2130; }
    .card-outline.card-navy .card-header { background:linear-gradient(135deg, #001f3f, #003366); color:#fff; }
    .card-outline.card-olive .card-header { background:linear-gradient(135deg, #3d9970, #2d6b50); color:#fff; }
    .card-outline.card-teal .card-header { background:linear-gradient(135deg, #17a2b8, #0f7a8c); color:#fff; }
    .info-box-mini { background:#f8f9fa; border:1px solid #e9ecef; border-radius:0.25rem; padding:0.65rem 0.85rem; font-size:0.875rem; }
    .info-box-mini .label { color:#6c757d; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em; }
    .info-box-mini .value { font-weight:600; color:#212529; margin-top:0.15rem; }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-left" style="background:transparent; padding:0; margin:0;">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Product Registry</a></li>
            <li class="breadcrumb-item active">Edit Product</li>
        </ol>
    </div>
    <div class="col-sm-6 text-right">
        <span class="badge badge-secondary mr-2">ID #{{ $product->id }}</span>
        <span class="badge badge-info mr-2">{{ $product->code ?? '—' }}</span>
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
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Failed to save product:</strong> {{ session('error') }}
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><i class="fas fa-check-circle mr-1"></i></strong> {{ session('success') }}
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

    <form id="productEditForm" action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Hidden sync fields (kept updated from variation table / pricing) --}}
        <input type="hidden" name="stock_quantity" id="stock_quantity_hidden" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">

        <div class="row">
            {{-- Main Column --}}
            <div class="col-md-8">

                {{-- Primary Information --}}
                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-box mr-2"></i> Primary Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Product Title <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $product->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Product Code</label>
                                    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $product->code) }}" readonly style="background:#e9ecef;">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Barcode</label>
                                    <input type="text" name="barcode" id="product_barcode" class="form-control form-control-sm input-barcode" value="{{ old('barcode', $product->barcode) }}">
                                    <small class="form-text text-muted">Scan value used for POS / labels.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control form-control-sm select2">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <select name="brand_id" class="form-control form-control-sm select2">
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" id="supplier_id" class="form-control form-control-sm select2">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->company_name ? $supplier->company_name . ' - ' . $supplier->name : $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Base Unit <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-control form-control-sm select2">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Model</label>
                                    <select name="model_id" class="form-control form-control-sm select2">
                                        <option value="">Select Model</option>
                                        @foreach($models as $model)
                                            <option value="{{ $model->id }}" {{ old('model_id', $product->model_id) == $model->id ? 'selected' : '' }}>{{ $model->name }}{{ $model->brand ? ' (' . $model->brand->name . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tag mr-2"></i> Pricing</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Purchase Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" class="form-control form-control-sm input-purchase" value="{{ old('purchase_price', $product->purchase_price ?? $product->cost) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sale Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="sale_price" id="sale_price" class="form-control form-control-sm input-sell" value="{{ old('sale_price', $product->sale_price ?? $product->price) }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>MRP</label>
                                    <input type="number" step="0.01" min="0" name="mrp" id="mrp" class="form-control form-control-sm" value="{{ old('mrp', $product->mrp) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Discount (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="discount_percentage" id="discount_percentage" class="form-control form-control-sm" value="{{ old('discount_percentage', $product->discount_percentage) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tax (%)</label>
                                    <input type="number" step="0.01" min="0" name="tax_rate" class="form-control form-control-sm" value="{{ old('tax_rate', $product->tax_rate ?? 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Pricing Type</label>
                                    <select name="pricing_type" class="form-control form-control-sm">
                                        <option value="single" {{ old('pricing_type', $product->pricing_type ?? 'single') == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="batch" {{ old('pricing_type', $product->pricing_type) == 'batch' ? 'selected' : '' }}>Batch</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Total Stock (from variations)</label>
                                    <input type="number" id="total_stock_display" class="form-control form-control-sm" value="{{ $product->stock_quantity ?? 0 }}" readonly style="background:#e9ecef; font-weight:bold;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Min Stock Alert</label>
                                    <input type="number" min="0" name="min_stock_level" class="form-control form-control-sm" value="{{ old('min_stock_level', $product->min_stock_level ?? $product->min_stock ?? 5) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Variation & Stock --}}
                <div class="card card-outline card-olive">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i> Variation &amp; Stock</h3>
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
                                            <input type="number" id="v_purchase_price" step="0.01" min="0" class="form-control form-control-sm input-purchase" value="{{ old('purchase_price', $product->purchase_price ?? $product->cost) }}" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Selling Price <span class="text-danger">*</span></label>
                                            <input type="number" id="v_price" step="0.01" min="0" class="form-control form-control-sm input-sell" value="{{ old('sale_price', $product->sale_price ?? $product->price) }}" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <div class="form-group mb-0">
                                            <label>Opening Stock</label>
                                            <input type="number" id="v_stock" value="0" min="0" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" id="addVariation" class="btn btn-success btn-sm btn-block">
                                            <i class="fas fa-plus mr-1"></i> Add Variation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mt-3" style="max-height:240px; overflow-y:auto; border:1px solid #dee2e6; border-radius:0.25rem;">
                            <table class="table table-sm table-striped variation-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th width="120">Buy Price</th>
                                        <th width="120">Sell Price</th>
                                        <th width="110">Stock</th>
                                        <th width="50" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="variationBody">
                                    @php
                                        $colorIdsByName = $colors->mapWithKeys(fn ($color) => [mb_strtolower(trim((string) $color->name)) => $color->id]);
                                        $sizeIdsByName = $sizes->mapWithKeys(fn ($size) => [mb_strtolower(trim((string) $size->name)) => $size->id]);
                                        $variationRows = [];
                                        $usedVariationIds = [];
                                        $rowIndex = 0;

                                        foreach ($product->stocks as $i => $stock) {
                                            $stockValue = implode(' - ', array_filter([$stock->color?->name ?? null, $stock->size?->name ?? null]));
                                            $matched = $product->productVariations->firstWhere('variation_value', $stockValue) ?: $product->productVariations->get($i);
                                            if ($matched) {
                                                $usedVariationIds[$matched->id] = true;
                                            }
                                            $variationRows[] = [
                                                'index' => $rowIndex++,
                                                'color_name' => $stock->color?->name ?? '—',
                                                'size_name' => $stock->size?->name ?? '—',
                                                'color_id' => $stock->color_id,
                                                'size_id' => $stock->size_id,
                                                'purchase_price' => $matched?->purchase_price ?? $product->purchase_price ?? $product->cost,
                                                'sell_price' => $matched?->price ?? $product->sale_price ?? $product->price,
                                                'barcode' => $matched?->barcode ?? '',
                                                'quantity' => $stock->quantity,
                                            ];
                                        }

                                        foreach ($product->productVariations as $variation) {
                                            if (isset($usedVariationIds[$variation->id])) {
                                                continue;
                                            }

                                            $parts = array_values(array_filter(
                                                array_map('trim', explode(' - ', (string) $variation->variation_value)),
                                                fn ($part) => $part !== '' && strcasecmp($part, 'Default') !== 0
                                            ));
                                            $colorName = null;
                                            $sizeName = null;
                                            if (count($parts) >= 2) {
                                                [$colorName, $sizeName] = [$parts[0], $parts[1]];
                                            } elseif (count($parts) === 1) {
                                                $partKey = mb_strtolower($parts[0]);
                                                if ($colorIdsByName->has($partKey)) {
                                                    $colorName = $parts[0];
                                                } elseif ($sizeIdsByName->has($partKey)) {
                                                    $sizeName = $parts[0];
                                                }
                                            }

                                            $usedVariationIds[$variation->id] = true;
                                            $variationRows[] = [
                                                'index' => $rowIndex++,
                                                'color_name' => $colorName ?? '—',
                                                'size_name' => $sizeName ?? '—',
                                                'color_id' => $colorName !== null ? $colorIdsByName->get(mb_strtolower($colorName)) : null,
                                                'size_id' => $sizeName !== null ? $sizeIdsByName->get(mb_strtolower($sizeName)) : null,
                                                'purchase_price' => $variation->purchase_price ?? $product->purchase_price ?? $product->cost,
                                                'sell_price' => $variation->price ?? $product->sale_price ?? $product->price,
                                                'barcode' => $variation->barcode ?? '',
                                                'quantity' => $variation->stock,
                                            ];
                                        }
                                    @endphp
                                    @foreach($variationRows as $row)
                                        <tr>
                                            <td>
                                                {{ $row['color_name'] }}
                                                <input type="hidden" name="stocks[{{ $row['index'] }}][color_id]" value="{{ $row['color_id'] }}">
                                            </td>
                                            <td>
                                                {{ $row['size_name'] }}
                                                <input type="hidden" name="stocks[{{ $row['index'] }}][size_id]" value="{{ $row['size_id'] }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="stocks[{{ $row['index'] }}][purchase_price]" class="form-control form-control-sm input-purchase" value="{{ $row['purchase_price'] }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="stocks[{{ $row['index'] }}][sell_price]" class="form-control form-control-sm input-sell" value="{{ $row['sell_price'] }}">
                                            </td>
                                            <td>
                                                <input type="hidden" name="stocks[{{ $row['index'] }}][barcode]" value="{{ $row['barcode'] }}">
                                                <input type="number" name="stocks[{{ $row['index'] }}][quantity]" value="{{ $row['quantity'] }}" class="form-control form-control-sm variation-stock-input" min="0">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn-remove-var remove-variation"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr id="emptyVariation" style="display:none;">
                                        <td colspan="6" class="text-center text-muted py-3">No variations added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">Total stock is calculated automatically from the rows above. Edit any stock value and the total updates.</small>
                    </div>
                </div>

                {{-- Dates & Description --}}
                <div class="card card-outline card-teal">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i> Dates &amp; Description</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Manufacturing Date</label>
                                    <input type="date" name="manufacturing_date" class="form-control form-control-sm" value="{{ old('manufacturing_date', $product->manufacturing_date ? \Carbon\Carbon::parse($product->manufacturing_date)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expire_date" class="form-control form-control-sm" value="{{ old('expire_date', $product->expire_date ? \Carbon\Carbon::parse($product->expire_date)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Description</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Short product description…">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="col-md-4">
                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image mr-2"></i> Product Image</h3>
                    </div>
                    <div class="card-body text-center">
                        @if($product->image)
                            <img id="currentImg" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="mb-2">
                        @endif
                        <div class="img-upload-zone" onclick="document.getElementById('imageInput').click()">
                            <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;">
                            <span id="uploadText"><i class="fas fa-cloud-upload-alt fa-2x d-block mb-1"></i> {{ $product->image ? 'Click to change image' : 'Drag & drop or click to upload' }}</span>
                        </div>
                        <img id="previewImg">
                        <small class="text-muted d-block mt-2">Leave empty to keep current image.</small>
                    </div>
                </div>

                <div class="card card-outline card-navy">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog mr-2"></i> Storage &amp; Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Rack Location</label>
                            <select name="rack_id" class="form-control form-control-sm select2">
                                <option value="">N/A</option>
                                @foreach($racks as $rack)
                                    <option value="{{ $rack->id }}" {{ old('rack_id', $product->rack_id) == $rack->id ? 'selected' : '' }}>{{ $rack->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shelf Location</label>
                            <select name="shelf_id" class="form-control form-control-sm select2">
                                <option value="">N/A</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}" {{ old('shelf_id', $product->shelf_id) == $shelf->id ? 'selected' : '' }}>{{ $shelf->name }}{{ $shelf->rack ? ' (' . $shelf->rack->name . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Default Color</label>
                            <select name="color_id" class="form-control form-control-sm select2">
                                <option value="">Select Color</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}" {{ old('color_id', $product->color_id) == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Default Size</label>
                            <select name="size_id" class="form-control form-control-sm select2">
                                <option value="">Select Size</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}" {{ old('size_id', $product->size_id) == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active Status</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-teal">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Current Info</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box-mini mb-2">
                                    <div class="label">ID</div>
                                    <div class="value">#{{ $product->id }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-mini mb-2">
                                    <div class="label">Code</div>
                                    <div class="value">{{ $product->code ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-mini mb-2">
                                    <div class="label">Created</div>
                                    <div class="value">{{ $product->created_at ? $product->created_at->format('d M Y') : '—' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-mini mb-2">
                                    <div class="label">Updated</div>
                                    <div class="value">{{ $product->updated_at ? $product->updated_at->format('d M Y') : '—' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-mini">
                                    <div class="label">Stock</div>
                                    <div class="value">{{ $product->stock_quantity ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-mini">
                                    <div class="label">Variations</div>
                                    <div class="value">{{ $product->productVariations->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <i class="fas fa-save mr-1"></i> Update Product
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

    function getNextVariationIndex() {
        var maxIndex = -1;
        $('#variationBody').find('input[name^="stocks["]').each(function() {
            var match = ($(this).attr('name') || '').match(/^stocks\[(\d+)\]/);
            if (match) { maxIndex = Math.max(maxIndex, parseInt(match[1], 10)); }
        });
        return maxIndex + 1;
    }

    if ($('#variationBody tr:not(#emptyVariation)').length === 0) { $('#emptyVariation').show(); }

    function calculateTotals() {
        var totalStock = 0;
        $('.variation-stock-input').each(function() {
            totalStock += parseInt($(this).val()) || 0;
        });
        $('#total_stock_display').val(totalStock);
        $('#stock_quantity_hidden').val(totalStock);
    }

    $('#addVariation').on('click', function() {
        var purchasePrice = parseFloat($('#v_purchase_price').val()) || 0;
        var sellPrice = parseFloat($('#v_price').val()) || 0;
        var colorId = $('#v_color').val();
        var colorName = $('#v_color option:selected').text();
        var sizeId = $('#v_size').val();
        var sizeName = $('#v_size option:selected').text();
        var stock = parseInt($('#v_stock').val()) || 0;
        var variationBarcode = '';

        if (purchasePrice <= 0) { alert('Please enter Purchase Price.'); return; }
        if (sellPrice <= 0) { alert('Please enter Selling Price.'); return; }
        if (!colorId && !sizeId) { alert('Please select at least a Color or a Size.'); return; }
        if (stock < 0) { alert('Stock cannot be negative.'); return; }

        var isDuplicate = false;
        $('#variationBody tr:not(#emptyVariation)').each(function() {
            if ($(this).find('input[name*="[color_id]"]').val() === (colorId || '') &&
                $(this).find('input[name*="[size_id]"]').val() === (sizeId || '')) {
                isDuplicate = true; return false;
            }
        });
        if (isDuplicate) { alert('This Color + Size combination already exists!'); return; }

        $('#emptyVariation').hide();

        var variationIndex = getNextVariationIndex();
        var rowHtml = ''
            + '<tr>'
            + '<td>' + (colorId ? colorName : '-') + '<input type="hidden" name="stocks[' + variationIndex + '][color_id]" value="' + (colorId || '') + '"></td>'
            + '<td>' + (sizeId ? sizeName : '-') + '<input type="hidden" name="stocks[' + variationIndex + '][size_id]" value="' + (sizeId || '') + '"></td>'
            + '<td><input type="number" step="0.01" min="0" name="stocks[' + variationIndex + '][purchase_price]" value="' + purchasePrice + '" class="form-control form-control-sm input-purchase"></td>'
            + '<td><input type="number" step="0.01" min="0" name="stocks[' + variationIndex + '][sell_price]" value="' + sellPrice + '" class="form-control form-control-sm input-sell"></td>'
            + '<td><input type="hidden" name="stocks[' + variationIndex + '][barcode]" value="' + variationBarcode + '">'
            + '<input type="number" name="stocks[' + variationIndex + '][quantity]" value="' + stock + '" class="form-control form-control-sm variation-stock-input" min="0"></td>'
            + '<td class="text-center"><button type="button" class="btn-remove-var remove-variation"><i class="fas fa-trash"></i></button></td>'
            + '</tr>';

        $('#variationBody').append(rowHtml);
        calculateTotals();

        $('#v_color, #v_size').val('').trigger('change');
        $('#v_stock').val(0);
        $('#v_purchase_price').val($('#purchase_price').val());
        $('#v_price').val($('#sale_price').val());
    });

    $(document).on('click', '.remove-variation', function() {
        $(this).closest('tr').remove();
        if ($('#variationBody tr:not(#emptyVariation)').length === 0) $('#emptyVariation').show();
        calculateTotals();
    });

    $(document).on('input', '.variation-stock-input', calculateTotals);

    // Auto-calculate sale price from MRP + discount
    function calculateSalePrice() {
        var mrp = parseFloat($('#mrp').val()) || 0;
        var discount = parseFloat($('#discount_percentage').val()) || 0;
        if (mrp > 0) {
            $('#sale_price').val((mrp - (mrp * discount / 100)).toFixed(2));
        }
    }
    $('#mrp, #discount_percentage').on('input', calculateSalePrice);

    $('#productEditForm').on('submit', function(e) {
        var rows = $('#variationBody tr:not(#emptyVariation)');
        if (!$('input[name="name"]').val().trim()) { e.preventDefault(); alert('Please enter a product title.'); return; }
        if (!$('#category_id').val()) { e.preventDefault(); alert('Please select a category.'); $('#category_id').select2('open'); return; }
        if (!$('#supplier_id').val()) { e.preventDefault(); alert('Please select a supplier.'); $('#supplier_id').select2('open'); return; }
        if (!$('#unit_id').val()) { e.preventDefault(); alert('Please select a base unit.'); $('#unit_id').select2('open'); return; }
        if (rows.length === 0) { e.preventDefault(); alert('Please add at least one variation.'); return; }
    });

    $('#imageInput').on('change', function () {
        if (!this.files || !this.files[0]) { return; }
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#previewImg').attr('src', e.target.result).show();
            $('#uploadText').text('Change image');
        };
        reader.readAsDataURL(this.files[0]);
    });

    calculateTotals();
});
</script>
@stop
