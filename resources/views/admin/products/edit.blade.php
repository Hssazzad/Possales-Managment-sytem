@extends('adminlte::page')

@section('title', 'Edit Product | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.2rem; font-weight:normal; color:#000;">
        Edit Product
    </h1>
    <div>
        <a href="{{ route('products.index') }}" class="btn btn-sm" style="background:#fff; border:1px solid #ccc; color:#000;">
            Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background:#f5f5f5; border-bottom:1px solid #ddd;">
                <h5 style="margin:0; font-weight:normal; color:#000;">
                    Product Information
                </h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code" class="form-label">Product Code *</label>
                                <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $product->code) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select class="form-control" id="brand_id" name="brand_id">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_id" class="form-label">Unit</label>
                                <select class="form-control" id="unit_id" name="unit_id">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model_id" class="form-label">Model</label>
                                <select class="form-control" id="model_id" name="model_id">
                                    <option value="">Select Model</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('model_id', $product->model_id) == $model->id ? 'selected' : '' }}>{{ $model->name }} ({{ $model->brand->name ?? 'No Brand' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="rack_id" class="form-label">Rack</label>
                                <select class="form-control" id="rack_id" name="rack_id">
                                    <option value="">Select Rack</option>
                                    @foreach($racks as $rack)
                                        <option value="{{ $rack->id }}" {{ old('rack_id', $product->rack_id) == $rack->id ? 'selected' : '' }}>{{ $rack->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="shelf_id" class="form-label">Shelf</label>
                                <select class="form-control" id="shelf_id" name="shelf_id">
                                    <option value="">Select Shelf</option>
                                    @foreach($shelves as $shelf)
                                        <option value="{{ $shelf->id }}" {{ old('shelf_id', $product->shelf_id) == $shelf->id ? 'selected' : '' }}>{{ $shelf->name }} ({{ $shelf->rack->name ?? 'No Rack' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="purchase_price" class="form-label">Purchase Price *</label>
                                <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? $product->cost) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="mrp" class="form-label">MRP</label>
                                <input type="number" step="0.01" class="form-control" id="mrp" name="mrp" value="{{ old('mrp', $product->mrp ?? $product->price) }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="discount_percentage" class="form-label">Discount (%)</label>
                                <input type="number" step="0.01" class="form-control" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', $product->discount_percentage ?? 0) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sale_price" class="form-label">Sale Price *</label>
                                <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? $product->price) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="min_stock_level" class="form-label">Low Stock Alert</label>
                                <input type="number" class="form-control" id="min_stock_level" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level ?? $product->min_stock) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manufacturing_date" class="form-label">Manufacturing Date</label>
                                <input type="date" class="form-control" id="manufacturing_date" name="manufacturing_date" value="{{ old('manufacturing_date', $product->manufacturing_date ? date('Y-m-d', strtotime($product->manufacturing_date)) : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expire_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expire_date" name="expire_date" value="{{ old('expire_date', $product->expire_date ? date('Y-m-d', strtotime($product->expire_date)) : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image" class="form-label">Product Image</label>
                                @if($product->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-height:100px; border:1px solid #ddd;">
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn" style="background:#333; color:#fff; border:1px solid #000;">
                            Update Product
                        </button>
                        <a href="{{ route('products.index') }}" class="btn ml-2" style="background:#fff; color:#000; border:1px solid #ccc;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background:#f5f5f5; border-bottom:1px solid #ddd;">
                <h5 style="margin:0; font-weight:normal; color:#000;">
                    Current Product Info
                </h5>
            </div>
            <div class="card-body">
                <div style="border:1px solid #ddd; padding:10px; background:#f9f9f9;">
                    <table style="width:100%; font-size:0.9rem;">
                        <tr><td><strong>ID:</strong></td><td>{{ $product->id }}</td></tr>
                        <tr><td><strong>Code:</strong></td><td>{{ $product->code }}</td></tr>
                        <tr><td><strong>Created:</strong></td><td>{{ $product->created_at->format('d M Y') }}</td></tr>
                        <tr><td><strong>Updated:</strong></td><td>{{ $product->updated_at->format('d M Y') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Auto-calculate sales price from MRP and discount
document.getElementById('mrp').addEventListener('input', calculateSalePrice);
document.getElementById('discount_percentage').addEventListener('input', calculateSalePrice);

function calculateSalePrice() {
    const mrp = parseFloat(document.getElementById('mrp').value) || 0;
    const discount = parseFloat(document.getElementById('discount_percentage').value) || 0;
    const salePrice = mrp - (mrp * discount / 100);
    document.getElementById('sale_price').value = salePrice.toFixed(2);
}
</script>
@stop
