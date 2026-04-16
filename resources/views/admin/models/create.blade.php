@extends('adminlte::page')

@section('title', 'New Model | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-cube mr-2" style="color:#6c757d;"></i> New Model
    </h1>
    <div>
        <a href="{{ route('models.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Models
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-cube mr-2" style="color:#6c757d;"></i>
                    Model Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('models.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Model Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter model name" required value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug" class="form-label">Slug (Optional)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="auto-generated-from-name" value="{{ old('slug') }}">
                                <small class="text-muted">Leave blank to auto-generate from name</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-control @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter model description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Model Image</label>
                        <div class="image-upload-container" id="imageUploadContainer" style="border: 2px dashed #6c757d; border-radius: 8px; padding: 30px; text-align: center; background: #f8f9fa; cursor: pointer; transition: all 0.3s;">
                            <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-secondary mb-3"></i>
                                <p class="mb-1"><strong>Drag & Drop Image Here</strong></p>
                                <p class="text-muted mb-2">or click to browse</p>
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                            </div>
                            <div class="image-preview" id="imagePreview" style="display: none;">
                                <img src="" alt="Preview" style="max-width: 150px; max-height: 130px; border-radius: 4px; margin-bottom: 10px;">
                                <p class="mb-0"><span id="fileName"></span> <a href="#" onclick="clearImage(); return false;" class="text-danger"><i class="fas fa-times"></i> Remove</a></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Model
                        </button>
                        <a href="{{ route('models.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#6c757d;"></i>
                    Model Guidelines
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Enter unique model name</li>
                        <li>Select associated brand</li>
                        <li>Slug auto-generates if left blank</li>
                        <li>Upload model image (optional)</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Model name must be unique. Models are linked to brands and can be used when adding products.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            imageContainer.style.borderColor = '#6c757d';
            imageContainer.style.background = '#e9ecef';
        });

        imageContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#6c757d';
            imageContainer.style.background = '#f8f9fa';
        });

        imageContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#6c757d';
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
.form-group { margin-bottom: 1rem; }
.alert { margin-bottom: 1rem; }
.image-upload-container:hover {
    border-color: #6c757d !important;
    background: #e9ecef !important;
}
</style>
@stop
