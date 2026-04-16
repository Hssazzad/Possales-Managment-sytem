@extends('adminlte::page')

@section('title', 'Edit Rack | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-th-large mr-2" style="color:#fd7e14;"></i> Edit Rack
    </h1>
    <div>
        <a href="{{ route('racks.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Racks
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
                    <i class="fas fa-th-large mr-2" style="color:#fd7e14;"></i>
                    Rack Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('racks.update', $rack->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Rack Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter rack name" required value="{{ old('name', $rack->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code" class="form-label">Rack Code (Optional)</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" placeholder="auto-generated" value="{{ old('code', $rack->code) }}">
                                <small class="text-muted">Leave blank to auto-generate from name</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" placeholder="Enter rack location (e.g., Warehouse A, Section B)" value="{{ old('location', $rack->location) }}">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter rack description">{{ old('description', $rack->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $rack->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Rack
                        </button>
                        <a href="{{ route('racks.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fas fa-info-circle mr-2" style="color:#fd7e14;"></i>
                    Rack Guidelines
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Edit rack name or code</li>
                        <li>Update location if moved</li>
                        <li>Toggle active status</li>
                        <li>Changes apply immediately</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Changing rack details will not affect products stored in this rack.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_css')
<style>
.form-group { margin-bottom: 1rem; }
.alert { margin-bottom: 1rem; }
</style>
@stop
