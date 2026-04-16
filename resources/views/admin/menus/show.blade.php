@extends('adminlte::page')

@section('title', 'Menu Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-eye mr-2" style="color:#17a2b8;"></i> Menu Details
    </h1>
    <div>
        <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-warning btn-sm mr-2">
            <i class="fas fa-edit mr-1"></i> Edit Menu
        </a>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="{{ $menu->icon }} mr-2" style="color:#17a2b8;"></i>
                    {{ $menu->text }}
                    <span class="badge {{ $menu->is_active ? 'badge-success' : 'badge-danger' }} ml-2">
                        {{ $menu->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" style="width:120px;">Menu ID</td>
                                <td><strong>#{{ $menu->id }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Menu Name</td>
                                <td><strong>{{ $menu->text }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">URL</td>
                                <td>
                                    <code>{{ $menu->url }}</code>
                                    @if($menu->url !== '#')
                                        <a href="{{ url($menu->url) }}" target="_blank" class="ml-2">
                                            <i class="fas fa-external-link-alt text-info"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Icon Class</td>
                                <td>
                                    <code>{{ $menu->icon }}</code>
                                    <span class="ml-2">
                                        <i class="{{ $menu->icon }}" style="color:#17a2b8;"></i>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" style="width:120px;">Parent Menu</td>
                                <td>
                                    @if($menu->parent)
                                        <a href="{{ route('menus.show', $menu->parent->id) }}" class="text-info">
                                            {{ $menu->parent->text }}
                                        </a>
                                    @else
                                        <span class="badge badge-primary">Top Level Menu</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sort Order</td>
                                <td><strong>{{ $menu->sort_order }}</strong></td>
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
                            <tr>
                                <td class="text-muted">Description</td>
                                <td>{{ $menu->description ?: 'No description' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($menu->description)
                <div class="mt-3">
                    <h6 style="font-weight:600; color:#495057;">Description</h6>
                    <p class="text-muted">{{ $menu->description }}</p>
                </div>
                @endif

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-calendar-plus mr-1"></i>
                            Created: {{ $menu->created_at->format('d M Y, h:i A') }}
                        </small>
                    </div>
                    <div class="col-md-6 text-right">
                        <small class="text-muted">
                            <i class="fas fa-calendar-edit mr-1"></i>
                            Updated: {{ $menu->updated_at->format('d M Y, h:i A') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Side Information --}}
    <div class="col-md-4">
        {{-- Children Menus --}}
        @if($menu->children->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-sitemap mr-2" style="color:#28a745;"></i>
                    Submenus ({{ $menu->children->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>URL</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menu->children as $child)
                        <tr>
                            <td>
                                <i class="{{ $child->icon }} mr-2" style="font-size:12px;"></i>
                                <a href="{{ route('menus.show', $child->id) }}" class="text-info">
                                    {{ $child->text }}
                                </a>
                            </td>
                            <td><code style="font-size:11px;">{{ $child->url }}</code></td>
                            <td>
                                <span class="badge {{ $child->is_active ? 'badge-success' : 'badge-danger' }}" style="font-size:10px;">
                                    {{ $child->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-tools mr-2" style="color:#ffc107;"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit mr-1"></i> Edit Menu
                    </a>
                    
                    <form action="{{ route('menus.toggle', $menu->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm btn-block">
                            <i class="fas fa-power-off mr-1"></i>
                            {{ $menu->is_active ? 'Deactivate' : 'Activate' }} Menu
                        </button>
                    </form>

                    @if($menu->children->count() == 0)
                    <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this menu?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm btn-block">
                            <i class="fas fa-trash mr-1"></i> Delete Menu
                        </button>
                    </form>
                    @else
                    <button class="btn btn-secondary btn-sm btn-block" disabled>
                        <i class="fas fa-trash mr-1"></i> Cannot Delete (Has Submenus)
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Menu Statistics --}}
        <div class="card mt-3">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-chart-bar mr-2" style="color:#6f42c1;"></i>
                    Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="mb-3">
                            <h4 class="text-primary">{{ $menu->children->count() }}</h4>
                            <small class="text-muted">Children</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <h4 class="text-{{ $menu->is_active ? 'success' : 'danger' }}">
                                {{ $menu->is_active ? 'Active' : 'Inactive' }}
                            </h4>
                            <small class="text-muted">Status</small>
                        </div>
                    </div>
                </div>
                
                <div class="progress" style="height: 8px;">
                    @if($menu->is_active)
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                    @else
                    <div class="progress-bar bg-danger" style="width: 100%"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('adminlte_js')
<script>
    // Add any JavaScript if needed
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
