@extends('adminlte::page')

@section('title', 'Permission Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-key mr-2" style="color:#c0392b;"></i> Permission Management
    </h1>
    <a href="{{ route('permissions.create') }}" class="btn btn-danger btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Permission
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
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Name</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Display Name</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Description</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Roles</th>
                    <th class="py-3" style="color:#6c757d; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                <tr>
                    <td class="pl-4">{{ $loop->iteration }}</td>
                    <td><code>{{ $permission->name }}</code></td>
                    <td>{{ $permission->display_name }}</td>
                    <td>{{ $permission->description ?? '-' }}</td>
                    <td>
                        <span class="badge badge-info">{{ $permission->roles_count }}</span>
                    </td>
                    <td>
                        <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-warning mr-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this permission?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-key fa-2x d-block mb-2" style="color:#dee2e6;"></i>
                        <span class="text-muted">No permissions found</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
