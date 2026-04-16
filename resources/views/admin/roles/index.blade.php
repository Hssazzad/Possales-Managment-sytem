@extends('adminlte::page')

@section('title', 'Role Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-user-tag mr-2" style="color:#c0392b;"></i> Role Management
    </h1>
    <a href="{{ route('roles.create') }}" class="btn btn-danger btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Role
    </a>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:0.875rem;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th class="pl-4 py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">#</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Role Name</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Display Name</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Users</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Permissions</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td class="pl-4">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $role->name }}</strong>
                        @if($role->description)
                            <br><small class="text-muted">{{ $role->description }}</small>
                        @endif
                    </td>
                    <td>{{ $role->display_name }}</td>
                    <td>
                        <span class="badge badge-info">{{ $role->users_count }}</span>
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $role->permissions->count() }}</span>
                    </td>
                    <td>
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning mr-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this role?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-user-tag fa-2x d-block mb-2" style="color:#dee2e6;"></i>
                        <span class="text-muted">No roles found</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
