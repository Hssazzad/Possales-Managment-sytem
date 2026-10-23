@extends('adminlte::page')

@section('title', 'New Category Registration | PosSales')

@section('adminlte_css')
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<style>
.content-wrapper { background: #f0f0f0 !important; }
.two-col-layout  { display: flex; gap: 16px; align-items: flex-start; }
.main-col        { flex: 1; min-width: 0; }
.sidebar-col     { width: 300px; min-width: 280px; }
.product-grid-2  { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
.section-title {
    font-size: 13px; font-weight: bold; color: #006d7a;
    border-bottom: 2px solid #00bcd4; padding-bottom: 6px; margin-bottom: 12px;
}
.form-section { padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid #e0e0e0; }
.form-section:last-child { border-bottom: none; margin-bottom: 0; }
.field-error { color: #d32f2f; font-size: 11px; margin-top: 2px; display: block; }
.field-hint  { color: #999; font-size: 11px; margin-top: 2px; display: block; }

.variation-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 8px;
    background: #f9fffe;
    border: 1px solid #00bcd4;
    padding: 10px;
}
.variation-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 8px;
    background: #fff;
    border: 1px solid #e0e0e0;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
    color: #006d7a;
}
.variation-item:hover { background: #e0f7fa; border-color: #00bcd4; }
.variation-item input { cursor: pointer; accent-color: #00bcd4; }

.status-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f9fffe;
    border: 1px solid #00bcd4;
    padding: 10px 12px;
    cursor: pointer;
}
.status-badge {
    padding: 2px 8px;
    font-size: 11px;
    font-weight: bold;
    border: 1px solid transparent;
}

@media (max-width: 900px) {
    .two-col-layout { flex-direction: column; }
    .sidebar-col    { width: 100%; }
    .product-grid-2 { grid-template-columns: 1fr; }
}
</style>
@stop

@section('content_header')
<div class="page-header">
    <span class="page-title">
        <i class="fas fa-folder-plus" style="margin-right:6px;"></i> Category Registration Form
    </span>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Categories
    </a>
</div>
@stop

@section('content')
<div style="padding:14px;">

    <div class="alert alert-info" style="margin-bottom:14px;">
        <i class="fas fa-info-circle" style="margin-right:6px;"></i>
        Please complete all mandatory fields marked with <strong style="color:#d32f2f;">*</strong> before submission.
    </div>

    <div class="two-col-layout">

        <!-- MAIN FORM -->
        <div class="main-col">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-edit" style="margin-right:6px;"></i> Category Details
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('categories.store') }}">
                        @csrf

                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-info" style="margin-right:5px;"></i>Basic Information</div>

                            <div class="form-group">
                                <label>Category Name <span style="color:#d32f2f;">*</span></label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Enter official category name" required
                                    value="{{ old('name') }}">
                                @error('name')<span class="field-error">{{ $message }}</span>@enderror
                                <span class="field-hint">Must be unique and descriptive</span>
                            </div>

                            <div class="form-group">
                                <label>URL Slug <span style="color:#999;font-weight:normal;font-size:11px;">(Optional — auto-generated)</span></label>
                                <input type="text" id="slug" name="slug" class="form-control"
                                    placeholder="auto-generated-from-name"
                                    value="{{ old('slug') }}"
                                    style="font-family:monospace;">
                                @error('slug')<span class="field-error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- Configuration -->
                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-cog" style="margin-right:5px;"></i>Configuration</div>

                            <div class="product-grid-2">
                                <div class="form-group">
                                    <label>Category Icon</label>
                                    <input type="text" name="icon" class="form-control"
                                        placeholder="e.g., fa-box"
                                        value="{{ old('icon') }}">
                                    @error('icon')<span class="field-error">{{ $message }}</span>@enderror
                                    <span class="field-hint">FontAwesome icon class</span>
                                </div>
                                <div class="form-group">
                                    <label>Associated Brand</label>
                                    <select name="brand_id" class="form-control">
                                        <option value="">-- Select Brand --</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')<span class="field-error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Product Variations -->
                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-cubes" style="margin-right:5px;"></i>Product Variations</div>

                            <div class="variation-grid">
                                @foreach($variationTypes as $variation)
                                <label class="variation-item">
                                    <input type="checkbox" name="variations[]" value="{{ $variation }}"
                                        {{ (old('variations') && in_array($variation, old('variations'))) ? 'checked' : '' }}>
                                    {{ $variation }}
                                </label>
                                @endforeach
                            </div>
                            @error('variations')<span class="field-error" style="margin-top:4px;">{{ $message }}</span>@enderror
                            <span class="field-hint"><i class="fas fa-info-circle"></i> Select applicable variations for products in this category</span>
                        </div>

                        <!-- Description -->
                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-pen-fancy" style="margin-right:5px;"></i>Description</div>

                            <div class="form-group">
                                <textarea id="description" name="description" class="form-control"
                                    rows="4" placeholder="Provide a comprehensive description (optional)">{{ old('description') }}</textarea>
                                @error('description')<span class="field-error">{{ $message }}</span>@enderror
                                <div style="display:flex;justify-content:space-between;margin-top:3px;">
                                    <span class="field-hint">Maximum 500 characters</span>
                                    <span id="charCount" style="font-size:11px;color:#999;font-weight:bold;">0/500</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-toggle-on" style="margin-right:5px;"></i>Status & Publish</div>

                            <label class="status-box">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                    onchange="updateStatusBadge()"
                                    style="cursor:pointer;accent-color:#00bcd4;width:16px;height:16px;">
                                <span style="font-weight:bold;font-size:13px;color:#006d7a;">Active Category</span>
                                <span id="statusBadge" class="status-badge"></span>
                            </label>
                            <span class="field-hint" style="margin-top:6px;">Inactive categories will not be visible to users</span>
                        </div>

                        <!-- Buttons -->
                        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid #e0e0e0;">
                            <button type="submit" class="btn btn-primary" style="flex:1;padding:8px;">
                                <i class="fas fa-check"></i> Register Category
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary" style="flex:1;padding:8px;justify-content:center;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

       
@stop

@section('adminlte_js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const descriptionInput = document.getElementById('description');
    const charCount        = document.getElementById('charCount');

    if (descriptionInput && charCount) {
        descriptionInput.addEventListener('input', function () {
            let count = this.value.length;
            if (count > 500) { this.value = this.value.substring(0, 500); count = 500; }
            charCount.textContent = count + '/500';
        });
        charCount.textContent = descriptionInput.value.length + '/500';
    }

    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function () {
            if (!slugInput.value) {
                slugInput.value = this.value
                    .toLowerCase().trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
    }

    updateStatusBadge();
});

function updateStatusBadge() {
    const checked = document.getElementById('is_active').checked;
    const badge   = document.getElementById('statusBadge');
    badge.textContent        = checked ? 'ACTIVE' : 'INACTIVE';
    badge.style.background   = checked ? '#e8f5e9' : '#ffebee';
    badge.style.color        = checked ? '#388e3c' : '#d32f2f';
    badge.style.borderColor  = checked ? '#a5d6a7' : '#ef9a9a';
}
</script>
@stop