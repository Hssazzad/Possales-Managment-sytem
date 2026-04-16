@extends('adminlte::page')

@section('title', 'Add Product | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.2rem; font-weight:600; color:#000000;">
        Add New Product
    </h1>
    <div>
        <a href="{{ route('products.index') }}" class="btn btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    Product Information
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label" style="font-weight:600; color:#000000;">Product Name *</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code" class="form-label" style="font-weight:600; color:#000000;">Product Code *</label>
                                <input type="text" class="form-control" id="code" name="code" placeholder="Enter unique product code" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id" class="form-label" style="font-weight:600; color:#000000;">Category</label>
                                <select class="form-control" id="category_id" name="category_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand_id" class="form-label" style="font-weight:600; color:#000000;">Brand</label>
                                <select class="form-control" id="brand_id" name="brand_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_id" class="form-label" style="font-weight:600; color:#000000;">Unit</label>
                                <select class="form-control" id="unit_id" name="unit_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="barcode" class="form-label" style="font-weight:600; color:#000000;">Barcode</label>
                                <input type="text" class="form-control" id="barcode" name="barcode" placeholder="Enter barcode" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model_id" class="form-label" style="font-weight:600; color:#000000;">Model</label>
                                <select class="form-control" id="model_id" name="model_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Model</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }} ({{ $model->brand->name ?? 'No Brand' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="rack_id" class="form-label" style="font-weight:600; color:#000000;">Rack</label>
                                <select class="form-control" id="rack_id" name="rack_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Rack</option>
                                    @foreach($racks as $rack)
                                        <option value="{{ $rack->id }}" {{ old('rack_id') == $rack->id ? 'selected' : '' }}>{{ $rack->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="shelf_id" class="form-label" style="font-weight:600; color:#000000;">Shelf</label>
                                <select class="form-control" id="shelf_id" name="shelf_id" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                                    <option value="">Select Shelf</option>
                                    @foreach($shelves as $shelf)
                                        <option value="{{ $shelf->id }}" {{ old('shelf_id') == $shelf->id ? 'selected' : '' }}>{{ $shelf->name }} ({{ $shelf->rack->name ?? 'No Rack' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" style="font-weight:600; color:#000000;">Product Price Type</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="pricing_type" id="pricing_single" value="single" checked style="border:2px solid #333333;">
                                    <label class="form-check-label" for="pricing_single" style="color:#000000;">Single</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="pricing_type" id="pricing_batch" value="batch" style="border:2px solid #333333;">
                                    <label class="form-check-label" for="pricing_batch" style="color:#000000;">Batch</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_stock_level" class="form-label" style="font-weight:600; color:#000000;">Low Stock Alert</label>
                                <input type="number" class="form-control" id="min_stock_level" name="min_stock_level" min="0" value="5" placeholder="EX: 5" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock_quantity" class="form-label" style="font-weight:600; color:#000000;">Quantity *</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="0" placeholder="EX: 10" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_price" class="form-label" style="font-weight:600; color:#000000;">Cost excl. Tax *</label>
                                <input type="number" class="form-control" id="purchase_price" name="purchase_price" step="0.01" min="0" placeholder="Enter Purchase Price" required style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="mrp" class="form-label" style="font-weight:600; color:#000000;">MRP (Maximum Retail Price)</label>
                                <input type="number" class="form-control" id="mrp" name="mrp" step="0.01" min="0" placeholder="Enter MRP" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="discount_percentage" class="form-label" style="font-weight:600; color:#000000;">Discount (%)</label>
                                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" step="0.01" min="0" max="100" value="0" placeholder="Enter discount percentage" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sale_price" class="form-label" style="font-weight:600; color:#000000;">Sales Price (Calculated) *</label>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" placeholder="Auto calculated" readonly style="border:2px solid #333333; background:#f5f5f5; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_rate" class="form-label" style="font-weight:600; color:#000000;">Tax Rate (%)</label>
                                <input type="number" class="form-control" id="tax_rate" name="tax_rate" step="0.01" min="0" value="0" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" style="font-weight:600; color:#000000;">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked style="border:2px solid #333333;">
                                    <label class="form-check-label" for="is_active" style="color:#000000;">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manufacturing_date" class="form-label" style="font-weight:600; color:#000000;">Manufacturing Date</label>
                                <input type="date" class="form-control" id="manufacturing_date" name="manufacturing_date" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expire_date" class="form-label" style="font-weight:600; color:#000000;">Expire Date</label>
                                <input type="date" class="form-control" id="expire_date" name="expire_date" style="border:2px solid #333333; background:#ffffff; color:#000000;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label" style="font-weight:600; color:#000000;">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter product description" style="border:2px solid #333333; background:#ffffff; color:#000000;"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight:600; color:#000000;">Product Image</label>
                        <div class="image-upload-container" id="imageUploadContainer" style="border: 2px dashed #333333; border-radius: 4px; padding: 30px; text-align: center; background: #f5f5f5; cursor: pointer; transition: all 0.3s;"
                            <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color:#000000;"></i>
                                <p class="mb-1" style="color:#000000;"><strong>Drag & Drop Image Here</strong></p>
                                <p class="mb-2" style="color:#666666;">or click to browse</p>
                                <small style="color:#666666;">Image (Size 150x130)<br>Allowed: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                            </div>
                            <div class="image-preview" id="imagePreview" style="display: none;">
                                <img src="" alt="Preview" style="max-width: 150px; max-height: 130px; border-radius: 4px; margin-bottom: 10px;">
                                <p class="mb-0"><span id="fileName"></span> <a href="#" onclick="clearImage(); return false;" class="text-danger"><i class="fas fa-times"></i> Remove</a></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px; font-weight:600;">
                            Save Product
                        </button>
                        <a href="{{ route('products.index') }}" class="btn ml-2" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px; font-weight:600;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    Quick Tips
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <div style="border:2px solid #333333; padding:15px; margin-bottom:15px; background:#f5f5f5; border-radius:4px;">
                    <h6 style="margin-bottom:8px; color:#000000; font-weight:600;">Tips</h6>
                    <ul style="margin:0; padding-left:20px; color:#000000;">
                        <li>All fields marked with * are required</li>
                        <li>Product code must be unique</li>
                        <li>Set low stock alert for inventory warnings</li>
                        <li>Use proper category for reporting</li>
                    </ul>
                </div>
                <div style="border:2px solid #333333; padding:15px; background:#f5f5f5; border-radius:4px;">
                    <h6 style="margin-bottom:8px; color:#000000; font-weight:600;">Important</h6>
                    <p style="margin:0; color:#000000;">Product name and code cannot be changed after creation. Please double-check before saving.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Auto-calculate sales price from MRP and discount
document.addEventListener('DOMContentLoaded', function() {
    const mrpInput = document.getElementById('mrp');
    const discountInput = document.getElementById('discount_percentage');
    const salePriceInput = document.getElementById('sale_price');

    function calculateSalePrice() {
        const mrp = parseFloat(mrpInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;

        if (mrp > 0) {
            const discountAmount = mrp * (discount / 100);
            const salePrice = mrp - discountAmount;
            salePriceInput.value = salePrice.toFixed(2);
        } else {
            salePriceInput.value = '';
        }
    }

    if (mrpInput && discountInput && salePriceInput) {
        mrpInput.addEventListener('input', calculateSalePrice);
        discountInput.addEventListener('input', calculateSalePrice);
    }

    // Drag & Drop Image Upload
    const imageContainer = document.getElementById('imageUploadContainer');
    const imageInput = document.getElementById('image');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = imagePreview.querySelector('img');
    const fileNameSpan = document.getElementById('fileName');

    if (imageContainer && imageInput) {
        // Click to browse
        imageContainer.addEventListener('click', function(e) {
            if (e.target !== imageInput && !e.target.closest('a')) {
                imageInput.click();
            }
        });

        // File selected
        imageInput.addEventListener('change', function(e) {
            handleImageFile(e.target.files[0]);
        });

        // Drag & drop events
        imageContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#28a745';
            imageContainer.style.background = '#e8f5e9';
        });

        imageContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#28a745';
            imageContainer.style.background = '#f8f9fa';
        });

        imageContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#28a745';
            imageContainer.style.background = '#f8f9fa';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                handleImageFile(files[0]);
            }
        });

        function handleImageFile(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    fileNameSpan.textContent = file.name;
                    uploadPlaceholder.style.display = 'none';
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Clear image function
        window.clearImage = function() {
            imageInput.value = '';
            previewImg.src = '';
            uploadPlaceholder.style.display = 'block';
            imagePreview.style.display = 'none';
            return false;
        };
    }
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.form-group { margin-bottom: 1rem; }
.alert { margin-bottom: 1rem; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.image-upload-container:hover {
    border-color: #000000 !important;
    background: #f5f5f5 !important;
}
</style>
@stop
