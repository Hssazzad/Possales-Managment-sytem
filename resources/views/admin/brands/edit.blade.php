@extends('adminlte::page')

@section('title', 'Edit Brand | PosSales')

@section('content_header')
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-copyright mr-2"></i> Edit Brand
    </h1>
    <a href="{{ route('brands.index') }}" class="btn-back">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="col-span-1 md:col-span-3">
        <div class="card-professional">
            <div class="card-header-professional">
                <h5 class="m-0 font-heading text-subtitle">
                    <i class="fas fa-copyright mr-2"></i> Brand Information
                </h5>
            </div>
            <div class="card-body-professional">
                <form method="POST" action="{{ route('brands.update', $brand->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group-professional">
                            <label for="name" class="form-label-professional">Brand Name *</label>
                            <input type="text" class="w-full form-input-professional @error('name') border-danger-500 @enderror" id="name" name="name" placeholder="Enter brand name" required value="{{ old('name', $brand->name) }}">
                            @error('name')
                                <div class="text-danger-600 text-helper mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group-professional">
                            <label for="slug" class="form-label-professional">Slug (Optional)</label>
                            <input type="text" class="w-full form-input-professional @error('slug') border-danger-500 @enderror" id="slug" name="slug" placeholder="auto-generated-from-name" value="{{ old('slug', $brand->slug) }}">
                            <span class="helper-text">Leave blank to auto-generate from name</span>
                            @error('slug')
                                <div class="text-danger-600 text-helper mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group-professional">
                        <label for="description" class="form-label-professional">Description</label>
                        <textarea class="w-full form-input-professional @error('description') border-danger-500 @enderror" id="description" name="description" rows="3" placeholder="Enter brand description">{{ old('description', $brand->description) }}</textarea>
                        @error('description')
                            <div class="text-danger-600 text-helper mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group-professional">
                        <label class="form-label-professional">Brand Logo</label>
                        <div class="image-upload-container border-2 border-dashed border-primary-300 rounded-input p-8 text-center bg-primary-50 cursor-pointer transition-all hover:border-primary-500 hover:bg-primary-100" id="imageUploadContainer">
                            <input type="file" id="image" name="image" accept="image/*" class="hidden">
                            <div class="upload-placeholder" id="uploadPlaceholder" @if($brand->image) class="hidden" @endif>
                                <i class="fas fa-cloud-upload-alt text-4xl text-primary-500 mb-3 block"></i>
                                <p class="mb-1 font-heading text-primary-700"><strong>Drag & Drop Image Here</strong></p>
                                <p class="text-gray-500 mb-2">or click to browse</p>
                                <small class="text-gray-400">Allowed: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                            </div>
                            <div class="image-preview @if(!$brand->image) hidden @endif" id="imagePreview">
                                @if($brand->image)
                                    <img src="{{ asset('storage/' . $brand->image) }}?{{ time() }}" alt="{{ $brand->name }}" class="max-w-150px max-h-130px rounded mb-3 mx-auto">
                                @else
                                    <img src="" alt="Preview" class="max-w-150px max-h-130px rounded mb-3 mx-auto">
                                @endif
                                <p class="mb-0 text-sm"><span id="fileName">{{ $brand->image ? basename($brand->image) : '' }}</span> <a href="#" onclick="clearImage(); return false;" class="text-danger-600 ml-2"><i class="fas fa-times"></i> Remove</a></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-professional">
                        <label class="form-label-professional">Status</label>
                        <div class="flex items-center">
                            <input class="w-4 h-4 rounded border-primary-300 text-primary-500" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label ml-2 text-input cursor-pointer" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8">
                        <button type="submit" class="btn-primary flex-1">
                            <i class="fas fa-save mr-2"></i> Update Brand
                        </button>
                        <a href="{{ route('brands.index') }}" class="btn-secondary flex-1 text-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-span-1 md:col-span-1">
        <div class="card-professional">
            <div class="card-header-professional">
                <h5 class="m-0 font-heading text-subtitle">
                    <i class="fas fa-info-circle mr-2"></i> Brand Guidelines
                </h5>
            </div>
            <div class="card-body-professional">
                <div class="tip-box">
                    <h6 class="tip-box-title"><i class="fas fa-lightbulb mr-2"></i> Quick Tips</h6>
                    <ul class="tip-box-list">
                        <li class="tip-box-item">Edit brand name or slug</li>
                        <li class="tip-box-item">Upload new logo if needed</li>
                        <li class="tip-box-item">Toggle active status</li>
                        <li class="tip-box-item">Changes apply immediately</li>
                    </ul>
                </div>
                <div class="tip-box bg-warning-50 border-l-4 border-warning-500 mt-4">
                    <h6 class="text-warning-800 font-heading mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="text-sm text-warning-800 mb-0">Changing the brand name will not affect products already using this brand.</p>
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
            imageContainer.classList.add('border-primary-500', 'bg-primary-100');
            imageContainer.classList.remove('border-primary-300', 'bg-primary-50');
        });

        imageContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            imageContainer.classList.remove('border-primary-500', 'bg-primary-100');
            imageContainer.classList.add('border-primary-300', 'bg-primary-50');
        });

        imageContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            imageContainer.classList.remove('border-primary-500', 'bg-primary-100');
            imageContainer.classList.add('border-primary-300', 'bg-primary-50');

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
                    uploadPlaceholder.classList.add('hidden');
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Clear image function
        window.clearImage = function() {
            imageInput.value = '';
            previewImg.src = '';
            uploadPlaceholder.classList.remove('hidden');
            imagePreview.classList.add('hidden');
            return false;
        };
    }
});
</script>
@stop

@section('adminlte_css')
<style>
  .image-upload-container {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    min-height: 180px;
  }

  .image-preview img {
    max-width: 150px;
    max-height: 130px;
    border-radius: 4px;
    object-fit: cover;
  }
