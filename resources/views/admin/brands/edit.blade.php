@extends('adminlte::page')

@section('title', 'Edit Brand | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-copyright mr-2" style="color:#17a2b8;"></i> Edit Brand
    </h1>
    <div>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Brands
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
                    <i class="fas fa-copyright mr-2" style="color:#17a2b8;"></i>
                    Brand Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('brands.update', $brand->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Brand Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter brand name" required value="{{ old('name', $brand->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug" class="form-label">Slug (Optional)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="auto-generated-from-name" value="{{ old('slug', $brand->slug) }}">
                                <small class="text-muted">Leave blank to auto-generate from name</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter brand description">{{ old('description', $brand->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Brand Logo</label>
                        <div class="image-upload-container" id="imageUploadContainer" style="border: 2px dashed #17a2b8; border-radius: 8px; padding: 30px; text-align: center; background: #f8f9fa; cursor: pointer; transition: all 0.3s;">
                            <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                            <div class="upload-placeholder" id="uploadPlaceholder" style="{{ $brand->image ? 'display: none;' : '' }}">
                                <i class="fas fa-cloud-upload-alt fa-3x text-info mb-3"></i>
                                <p class="mb-1"><strong>Drag & Drop Image Here</strong></p>
                                <p class="text-muted mb-2">or click to browse</p>
                                <small class="text-muted">Allowed: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                            </div>
                            <div class="image-preview" id="imagePreview" style="{{ $brand->image ? 'display: block;' : 'display: none;' }}">
                                @if($brand->image)
                                    <img src="{{ asset('storage/' . $brand->image) }}?{{ time() }}" alt="{{ $brand->name }}" style="max-width: 150px; max-height: 130px; border-radius: 4px; margin-bottom: 10px;">
                                @else
                                    <img src="" alt="Preview" style="max-width: 150px; max-height: 130px; border-radius: 4px; margin-bottom: 10px;">
                                @endif
                                <p class="mb-0">
                                    <span id="fileName">{{ $brand->image ? basename($brand->image) : '' }}</span> 
                                    <a href="#" onclick="clearImage(); return false;" class="text-danger"><i class="fas fa-times"></i> Remove</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Brand
                        </button>
                        <a href="{{ route('brands.index') }}" class="btn btn-secondary ml-2">
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
                    <i class="fas fa-info-circle mr-2" style="color:#17a2b8;"></i>
                    Brand Guidelines
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Edit brand name or slug</li>
                        <li>Upload new logo if needed</li>
                        <li>Toggle active status</li>
                        <li>Changes apply immediately</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Changing the brand name will not affect products already using this brand.</p>
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
            imageContainer.style.borderColor = '#17a2b8';
            imageContainer.style.background = '#e3f2fd';
        });

        imageContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#17a2b8';
            imageContainer.style.background = '#f8f9fa';
        });

        imageContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            imageContainer.style.borderColor = '#17a2b8';
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
    border-color: #17a2b8 !important;
    background: #e3f2fd !important;
}
</style>
@stop
