@extends('adminlte::page')

@section('title', 'Assign Roles & Menu Access')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-user-cog mr-2" style="color:#c0392b;"></i> Assign Roles & Menu Access
    </h1>
    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Users
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

<div class="row">
    <div class="col-md-4">
        <div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-header">
                <h5 style="margin:0; color:#343a40;">
                    <i class="fas fa-users mr-2"></i> Select User
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label style="font-weight:600; font-size:0.875rem;">Select User</label>
                    <select id="userSelect" class="form-control" onchange="selectUserFromDropdown()">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-user-email="{{ $user->email }}">
                                {{ $user->name }} ({{ $user->email }})
                                @foreach($user->roles as $role)
                                    <span class="badge badge-primary mr-1">{{ $role->display_name }}</span>
                                @endforeach
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select a user to configure their role and menu access</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
            <div class="card-header">
                <h5 style="margin:0; color:#343a40;">
                    <i class="fas fa-cog mr-2"></i> Menu Access Configuration
                </h5>
            </div>
            <div class="card-body">
                <div id="userSelection" class="text-center py-5">
                    <i class="fas fa-user fa-3x d-block mb-3" style="color:#dee2e6;"></i>
                    <p class="text-muted">Select a user from the left to configure their menu access</p>
                </div>

                <form id="menuAccessForm" action="{{ route('users.update-menu-access') }}" method="POST" style="display:none;">
                    @csrf
                    <input type="hidden" name="user_id" id="selectedUserId">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 style="font-weight:600; color:#495057; margin-bottom:15px;">
                                <i class="fas fa-user-tag mr-2"></i> Role Assignment
                            </h6>
                            <div class="form-group">
                                <label style="font-weight:600; font-size:0.875rem;">Select Roles</label>
                                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                                    @foreach($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="roles[]"
                                                   value="{{ $role->id }}"
                                                   id="role_{{ $role->id }}"
                                                   data-role-name="{{ $role->name }}">
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                <strong>{{ $role->display_name }}</strong>
                                                <small class="text-muted d-block">{{ $role->description }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 style="font-weight:600; color:#495057; margin-bottom:15px;">
                                <i class="fas fa-bars mr-2"></i> Menu Access
                            </h6>
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                                @foreach($menus as $menu)
                                    <div class="mb-3">
                                        <h6 style="font-weight:600; color:#495057; margin-bottom:10px;">
                                            <i class="{{ $menu->icon }} mr-2"></i>{{ $menu->text }}
                                        </h6>
                                        @foreach($menu->children as $submenu)
                                            <div class="form-check">
                                                <input class="form-check-input menu-checkbox" type="checkbox"
                                                       name="menus[]"
                                                       value="{{ $submenu->id }}"
                                                       id="menu_{{ $submenu->id }}"
                                                       data-menu-name="{{ $submenu->text }}">
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
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save mr-1"></i> Save User Access
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" onclick="resetForm()">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('adminlte_js')
<script>
let selectedUserId = null;

function selectUserFromDropdown() {
    const select = document.getElementById('userSelect');
    const selectedOption = select.options[select.selectedIndex];

    if (select.value === '') {
        resetForm();
        return;
    }

    const userId = select.value;
    const userName = selectedOption.dataset.userName;
    const userEmail = selectedOption.dataset.userEmail;

    selectedUserId = userId;

    // Show form
    document.getElementById('userSelection').style.display = 'none';
    document.getElementById('menuAccessForm').style.display = 'block';

    // Update selected user display
    document.getElementById('selectedUserId').value = userId;

    // Load user's current roles and menu access via AJAX
    loadUserAccess(userId);
}

function loadUserAccess(userId) {
    fetch(`/admin/users/${userId}/access`)
        .then(response => response.json())
        .then(data => {
            // Clear previous selections
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
            document.querySelectorAll('.menu-checkbox').forEach(cb => cb.checked = false);

            // Set current roles
            data.roles.forEach(roleId => {
                const roleCheckbox = document.getElementById(`role_${roleId}`);
                if (roleCheckbox) roleCheckbox.checked = true;
            });

            // Set current menu access
            data.menus.forEach(menuId => {
                const menuCheckbox = document.getElementById(`menu_${menuId}`);
                if (menuCheckbox) menuCheckbox.checked = true;
            });
        })
        .catch(error => console.error('Error loading user access:', error));
}

function resetForm() {
    document.getElementById('userSelection').style.display = 'block';
    document.getElementById('menuAccessForm').style.display = 'none';
    document.getElementById('userSelect').value = '';
    document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('.menu-checkbox').forEach(cb => cb.checked = false);
    selectedUserId = null;
}
</script>

<style>
.user-item:hover {
    background-color: #f8f9fa;
    border-color: #6c757d !important;
}

.user-item {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.form-check {
    margin-bottom: 0.5rem;
}

.form-check-label {
    font-size: 0.875rem;
    margin-left: 0.5rem;
}
</style>
@stop
