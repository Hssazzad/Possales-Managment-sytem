@extends('adminlte::page')

@section('title', 'Add Permission')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-plus mr-2" style="color:#c0392b;"></i> Add New Permission
    </h1>
    <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06); max-width:600px;">
    <div class="card-body">
        <form action="{{ route('permissions.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Permission Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="e.g. view-products">
                <small class="text-muted">Use format: action-resource (e.g., view-users, create-sales)</small>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Display Name <span class="text-danger">*</span></label>
                <input type="text" name="display_name" class="form-control @error('display_name') is-invalid @enderror"
                       value="{{ old('display_name') }}" placeholder="e.g. View Products">
                @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Permission description...">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save mr-1"></i> Save Permission
            </button>
        </form>
    </div>
</div>
@stop
