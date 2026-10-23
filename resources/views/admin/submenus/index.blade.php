@extends('adminlte::page')

@section('title', 'Manage Submenus')

@section('adminlte_css')
@vite('resources/css/ui-table.css')
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Manage Submenus</h1>
    <div>
        <a href="{{ route('mainmenu.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Main Menu
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSubmenuModal">
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

{{-- Filter + search --}}
<div class="card">
    <div class="card-body">
        <form action="{{ route('submenus.index') }}" method="GET" class="form-inline">
            <label class="mr-2" for="parent">Parent Menu</label>
            <select name="parent" id="parent" class="form-control mr-2">
                <option value="">-- All Parents --</option>
                @foreach($parents as $parent)
                <option value="{{ $parent->id }}"
                    {{ $selectedParent && $selectedParent->id == $parent->id ? 'selected' : '' }}>
                    {{ $parent->text }} ({{ $parent->code ?? $parent->id }})
                </option>
                @endforeach
            </select>
            <div class="input-group mr-2">
                <input type="text" name="q" class="form-control" placeholder="Search submenu..."
                       value="{{ $search ?? '' }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            @if(($search ?? '') !== '' || $selectedParent)
            <a href="{{ route('submenus.index') }}" class="btn btn-secondary ml-2">Reset</a>
            @endif
        </form>
    </div>
</div>

{{-- Parent context (shown when a parent is selected) --}}
@if($selectedParent)
<div class="alert alert-info">
    <strong>Parent Menu:</strong> {{ $selectedParent->text }}
    <span class="ml-3"><strong>Main Menu Code:</strong>
        <span class="text-monospace">{{ $selectedParent->code ?? $selectedParent->id }}</span>
    </span>
</div>
@endif

{{-- Submenu list --}}
<div class="card">
    <div class="card-body">
        <x-ui.table class="w-full">
            <x-ui.table-caption>A list of submenus{{ $selectedParent ? ' under ' . $selectedParent->text . '.' : '.' }}</x-ui.table-caption>
            <x-ui.table-header>
                <x-ui.table-row>
                    <x-ui.table-head>SL</x-ui.table-head>
                    <x-ui.table-head>Submenu Code</x-ui.table-head>
                    <x-ui.table-head>Submenu Name</x-ui.table-head>
                    <x-ui.table-head>Route</x-ui.table-head>
                    @if(!$selectedParent)
                    <x-ui.table-head>Parent Menu</x-ui.table-head>
                    @endif
                    <x-ui.table-head>Display Order</x-ui.table-head>
                    <x-ui.table-head>Status</x-ui.table-head>
                    <x-ui.table-head class="text-right">Action</x-ui.table-head>
                </x-ui.table-row>
            </x-ui.table-header>
            <x-ui.table-body>
                @forelse($submenus as $submenu)
                <x-ui.table-row>
                    <x-ui.table-cell>{{ $loop->iteration }}</x-ui.table-cell>
                    <x-ui.table-cell class="font-mono">{{ $submenu->code ?? $submenu->id }}</x-ui.table-cell>
                    <x-ui.table-cell class="font-medium">
                        <i class="{{ $submenu->icon }} mr-2"></i>{{ $submenu->text }}
                    </x-ui.table-cell>
                    <x-ui.table-cell><code>{{ $submenu->url }}</code></x-ui.table-cell>
                    @if(!$selectedParent)
                    <x-ui.table-cell>
                        @if($submenu->parent)
                        {{ $submenu->parent->text }}
                        @else
                        <span class="text-gray-400">&mdash;</span>
                        @endif
                    </x-ui.table-cell>
                    @endif
                    <x-ui.table-cell>{{ $submenu->sort_order }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        @if($submenu->is_active)
                        <x-ui.badge variant="outline" dot="bg-emerald-500">Active</x-ui.badge>
                        @else
                        <x-ui.badge variant="outline" dot="bg-gray-400">Inactive</x-ui.badge>
                        @endif
                    </x-ui.table-cell>
                    <x-ui.table-cell class="text-right">
                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $submenu->id }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form action="{{ route('mainmenu.destroy', $submenu) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this submenu?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </x-ui.table-cell>
                </x-ui.table-row>
                @empty
                <x-ui.table-row>
                    <x-ui.table-cell colspan="8" class="text-center py-4 text-gray-400">No submenus found</x-ui.table-cell>
                </x-ui.table-row>
                @endforelse
            </x-ui.table-body>
            <x-ui.table-footer>
                <x-ui.table-row>
                    <x-ui.table-cell colspan="{{ $selectedParent ? 6 : 7 }}">Total Submenus</x-ui.table-cell>
                    <x-ui.table-cell class="text-right">{{ $submenus->count() }}</x-ui.table-cell>
                </x-ui.table-row>
            </x-ui.table-footer>
        </x-ui.table>
    </div>
</div>

{{-- Add Submenu modal --}}
<div class="modal fade" id="addSubmenuModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('mainmenu.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Submenu</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Parent Main Menu <span class="text-danger">*</span></label>
                        <select name="parent_id" class="form-control @error('parent_id') is-invalid @enderror" required>
                            <option value="">-- Select Parent Main Menu --</option>
                            @foreach($parents as $parent)
                            <option value="{{ $parent->id }}"
                                {{ ($selectedParent && $selectedParent->id == $parent->id) || old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->text }} ({{ $parent->code ?? $parent->id }})
                            </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Submenu Name <span class="text-danger">*</span></label>
                        <input type="text" name="text" class="form-control @error('text') is-invalid @enderror"
                               value="{{ old('text') }}" placeholder="e.g. Users" required>
                        @error('text')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>URL <span class="text-danger">*</span></label>
                        <input type="text" name="url" class="form-control @error('url') is-invalid @enderror"
                               value="{{ old('url', '#') }}" placeholder="e.g. admin/users or #" required>
                        @error('url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Icon Class <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="icon" id="submenu-icon-input"
                                   class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', 'fas fa-fw fa-circle') }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="submenu-icon-preview">
                                    <i class="fas fa-fw fa-circle"></i>
                                </span>
                            </div>
                        </div>
                        @error('icon')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Submenu code and display order are assigned automatically on save.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit modals (submenus) --}}
@foreach($submenus as $submenu)
@include('admin.mainmenu.partials.edit-modal', ['item' => $submenu, 'parents' => $parents])
@endforeach

@stop

@section('adminlte_js')
<script>
    document.getElementById('submenu-icon-input')?.addEventListener('input', function() {
        document.querySelector('#submenu-icon-preview i').className = this.value;
    });
</script>
@stop
