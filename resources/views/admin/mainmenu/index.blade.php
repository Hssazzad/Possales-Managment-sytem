@extends('adminlte::page')

@section('title', 'Manage Main Menus')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Manage Main Menus</h1>
    <div>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Menu List
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addMainMenuModal">
            <i class="fas fa-plus mr-1"></i> Add New
        </button>
    </div>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:60px;">SL</th>
                        <th style="width:140px;">Main Menu Code</th>
                        <th>Main Menu Name</th>
                        <th>Controller Name</th>
                        <th>Main Route Name</th>
                        <th style="width:130px;">Display Order</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:190px;" class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mainMenus as $menu)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-monospace">{{ $menu->code ?? $menu->id }}</td>
                        <td>
                            <i class="{{ $menu->icon }} mr-2"></i>
                            {{ $menu->text }}
                        </td>
                        <td class="text-monospace">{{ $menu->controller_name ?: '—' }}</td>
                        <td class="text-monospace">{{ $menu->main_route ?: '—' }}</td>
                        <td>{{ $menu->sort_order }}</td>
                        <td>
                            @if($menu->is_active)
                            <span class="badge badge-success">Active</span>
                            @else
                            <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $menu->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="{{ route('submenus.index', ['parent' => $menu->id]) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-list"></i> Submenus
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No main menus found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Main Menu modal (top-level only: no parent is sent) --}}
<div class="modal fade" id="addMainMenuModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('mainmenu.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Main Menu</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Menu Name <span class="text-danger">*</span></label>
                        <input type="text" name="text" class="form-control @error('text') is-invalid @enderror"
                               value="{{ old('text') }}" placeholder="e.g. Reports" required>
                        @error('text')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Menu Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}" placeholder="e.g. 40" required>
                        @error('code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>URL <span class="text-muted">(Optional)</span></label>
                        <input type="text" name="url" class="form-control @error('url') is-invalid @enderror"
                               value="{{ old('url') }}" placeholder="Leave empty when this menu only has submenus">
                        @error('url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Icon Class <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="icon" id="icon-input"
                                   class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', 'fas fa-fw fa-circle') }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="icon-preview">
                                    <i class="fas fa-fw fa-circle"></i>
                                </span>
                            </div>
                        </div>
                        @error('icon')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Controller Name <span class="text-muted">(Optional)</span></label>
                        <input type="text" name="controller_name" class="form-control @error('controller_name') is-invalid @enderror"
                               value="{{ old('controller_name') }}" placeholder="e.g. AccountingController">
                        @error('controller_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Main Route Name <span class="text-muted">(Optional, unique)</span></label>
                        <input type="text" name="main_route" class="form-control @error('main_route') is-invalid @enderror"
                               value="{{ old('main_route') }}" placeholder="e.g. accounting">
                        @error('main_route')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Display Order <span class="text-muted">(Optional)</span></label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order') }}" min="1" placeholder="Leave empty for automatic order">
                        @error('sort_order')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit modals (main menus only) --}}
@foreach($mainMenus as $menu)
@include('admin.mainmenu.partials.edit-modal', ['item' => $menu, 'parents' => $parents])
@endforeach

@stop

@section('adminlte_js')
<script>
    document.getElementById('icon-input')?.addEventListener('input', function() {
        document.querySelector('#icon-preview i').className = this.value;
    });
</script>
@stop
