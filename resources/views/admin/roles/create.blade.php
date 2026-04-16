@extends('adminlte::page')

@section('title', 'Add Role')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-plus mr-2" style="color:#c0392b;"></i> Add New Role
    </h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. manager">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" class="form-control @error('display_name') is-invalid @enderror"
                               value="{{ old('display_name') }}" placeholder="e.g. Manager">
                        @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Role description...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Permissions</label>
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="mb-3">
                                    <h6 style="font-weight:600; text-transform:capitalize; color:#495057;">{{ $group }}</h6>
                                    @foreach($groupPermissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->display_name }}
                                                <small class="text-muted d-block">{{ $permission->description }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save mr-1"></i> Save Role
            </button>
        </form>
    </div>
</div>
@stop
