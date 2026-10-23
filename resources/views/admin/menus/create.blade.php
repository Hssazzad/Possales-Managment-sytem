@extends('adminlte::page')

@section('title', 'Manage Submenus')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Manage Submenus</h1>
    <div>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Menu List
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSubmenuModal">
            <i class="fas fa-plus mr-1"></i> Add Submenu
        </button>
    </div>
</div>
@stop

@section('content')
<div id="submenu-alert"></div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:60px;">SL</th>
                        <th style="width:140px;">Submenu Code</th>
                        <th>Submenu Name</th>
                        <th>Parent Menu</th>
                        <th>URL</th>
                        <th style="width:130px;">Display Order</th>
                        <th style="width:100px;">Status</th>
                    </tr>
                </thead>
                <tbody id="submenu-list">
                    @forelse($submenus as $submenu)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-monospace">{{ $submenu->code ?? $submenu->id }}</td>
                        <td><i class="{{ $submenu->icon }} mr-2"></i>{{ $submenu->text }}</td>
                        <td>{{ $submenu->parent?->text }}</td>
                        <td><code>{{ $submenu->url }}</code></td>
                        <td>{{ $submenu->sort_order }}</td>
                        <td>
                            <span class="badge {{ $submenu->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $submenu->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-submenus">
                        <td colspan="7" class="text-center py-4 text-muted">No submenus found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubmenuModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="add-submenu-form" action="{{ route('menus.store') }}" method="POST">
                @csrf
                <input type="hidden" name="is_active" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Add Submenu</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Parent Main Menu <span class="text-danger">*</span></label>
                        <select name="parent_id" class="form-control" required>
                            <option value="">-- Select Parent Main Menu --</option>
                            @foreach($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->text }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Submenu Name <span class="text-danger">*</span></label>
                        <input type="text" name="text" class="form-control" placeholder="e.g. Users" required>
                    </div>
                    <div class="form-group">
                        <label>URL <span class="text-danger">*</span></label>
                        <input type="text" name="url" class="form-control" value="#" placeholder="e.g. admin/users" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Icon Class <span class="text-danger">*</span></label>
                        <input type="text" name="icon" class="form-control" value="fas fa-fw fa-circle" placeholder="e.g. fas fa-fw fa-users" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Submenu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
document.getElementById('add-submenu-form').addEventListener('submit', async function (event) {
    event.preventDefault();

    const form = event.currentTarget;
    const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name=_token]').value },
        body: new FormData(form),
    });
    const result = await response.json();

    if (!response.ok) {
        const message = Object.values(result.errors || {}).flat().join('<br>') || 'Unable to save submenu.';
        document.getElementById('submenu-alert').innerHTML = `<div class="alert alert-danger">${message}</div>`;
        return;
    }

    const menu = result.menu;
    const row = document.createElement('tr');
    const cell = (value) => {
        const item = document.createElement('td');
        item.textContent = value;
        row.appendChild(item);
        return item;
    };

    document.getElementById('empty-submenus')?.remove();
    cell(document.querySelectorAll('#submenu-list tr').length + 1);
    cell(menu.code || menu.id);
    const name = cell('');
    const icon = document.createElement('i');
    icon.className = `${menu.icon} mr-2`;
    name.append(icon, menu.text);
    cell(menu.parent.text);
    const url = cell('');
    const code = document.createElement('code');
    code.textContent = menu.url;
    url.appendChild(code);
    cell(menu.sort_order);
    const status = cell('');
    status.innerHTML = `<span class="badge ${menu.is_active ? 'badge-success' : 'badge-secondary'}">${menu.is_active ? 'Active' : 'Inactive'}</span>`;
    document.getElementById('submenu-list').appendChild(row);
    document.getElementById('submenu-alert').innerHTML = `<div class="alert alert-success">${result.message}</div>`;
    $('#addSubmenuModal').modal('hide');
    form.reset();
    form.querySelector('[name=url]').value = '#';
    form.querySelector('[name=icon]').value = 'fas fa-fw fa-circle';
});
</script>
@stop
