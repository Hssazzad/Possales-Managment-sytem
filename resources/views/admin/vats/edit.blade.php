@extends('adminlte::page')

@section('title', 'Edit VAT Rate | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-edit mr-2" style="color:#000000;"></i> Edit VAT Rate
    </h1>
    <div>
        <a href="{{ route('vats.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to VAT Rates
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-edit mr-2" style="color:#000000;"></i> Edit VAT Rate: {{ $vat->name }}
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                @if(session('success'))
                    <div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px; margin-bottom:20px; border-radius:4px;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('vats.update', $vat->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div style="display:flex; gap:20px;">
                        <div style="flex:1;">
                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">VAT Name *</label>
                                <input type="text" name="name" value="{{ old('name', $vat->name) }}" required
                                       style="width:100%; padding:10px; border:2px solid #333333; border-radius:4px; color:#000000; background:#ffffff;"
                                       placeholder="Enter VAT rate name">
                                @error('name')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">VAT Rate (%) *</label>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <input type="number" name="rate" step="0.01" min="0" max="100" value="{{ old('rate', $vat->rate) }}" required
                                           style="width:60px; height:40px; border:2px solid #333333; border-radius:4px; cursor:pointer;">
                                    <input type="text" value="{{ old('rate', $vat->rate) }}" readonly
                                           style="padding:8px; border:2px solid #333333; border-radius:4px; color:#000000; background:#f5f5f5; width:100px;">
                                </div>
                                @error('rate')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">VAT Rate (Decimal)</label>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <input type="number" name="rate" step="0.01" min="0" max="100" value="{{ old('rate', $vat->rate) }}" required
                                           style="width:60px; height:40px; border:2px solid #333333; border-radius:4px; cursor:pointer;">
                                    <input type="text" value="{{ old('rate', $vat->rate) }}" readonly
                                           style="padding:8px; border:2px solid #333333; border-radius:4px; color:#000000; background:#f5f5f5; width:100px;">
                                </div>
                                @error('rate')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">Description</label>
                                <textarea name="description" rows="3"
                                          style="width:100%; padding:10px; border:2px solid #333333; border-radius:4px; color:#000000; background:#ffffff; resize:vertical;"
                                          placeholder="Enter description (optional)">{{ old('description', $vat->description) }}</textarea>
                                @error('description')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:flex; align-items:center; cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vat->is_active) ? 'checked' : '' }}
                                           style="margin-right:8px; width:16px; height:16px;">
                                    <span style="color:#000000; font-weight:600;">Active</span>
                                </label>
                                <small style="color:#666666; margin-left:24px; display:block;">Enable this VAT rate for use</small>
                            </div>

                            <div style="display:flex; gap:10px; margin-top:30px;">
                                <button type="submit"
                                        style="padding:10px 20px; background:#000000; color:#ffffff; border:2px solid #000000; cursor:pointer; font-weight:600;">
                                    <i class="fas fa-save mr-2"></i> Update VAT Rate
                                </button>
                                <a href="{{ route('vats.index') }}"
                                   style="padding:10px 20px; background:#ffffff; color:#000000; border:2px solid #333333; text-decoration:none; display:inline-block;">
                                    Cancel
                                </a>
                            </div>
                        </div>

                        <div style="width:300px;">
                            <div style="background:#f8f9fa; border:2px solid #333333; border-radius:4px; padding:16px;">
                                <h6 style="margin:0 0 12px 0; color:#000000; font-weight:600;">
                                    <i class="fas fa-info-circle mr-2"></i> Current VAT Rate Details
                                </h6>
                                <div style="background:#ffffff; border:1px solid #333333; border-radius:4px; padding:12px; font-size:14px;">
                                    <div style="display:flex; align-items:center; margin-bottom:8px;">
                                        <div style="width:12px; height:12px; border-radius:50%; background:#28a745; margin-right:8px;"></div>
                                        <span style="font-weight:600;">{{ $vat->name }}</span>
                                    </div>
                                    <div style="margin-bottom:8px;"><strong>Rate:</strong> {{ $vat->formatted_rate }}</div>
                                    <div style="margin-bottom:8px;"><strong>Status:</strong> {{ $vat->is_active ? 'Active' : 'Inactive' }}</div>
                                    <div><strong>Created:</strong> {{ $vat->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>

                            <div style="background:#fff3cd; border:2px solid #ffeaa7; border-radius:4px; padding:16px; margin-top:16px;">
                                <h6 style="margin:0 0 8px 0; color:#856404; font-weight:600;">
                                    <i class="fas fa-calculator mr-2"></i> Quick Rates
                                </h6>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                    <button type="button" onclick="setRate(15)" style="padding:8px; background:#ffffff; border:1px solid #333333; cursor:pointer; font-size:12px;">15%</button>
                                    <button type="button" onclick="setRate(10)" style="padding:8px; background:#ffffff; border:1px solid #333333; cursor:pointer; font-size:12px;">10%</button>
                                    <button type="button" onclick="setRate(5)" style="padding:8px; background:#ffffff; border:1px solid #333333; cursor:pointer; font-size:12px;">5%</button>
                                    <button type="button" onclick="setRate(0)" style="padding:8px; background:#ffffff; border:1px solid #333333; cursor:pointer; font-size:12px;">0%</button>
                                    <button type="button" onclick="setRate(25)" style="padding:8px; background:#ffffff; border:1px solid #333333; cursor:pointer; font-size:12px;">25%</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
function setRate(rate) {
    document.querySelector('input[name="rate"]').value = rate;
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
input:focus, select:focus, textarea:focus { border-color: #000000 !important; box-shadow: none !important; outline: none; }
</style>
@stop
