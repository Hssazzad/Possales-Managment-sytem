@extends('adminlte::page')

@section('title', 'Add New Menu')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-plus-circle mr-2" style="color:#c0392b;"></i> Add New Menu
    </h1>
    <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Menu List
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

<div class="row">
    {{-- Create Form --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-plus-circle mr-2" style="color:#c0392b;"></i> Menu Information
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('menus.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            {{-- Menu Name --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">
                                    Menu Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="text"
                                       class="form-control @error('text') is-invalid @enderror"
                                       placeholder="e.g. Dashboard, Sales, etc."
                                       value="{{ old('text') }}" required>
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
                                       placeholder="e.g. dashboard, /sales"
                                       value="{{ old('url') }}" required>
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sort Order --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">Sort Order</label>
                                <input type="number" name="sort_order"
                                       class="form-control"
                                       value="{{ old('sort_order', 1) }}" min="1">
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
                                           placeholder="e.g. fas fa-fw fa-dashboard"
                                           value="{{ old('icon') }}" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="min-width:42px; justify-content:center;">
                                            <i id="icon-preview" class="{{ old('icon') }}" style="font-size:16px;"></i>
                                        </span>
                                    </div>
                                </div>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <a href="https://fontawesome.com/icons" target="_blank">FontAwesome Icons</a>
                                </small>
                            </div>

                            {{-- Parent Menu --}}
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.875rem;">Parent Menu</label>
                                <select name="parent_id" class="form-control">
                                    <option value="">-- Top Level Menu --</option>
                                    @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->text }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty for top level menu</small>
                            </div>

                            {{-- Active Status --}}
                            <div class="form-group mt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
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
                                  placeholder="Optional">{{ old('description') }}</textarea>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save mr-1"></i> Create Menu
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
                            <td><i class="{{ $icon[0] }}" style="color:#c0392b; font-size:16px;"></i></td>
                            <td><code style="font-size:11px;">{{ $icon[0] }}</code></td>
                            <td><small class="text-muted">{{ $icon[1] }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-2">
                    <small class="text-muted">
                        <i class="fas fa-hand-pointer mr-1"></i> Click row to select icon
                    </small>
                </div>
            </div>
        </div>

        {{-- Quick Tips --}}
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-lightbulb mr-2" style="color:#ffc107;"></i> Quick Tips
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-2"></i>
                        <strong>Parent Menu:</strong> Leave empty for top-level
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-2"></i>
                        <strong>URL:</strong> Use relative paths like "dashboard"
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-2"></i>
                        <strong>Sort Order:</strong> Higher numbers appear last
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success mr-2"></i>
                        <strong>Icon:</strong> Use FontAwesome classes
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success mr-2"></i>
                        <strong>Active:</strong> Uncheck to hide menu
                    </li>
                </ul>
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

    // Auto-generate sort order based on parent selection
    document.querySelector('select[name="parent_id"]').addEventListener('change', function() {
        const sortOrderInput = document.querySelector('input[name="sort_order"]');
        const parentSelect = this;
        
        if (parentSelect.value) {
            fetch(`/admin/menus/get-next-sort-order?parent_id=${parentSelect.value}`)
                .then(response => response.json())
                .then(data => {
                    sortOrderInput.value = data.nextSortOrder;
                })
                .catch(error => console.error('Error getting sort order:', error));
        } else {
            sortOrderInput.value = 1; // Reset to 1 for top-level menus
        }
    });
</script>

@section('adminlte_css')
<style>
    .main-sidebar, .main-sidebar::before { background-color: #2d3436 !important; }
    .brand-link { background-color: #232b2b !important; border-bottom: 1px solid #1a1f1f !important; }
    .brand-link .brand-text { color: #fff !important; font-weight:700 !important; }
    .nav-sidebar .nav-item > .nav-link { color: rgba(255,255,255,0.80) !important; }
    .nav-sidebar .nav-item > .nav-link .nav-icon { color: rgba(255,255,255,0.50) !important; }
    .nav-sidebar .nav-item > .nav-link:hover { background-color: rgba(255,255,255,0.08) !important; color:#fff !important; }
    .nav-sidebar .nav-item > .nav-link.active { background-color: #c0392b !important; color:#fff !important; border-radius:4px !important; }
    .nav-sidebar .nav-item > .nav-link.active .nav-icon { color:#fff !important; }
    .nav-treeview { background-color: rgba(0,0,0,0.18) !important; }
    .nav-sidebar .nav-treeview .nav-link { color: rgba(255,255,255,0.65) !important; }
    .nav-sidebar .nav-treeview .nav-link:hover { background-color: rgba(255,255,255,0.08) !important; color:#fff !important; }
    .nav-sidebar .nav-treeview .nav-link.active { background-color: #c0392b !important; color:#fff !important; }
    .main-header.navbar { background-color: #fff !important; border-bottom: 1px solid #e9ecef !important; }
    .main-header.navbar .nav-link { color: #6c757d !important; }
</style>
@stop
