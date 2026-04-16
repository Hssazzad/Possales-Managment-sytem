@extends('adminlte::page')

@section('title', 'Edit Category | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-folder mr-2" style="color:#e83e8c;"></i> Edit Category
    </h1>
    <div>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Categories
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
                    <i class="fas fa-folder mr-2" style="color:#e83e8c;"></i>
                    Category Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('categories.update', $category->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Category Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter category name" required value="{{ old('name', $category->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug" class="form-label">Slug (Optional)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" placeholder="auto-generated-from-name" value="{{ old('slug', $category->slug) }}">
                                <small class="text-muted">Leave blank to auto-generate from name</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon" class="form-label">Icon</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" placeholder="e.g., fa-box, fa-tag, fa-shopping-bag" value="{{ old('icon', $category->icon) }}">
                                <small class="text-muted">FontAwesome icon class (e.g., fa-box, fa-tag)</small>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select class="form-control @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $category->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Variations</label>
                        <div class="row">
                            @foreach($variationTypes as $variation)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="variations[]" id="variation_{{ $variation }}" value="{{ $variation }}" {{ (old('variations', $category->variations ?? []) && in_array($variation, old('variations', $category->variations ?? []))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="variation_{{ $variation }}">
                                        {{ $variation }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Select variations for products in this category</small>
                        @error('variations')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter category description">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Category
                        </button>
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary ml-2">
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
                    <i class="fas fa-info-circle mr-2" style="color:#e83e8c;"></i>
                    Category Guidelines
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Edit category name or slug</li>
                        <li>Update icon or brand</li>
                        <li>Change variations selection</li>
                        <li>Toggle active status</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Changes apply immediately to all products in this category.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_css')
<style>
.form-group { margin-bottom: 1rem; }
.alert { margin-bottom: 1rem; }
.form-check-label { margin-left: 5px; }
</style>
@stop
