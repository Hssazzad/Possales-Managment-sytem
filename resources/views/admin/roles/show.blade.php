@extends('adminlte::page')

@section('title', 'Role Details - ' . $role->display_name)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-user-tag mr-2" style="color:#c0392b;"></i> {{ $role->display_name }}
    </h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-header">
                <h5 style="margin:0; color:#343a40;">
                    <i class="fas fa-info-circle mr-2"></i> Role Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td style="width:120px; font-weight:600;">Name:</td>
                        <td>{{ $role->name }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Display Name:</td>
                        <td>{{ $role->display_name }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Description:</td>
                        <td>{{ $role->description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Users Count:</td>
                        <td>
                            <span class="badge badge-info">{{ $role->users_count }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Permissions:</td>
                        <td>
                            <span class="badge badge-primary">{{ $role->permissions->count() }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-header">
                <h5 style="margin:0; color:#343a40;">
                    <i class="fas fa-key mr-2"></i> Menu Access Control
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('roles.update', $role) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $role->name }}">
                    <input type="hidden" name="display_name" value="{{ $role->display_name }}">
                    <input type="hidden" name="description" value="{{ $role->description }}">
                    
                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Select User by Email</label>
                        <select name="user_email" class="form-control" id="userSelect">
                            <option value="">-- Select User --</option>
                            @foreach(App\Models\User::all() as $user)
                                <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a user to assign this role</small>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:600; font-size:0.875rem;">Menu Access</label>
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                            @foreach(\App\Models\Menu::whereNull('parent_id')->orderBy('sort_order')->get() as $menu)
                                <div class="mb-3">
                                    <h6 style="font-weight:600; color:#495057; margin-bottom:10px;">
                                        <i class="{{ $menu->icon }} mr-2"></i>{{ $menu->text }}
                                    </h6>
                                    @foreach($menu->children as $submenu)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="menus[]" 
                                                   value="{{ $submenu->id }}" 
                                                   id="menu_{{ $submenu->id }}"
                                                   @if($role->permissions->contains('name', 'access-' . str_slug($submenu->text, '-'))) checked @endif>
                                            <label class="form-check-label" for="menu_{{ $submenu->id }}">
                                                {{ $submenu->text }}
                                                <small class="text-muted d-block">{{ $submenu->url }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save mr-1"></i> Update Role & Menu Access
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <div class="card-header">
        <h5 style="margin:0; color:#343a40;">
            <i class="fas fa-list mr-2"></i> Current Permissions
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($role->permissions->groupBy(function($permission) {
                return explode('-', $permission->name)[0];
            }) as $group => $permissions)
            <div class="col-md-4">
                <h6 style="font-weight:600; text-transform:capitalize; color:#495057;">{{ $group }}</h6>
                @foreach($permissions as $permission)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               checked disabled>
                        <label class="form-check-label">
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

@stop

@section('adminlte_js')
<script>
document.getElementById('userSelect').addEventListener('change', function() {
    const userEmail = this.value;
    if (userEmail) {
        // You can add AJAX call here to load user's current role and menu access
        console.log('Selected user:', userEmail);
    }
});
</script>
@stop
