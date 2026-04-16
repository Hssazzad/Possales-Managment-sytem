@extends('adminlte::page')

@section('title', 'Bulk Product Upload | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-upload mr-2" style="color:#000000;"></i> Bulk Product Upload
    </h1>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back
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
                    <i class="fas fa-file-import mr-2" style="color:#000000;"></i>
                    Upload Products
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <!-- Profile/Company Info -->
                <div style="border:2px solid #333333; border-radius:4px; padding:16px; margin-bottom:20px; background:#f5f5f5;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x mr-3" style="color:#000000;"></i>
                        <div>
                            <h5 class="mb-0" style="color:#000000;">Hello 🖐</h5>
                            <p class="mb-0 font-weight-bold" style="color:#000000;">NBS TRADERS</p>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <form method="POST" action="{{ route('products.bulk-upload') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- File Upload -->
                    <div class="form-group">
                        <label for="file" class="form-label font-weight-bold" style="color:#000000;">
                            <i class="fas fa-file-csv mr-2" style="color:#000000;"></i>
                            Select File
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file" accept=".csv,.xlsx,.xls" required>
                            <label class="custom-file-label" for="file" style="border:2px solid #333333; background:#ffffff; color:#000000;">Choose file...</label>
                            @error('file')
                                <div class="invalid-feedback" style="color:#dc3545;">{{ $message }}</div>
                            @enderror
                        </div>
                        <small style="color:#666666;">Allowed formats: CSV, Excel (.xlsx, .xls) | Max size: 10MB</small>
                    </div>

                    <!-- Download Sample -->
                    <div class="form-group mt-4">
                        <label class="form-label font-weight-bold" style="color:#000000;">
                            <i class="fas fa-download mr-2" style="color:#000000;"></i>
                            Download Sample
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend" style="background:#ffffff; border:2px solid #333333; border-right:none;">
                                <span class="input-group-text" style="background:#ffffff; border:none; color:#000000;">
                                    <i class="fas fa-file-csv"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" value="product_sample.csv" readonly style="border:2px solid #333333; border-left:none; border-right:none; background:#ffffff; color:#000000;">
                            <div class="input-group-append">
                                <a href="{{ route('products.download-sample') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; border-left:none;">
                                    <i class="fas fa-download mr-1"></i> Download File
                                </a>
                            </div>
                        </div>
                        <small style="color:#666666;">Download the sample file, add your products, then upload</small>
                    </div>

                    <!-- Instructions -->
                    <div style="border:2px solid #333333; border-radius:4px; padding:16px; margin-top:20px; background:#f5f5f5;">
                        <h6 class="font-weight-bold" style="color:#000000;">
                            <i class="fas fa-info-circle mr-2" style="color:#000000;"></i>
                            Instructions
                        </h6>
                        <p class="mb-2" style="color:#000000;">Note: Please follow the instructions below to upload your file.</p>
                        <ol class="mb-0 pl-3" style="color:#000000;">
                            <li>Download the sample file first and add all your products to it.</li>
                            <li><strong>*</strong> Indicates a required field. If you do not provide the required fields, the system will ignore the product.</li>
                            <li>After adding all your products, please save the file and then upload the updated version.</li>
                        </ol>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-lg" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:12px 24px; font-weight:600;">
                            <i class="fas fa-upload mr-2"></i> Submit
                        </button>
                        <button type="reset" class="btn ml-2" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:12px 24px; font-weight:600;">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Required Fields Info -->
    <div class="col-md-4">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-list-check mr-2" style="color:#000000;"></i>
                    Required Fields
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <ul style="list-style:none; padding:0; margin:0;">
                    <li style="padding:10px 0; border-bottom:1px solid #333333; color:#000000;">
                        <i class="fas fa-asterisk mr-2" style="color:#dc3545;"></i>
                        <strong>name</strong> - Product name
                    </li>
                    <li style="padding:10px 0; border-bottom:1px solid #333333; color:#000000;">
                        <i class="fas fa-asterisk mr-2" style="color:#dc3545;"></i>
                        <strong>code</strong> - Product code/SKU
                    </li>
                    <li style="padding:10px 0; border-bottom:1px solid #333333; color:#000000;">
                        <i class="fas fa-asterisk mr-2" style="color:#dc3545;"></i>
                        <strong>purchase_price</strong> - Cost price
                    </li>
                    <li style="padding:10px 0; border-bottom:1px solid #333333; color:#000000;">
                        <i class="fas fa-asterisk mr-2" style="color:#dc3545;"></i>
                        <strong>sale_price</strong> - Selling price
                    </li>
                    <li style="padding:10px 0; color:#000000;">
                        <i class="fas fa-asterisk mr-2" style="color:#dc3545;"></i>
                        <strong>stock_quantity</strong> - Initial stock
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mt-3" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-info-circle mr-2" style="color:#000000;"></i>
                    Optional Fields
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <ul style="list-style:none; padding:0; margin:0;">
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">category_id</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">brand_id</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">model_id</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">unit_id</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">rack_id</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">shelf_id</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">mrp</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">discount_percentage</li>
                    <li style="padding:8px 0; border-bottom:1px solid #333333; color:#000000;">low_stock_alert</li>
                    <li style="padding:8px 0; color:#000000;">description</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// File input label update
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file...';
    e.target.nextElementSibling.textContent = fileName;
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.custom-file-label::after {
    content: "Browse";
    background: #f5f5f5;
    color: #000000;
    border: 2px solid #333333;
    border-left: none;
}
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.custom-file-input:focus ~ .custom-file-label { border-color: #000000 !important; box-shadow: none !important; }
</style>
@stop
