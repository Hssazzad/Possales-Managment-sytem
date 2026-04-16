@extends('adminlte::page')

@section('title', 'Add Menu')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-plus mr-2" style="color:#c0392b;"></i> Add New Menu
    </h1>
    <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card" style="border-radius:10px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06); max-width:600px;">
    <div class="card-body">
        <form action="{{ route('menus.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Menu Name <span class="text-danger">*</span></label>
                <input type="text" name="text" class="form-control @error('text') is-invalid @enderror"
                       value="{{ old('text') }}" placeholder="e.g. Dashboard">
                @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">URL <span class="text-danger">*</span></label>
                <input type="text" name="url" class="form-control @error('url') is-invalid @enderror"
                       value="{{ old('url', '#') }}" placeholder="e.g. dashboard or #">
                @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Icon Class <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="icon-preview">
                            <i class="fas fa-fw fa-circle"></i>
                        </span>
                    </div>
                    <input type="text" name="icon" id="icon-input"
                           class="form-control @error('icon') is-invalid @enderror"
                           value="{{ old('icon', 'fas fa-fw fa-circle') }}"
                           placeholder="e.g. fas fa-fw fa-home">
                </div>
                <small class="text-muted">
                    FontAwesome class দিন।
                    <a href="https://fontawesome.com/icons" target="_blank">Icons দেখুন →</a>
                </small>
                @error('icon')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="font-weight:600; font-size:0.875rem;">Parent Menu</label>
                <select name="parent_id" class="form-control">
                    <option value="">— Top Level Menu —</option>
                    @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id', request('parent_id')) == $parent->id ? 'selected' : '' }}>
                        {{ $parent->text }}
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">Submenu হলে parent select করুন</small>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="custom-control-input"
                           {{ old('is_active') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">
                        <strong>Active Status</strong>
                        <br>
                        <small class="text-muted">Enable this menu to show in navigation</small>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save mr-1"></i> Save Menu
            </button>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; color:#343a40;">
                    <i class="fas fa-plus mr-2"></i> Add New Menu
                </h5>
            </div>
            <form>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; color:#343a40;">
                    <i class="fas fa-list mr-2"></i> Current Menus
                </h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @php
                    $allMenus = App\Models\Menu::with('children')->whereNull('parent_id')->orderBy('sort_order')->get();
                @endphp
                @if($allMenus->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Menu Name</th>
                                <th>URL</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allMenus as $menu)
                                <tr>
                                    <td>{{ $menu->text }}</td>
                                    <td>{{ $menu->url }}</td>
                                    <td>
                                        <span class="badge {{ $menu->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @foreach($menu->children as $child)
                                    <tr style="background:#fafafa;">
                                        <td>↳ {{ $child->text }}</td>
                                        <td>{{ $child->url }}</td>
                                        <td>
                                            <span class="badge {{ $child->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $child->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">No menus found</p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
    document.getElementById('icon-input').addEventListener('input', function() {
        document.querySelector('#icon-preview i').className = this.value;
    });
</script>
@stop
