@extends('adminlte::page')

@section('title', 'Edit Menu')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-edit mr-2" style="color:#c0392b;"></i> Edit Menu
    </h1>
    <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06); max-width:600px;">
    <div class="card-body">
        <form action="{{ route('menus.update', $menu) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Menu Name <span class="text-danger">*</span></label>
                <input type="text" name="text" class="form-control @error('text') is-invalid @enderror"
                       value="{{ old('text', $menu->text) }}">
                @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">URL <span class="text-danger">*</span></label>
                <input type="text" name="url" class="form-control @error('url') is-invalid @enderror"
                       value="{{ old('url', $menu->url) }}">
                @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Icon Class <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="icon-preview">
                            <i class="{{ $menu->icon }}"></i>
                        </span>
                    </div>
                    <input type="text" name="icon" id="icon-input"
                           class="form-control @error('icon') is-invalid @enderror"
                           value="{{ old('icon', $menu->icon) }}">
                </div>
                @error('icon')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Parent Menu</label>
                <select name="parent_id" class="form-control">
                    <option value="">— Top Level Menu —</option>
                    @foreach($parents as $parent)
                    <option value="{{ $parent->id }}"
                        {{ old('parent_id', $menu->parent_id) == $parent->id ? 'selected' : '' }}>
                        {{ $parent->text }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="custom-control-input"
                           {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">
                        <strong>Active Status</strong>
                        <br>
                        <small class="text-muted">Enable this menu to show in navigation</small>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save mr-1"></i> Update Menu
            </button>

            <a href="{{ route('menus.create') }}?parent_id={{ $menu->id }}" class="btn btn-success ml-2">
                <i class="fas fa-plus mr-1"></i> Add Submenu
            </a>
        </form>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
    document.getElementById('icon-input').addEventListener('input', function() {
        document.querySelector('#icon-preview i').className = this.value;
    });
</script>
@stop
