@extends('adminlte::page')

@section('title', 'Edit Menu')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-edit mr-2" style="color:#dc3545;"></i> Edit Menu
    </h1>
    <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')

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

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="row">

    {{-- Edit Form --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-edit mr-2" style="color:#dc3545;"></i> Edit: {{ $menu->text }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('menus.update', $menu->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            {{-- Menu Name --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">
                                    Menu Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="text"
                                       class="form-control @error('text') is-invalid @enderror"
                                       value="{{ old('text', $menu->text) }}" required>
                                @error('text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- URL --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">
                                    URL <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="url"
                                       class="form-control @error('url') is-invalid @enderror"
                                       value="{{ old('url', $menu->url) }}" required>
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sort Order --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">Sort Order</label>
                                <input type="number" name="sort_order"
                                       class="form-control"
                                       value="{{ old('sort_order', $menu->sort_order) }}" min="1">
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{-- Icon --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">
                                    Icon Class <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" name="icon" id="icon-input"
                                           class="form-control @error('icon') is-invalid @enderror"
                                           value="{{ old('icon', $menu->icon) }}" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="min-width:42px; justify-content:center;">
                                            <i id="icon-preview" class="{{ old('icon', $menu->icon) }}" style="font-size:16px;"></i>
                                        </span>
                                    </div>
                                </div>
                                @error('icon')
                                    <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <a href="https://fontawesome.com/icons" target="_blank">FontAwesome Icons →</a>
                                </small>
                            </div>

                            {{-- Parent Menu --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">Parent Menu</label>
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

                            {{-- Active Status --}}
                            <div class="form-group mt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:0.875rem;">Description</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Optional">{{ old('description', $menu->description) }}</textarea>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save mr-1"></i> Update Menu
                    </button>
                    <a href="{{ route('menus.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>

                </form>
            </div>
        </div>
    </div>

    {{-- Quick Reference --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#17a2b8;"></i> Icon Examples
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Icon</th>
                            <th>Class</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $icons = [
                            ['fas fa-fw fa-tachometer-alt', 'Dashboard'],
                            ['fas fa-fw fa-shopping-cart',  'Sales'],
                            ['fas fa-fw fa-truck',          'Purchases'],
                            ['fas fa-fw fa-box',            'Products'],
                            ['fas fa-fw fa-warehouse',      'Stock'],
                            ['fas fa-fw fa-users',          'Customers'],
                            ['fas fa-fw fa-industry',       'Suppliers'],
                            ['fas fa-fw fa-money-bill-wave','Incomes'],
                            ['fas fa-fw fa-file-invoice',   'Expenses'],
                            ['fas fa-fw fa-percent',        'Tax'],
                            ['fas fa-fw fa-cog',            'Settings'],
                            ['fas fa-fw fa-clock',          'Due List'],
                        ];
                        @endphp
                        @foreach($icons as $icon)
                        <tr style="cursor:pointer;" onclick="useIcon('{{ $icon[0] }}')">
                            <td><i class="{{ $icon[0] }}" style="color:#dc3545; font-size:16px;"></i></td>
                            <td><code style="font-size:11px;">{{ $icon[0] }}</code></td>
                            <td><small class="text-muted">{{ $icon[1] }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-2">
                    <small class="text-muted">
                        <i class="fas fa-hand-pointer mr-1"></i> Row click করলে icon select হবে
                    </small>
                </div>
            </div>
        </div>

        {{-- Current Info --}}
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-eye mr-2" style="color:#28a745;"></i> Current Info
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">ID</td>
                        <td><strong>#{{ $menu->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $menu->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated</td>
                        <td>{{ $menu->updated_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Children</td>
                        <td><span class="badge badge-info">{{ $menu->children->count() }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($menu->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>
@stop

@section('adminlte_js')
<script>
    document.getElementById('icon-input').addEventListener('input', function () {
        document.getElementById('icon-preview').className = this.value;
    });

    function useIcon(iconClass) {
        document.getElementById('icon-input').value = iconClass;
        document.getElementById('icon-preview').className = iconClass;
    }
</script>
@stop
