@extends('adminlte::page')

@section('title', 'Edit Shelf | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-th mr-2" style="color:#20c997;"></i> Edit Shelf
    </h1>
    <div>
        <a href="{{ route('shelves.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Shelves
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
                    <i class="fas fa-th mr-2" style="color:#20c997;"></i>
                    Shelf Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('shelves.update', $shelf->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Shelf Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter shelf name" required value="{{ old('name', $shelf->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code" class="form-label">Shelf Code (Optional)</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" placeholder="auto-generated" value="{{ old('code', $shelf->code) }}">
                                <small class="text-muted">Leave blank to auto-generate from name</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rack_id" class="form-label">Rack</label>
                        <select class="form-control @error('rack_id') is-invalid @enderror" id="rack_id" name="rack_id">
                            <option value="">Select Rack</option>
                            @foreach($racks as $rack)
                                <option value="{{ $rack->id }}" {{ old('rack_id', $shelf->rack_id) == $rack->id ? 'selected' : '' }}>{{ $rack->name }}</option>
                            @endforeach
                        </select>
                        @error('rack_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter shelf description">{{ old('description', $shelf->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $shelf->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Shelf
                        </button>
                        <a href="{{ route('shelves.index') }}" class="btn btn-secondary ml-2">
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
                    <i class="fas fa-info-circle mr-2" style="color:#20c997;"></i>
                    Shelf Guidelines
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i> Quick Tips</h6>
                    <ul class="mb-0">
                        <li>Edit shelf name or code</li>
                        <li>Change associated rack</li>
                        <li>Update description</li>
                        <li>Toggle active status</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important</h6>
                    <p class="mb-0">Changing shelf details will not affect products stored in this shelf.</p>
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
