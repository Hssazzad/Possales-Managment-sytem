@extends('adminlte::page')

@section('title', 'Add Income Category | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-plus mr-2" style="color:#000000;"></i> Add Income Category
    </h1>
    <div>
        <a href="{{ route('income.categories') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Categories
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-plus mr-2" style="color:#000000;"></i> Create New Income Category
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                @if(session('success'))
                    <div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px; margin-bottom:20px; border-radius:4px;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('income.categories.store') }}" method="POST">
                    @csrf
                    <div style="display:flex; gap:20px;">
                        <div style="flex:1;">
                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">Category Name *</label>
                                <input type="text" name="name" required
                                       style="width:100%; padding:10px; border:2px solid #333333; border-radius:4px; color:#000000; background:#ffffff;"
                                       placeholder="Enter category name">
                                @error('name')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">Description</label>
                                <textarea name="description" rows="3"
                                          style="width:100%; padding:10px; border:2px solid #333333; border-radius:4px; color:#000000; background:#ffffff; resize:vertical;"
                                          placeholder="Enter category description"></textarea>
                                @error('description')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">Color *</label>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <input type="color" name="color" value="#6c757d" required
                                           style="width:60px; height:40px; border:2px solid #333333; border-radius:4px; cursor:pointer;">
                                    <input type="text" value="#6c757d" readonly
                                           style="padding:8px; border:2px solid #333333; border-radius:4px; color:#000000; background:#f5f5f5; width:100px;">
                                </div>
                                @error('color')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:flex; align-items:center; cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" checked
                                           style="margin-right:8px; width:16px; height:16px;">
                                    <span style="color:#000000; font-weight:600;">Active</span>
                                </label>
                                <small style="color:#666666; margin-left:24px;">Enable this category for use</small>
                            </div>

                            <div style="display:flex; gap:10px; margin-top:30px;">
                                <button type="submit" 
                                        style="padding:10px 20px; background:#000000; color:#ffffff; border:2px solid #000000; cursor:pointer; font-weight:600;">
                                    <i class="fas fa-save mr-2"></i> Save Category
                                </button>
                                <a href="{{ route('income.categories') }}" 
                                   style="padding:10px 20px; background:#ffffff; color:#000000; border:2px solid #333333; text-decoration:none; display:inline-block;">
                                    Cancel
                                </a>
                            </div>
                        </div>

                        <div style="width:300px;">
                            <div style="background:#f8f9fa; border:2px solid #333333; border-radius:4px; padding:16px;">
                                <h6 style="margin:0 0 12px 0; color:#000000; font-weight:600;">Preview</h6>
                                <div id="categoryPreview" style="background:#ffffff; border:2px solid #333333; border-radius:4px; padding:12px;">
                                    <div style="display:flex; align-items:center; margin-bottom:8px;">
                                        <div id="colorPreview" style="width:12px; height:12px; border-radius:50%; background:#6c757d; margin-right:8px;"></div>
                                        <span id="namePreview" style="font-weight:600; color:#000000;">Category Name</span>
                                    </div>
                                    <p id="descriptionPreview" style="margin:0; color:#666666; font-size:14px;">Category description will appear here</p>
                                </div>
                            </div>

                            <div style="background:#fff3cd; border:2px solid #ffeaa7; border-radius:4px; padding:16px; margin-top:16px;">
                                <h6 style="margin:0 0 8px 0; color:#856404; font-weight:600;">
                                    <i class="fas fa-info-circle mr-2"></i> Tips
                                </h6>
                                <ul style="margin:0; padding-left:20px; color:#856404; font-size:14px;">
                                    <li>Choose a unique category name</li>
                                    <li>Select a color for visual identification</li>
                                    <li>Add a clear description</li>
                                    <li>Keep categories active when in use</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="name"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');
    const colorInput = document.querySelector('input[name="color"]');
    const colorText = document.querySelector('input[type="text"][readonly]');
    const namePreview = document.getElementById('namePreview');
    const descriptionPreview = document.getElementById('descriptionPreview');
    const colorPreview = document.getElementById('colorPreview');

    // Update preview when inputs change
    nameInput.addEventListener('input', function() {
        namePreview.textContent = this.value || 'Category Name';
    });

    descriptionInput.addEventListener('input', function() {
        descriptionPreview.textContent = this.value || 'Category description will appear here';
    });

    colorInput.addEventListener('input', function() {
        colorPreview.style.background = this.value;
        colorText.value = this.value;
    });

    // Sync color text input with color picker
    colorText.addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            colorInput.value = this.value;
            colorPreview.style.background = this.value;
        }
    });
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
input:focus, textarea:focus { border-color: #000000 !important; box-shadow: none !important; outline: none; }
</style>
@stop
