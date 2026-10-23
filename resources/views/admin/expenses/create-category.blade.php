@extends('adminlte::page')

@section('title')
    Add Expense Category | {{ config('app.name') }}
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-tags mr-2" style="color:#6f42c1;"></i> Add Expense Category
    </h1>
    <a href="{{ route('expenses.categories') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Categories
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-plus mr-2" style="color:#6f42c1;"></i> Add New Expense Category
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('expenses.categories.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Category Name *</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter category name" required value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="color" class="form-label">Color *</label>
                                <div class="d-flex align-items-center" style="gap:10px;">
                                    <input type="color" id="color" name="color" value="{{ old('color', '#6c757d') }}" required
                                           style="width:60px; height:40px; border:1px solid #ced4da; border-radius:4px; cursor:pointer;">
                                    <input type="text" id="colorText" value="{{ old('color', '#6c757d') }}" readonly
                                           class="form-control" style="width:100px; background:#f5f5f5;">
                                </div>
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Enter category description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold" for="is_active">Active</label>
                        </div>
                        <small class="text-muted" style="margin-left:24px; display:block;">Enable this category for use</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Category
                        </button>
                        <a href="{{ route('expenses.categories') }}" class="btn btn-secondary ml-2">
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
                    <i class="fas fa-info-circle mr-2" style="color:#6f42c1;"></i> Tips
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <ul class="mb-0">
                        <li>Choose a descriptive category name</li>
                        <li>Select a color for visual identification</li>
                        <li>Add a clear description</li>
                        <li>Keep categories active when in use</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-palette mr-2" style="color:#f59e0b;"></i> Color Presets
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <button type="button" onclick="setColor('#dc3545')" class="btn btn-sm" style="flex:1; background:#dc3545; color:#fff; border:1px solid #dc3545;">Red</button>
                    <button type="button" onclick="setColor('#28a745')" class="btn btn-sm" style="flex:1; background:#28a745; color:#fff; border:1px solid #28a745;">Green</button>
                    <button type="button" onclick="setColor('#007bff')" class="btn btn-sm" style="flex:1; background:#007bff; color:#fff; border:1px solid #007bff;">Blue</button>
                    <button type="button" onclick="setColor('#ffc107')" class="btn btn-sm" style="flex:1; background:#ffc107; color:#000; border:1px solid #ffc107;">Yellow</button>
                    <button type="button" onclick="setColor('#6c757d')" class="btn btn-sm" style="flex:1; background:#6c757d; color:#fff; border:1px solid #6c757d;">Gray</button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.querySelector('input[name="color"]');
    const colorText = document.getElementById('colorText');

    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
    });
});

function setColor(color) {
    document.querySelector('input[name="color"]').value = color;
    document.getElementById('colorText').value = color;
}
</script>
@stop
