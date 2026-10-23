@extends('adminlte::page')

@section('title', 'Manage Main Menus')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Manage Main Menus</h1>
    <div>
        <a href="{{ route('submenus.index') }}" class="btn btn-info btn-sm"><i class="fas fa-list mr-1"></i> Submenus</a>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#parentModal"><i class="fas fa-plus mr-1"></i> Add Main Menu</button>
    </div>
</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover table-sm mb-0">
    <thead class="thead-light"><tr><th>SL</th><th>Parent Code</th><th>Parent Name</th><th>Controller Name</th><th>Main Route</th><th>Position</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>@forelse($parents as $parent)<tr>
        <td>{{ $loop->iteration }}</td><td>{{ $parent->parentcode }}</td><td><i class="{{ $parent->icon }} mr-2"></i>{{ $parent->parentname }}</td><td>{{ $parent->controllername ?: '—' }}</td><td>@if($parent->mainroute)<code>{{ $parent->mainroute }}</code>@else<span class="badge badge-warning">Set route</span>@endif</td><td>{{ $parent->position }}</td><td><span class="badge {{ $parent->status ? 'badge-success' : 'badge-secondary' }}">{{ $parent->status ? 'Active' : 'Inactive' }}</span></td>
        <td class="text-nowrap"><button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editParent{{ $parent->id }}"><i class="fas fa-edit"></i></button><form class="d-inline" action="{{ route('mainmenu.destroy', $parent) }}" method="POST" onsubmit="return confirm('Delete this main menu and its submenus?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form></td>
    </tr>@empty<tr><td colspan="8" class="text-center py-4 text-muted">No main menus found</td></tr>@endforelse</tbody>
</table></div></div></div>

@include('admin.menu-manager.parent-form', ['modalId' => 'parentModal', 'action' => route('mainmenu.store'), 'parent' => null])
@foreach($parents as $parent)@include('admin.menu-manager.parent-form', ['modalId' => 'editParent' . $parent->id, 'action' => route('mainmenu.update', $parent), 'parent' => $parent])@endforeach
@stop
