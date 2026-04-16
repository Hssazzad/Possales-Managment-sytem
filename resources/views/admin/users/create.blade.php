@extends('adminlte::page')

@section('title', 'Add User')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-plus mr-2" style="color:#c0392b;"></i> Add New User
    </h1>
    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Enter full name">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="Enter email address">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror"
                               placeholder="Confirm password">
                        @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Roles</label>
                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        <strong>{{ $role->display_name }}</strong>
                                        <small class="text-muted d-block">{{ $role->description }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save mr-1"></i> Save User
            </button>
        </form>
    </div>
</div>
@stop
