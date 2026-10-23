@extends('adminlte::page')

@section('title', 'Add VAT Rate | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-plus mr-2" style="color:#000000;"></i> Add VAT Rate
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
                    <i class="fas fa-plus mr-2" style="color:#000000;"></i> Add New VAT Rate
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                @if(session('success'))
                    <div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px; margin-bottom:20px; border-radius:4px;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('vats.store') }}" method="POST">
                    @csrf
                    <div style="display:flex; gap:20px;">
                        <div style="flex:1;">
                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">VAT Name *</label>
                                <input type="text" name="name" required
                                       style="width:100%; padding:10px; border:2px solid #333333; border-radius:4px; color:#000000; background:#ffffff;"
                                       placeholder="Enter VAT rate name (e.g., Standard VAT, Reduced VAT)">
                                @error('name')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:block; margin-bottom:8px; color:#000000; font-weight:600;">VAT Rate (%) *</label>
                                <input type="number" name="rate" step="0.01" min="0" max="100" required
                                       style="width:100%; padding:10px; border:2px solid #333333; border-radius:4px; color:#000000; background:#ffffff;"
                                       placeholder="Enter VAT rate (e.g., 15.00 for 15%)">
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
                                          placeholder="Enter description (optional)"></textarea>
                                @error('description')
                                    <div style="color:#dc3545; font-size:14px; margin-top:5px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="margin-bottom:20px;">
                                <label style="display:flex; align-items:center; cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" checked
                                           style="margin-right:8px; width:16px; height:16px;">
                                    <span style="color:#000000; font-weight:600;">Active</span>
                                </label>
                                <small style="color:#666666; margin-left:24px; display:block;">Enable this VAT rate for use</small>
                            </div>

                            <div style="display:flex; gap:10px; margin-top:30px;">
                                <button type="submit"
                                        style="padding:10px 20px; background:#000000; color:#ffffff; border:2px solid #000000; cursor:pointer; font-weight:600;">
                                    <i class="fas fa-save mr-2"></i> Save VAT Rate
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
                                    <i class="fas fa-info-circle mr-2"></i> Tips
                                </h6>
                                <ul style="margin:0; padding-left:20px; color:#666666; font-size:14px;">
                                    <li>Enter a descriptive name for the VAT rate</li>
                                    <li>Use percentage format (e.g., 15.00 for 15%)</li>
                                    <li>Add clear description for documentation</li>
                                    <li>Keep VAT rates active when in use</li>
                                    <li>Standard rates: 15%, 10%, 5%, 0%</li>
                                </ul>
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
