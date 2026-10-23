@extends('adminlte::page')

@section('title', 'VAT Rate Details | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-eye mr-2" style="color:#000000;"></i> VAT Rate Details
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
    <div class="col-md-8">
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-percentage mr-2" style="color:#000000;"></i> {{ $vat->name }}
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#666666; font-weight:600;">VAT Name</label>
                            <div style="padding:12px; background:#f8f9fa; border:1px solid #333333; border-radius:4px; color:#000000; font-size:16px; font-weight:600;">
                                {{ $vat->name }}
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#666666; font-weight:600;">VAT Rate</label>
                            <div style="padding:12px; background:#f8f9fa; border:1px solid #333333; border-radius:4px; color:#000000; font-size:18px; font-weight:600;">
                                {{ $vat->formatted_rate }}
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#666666; font-weight:600;">Description</label>
                            <div style="padding:12px; background:#f8f9fa; border:1px solid #333333; border-radius:4px; color:#000000; font-size:14px;">
                                {{ $vat->description ?: 'No description provided' }}
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#666666; font-weight:600;">Status</label>
                            <div style="padding:12px; background:#f8f9fa; border:1px solid #333333; border-radius:4px; color:#000000;">
                                <span style="padding:4px 8px; border-radius:12px; font-size:12px; {{ $vat->is_active ? 'background:#d4edda; color:#155724;' : 'background:#f8d9fa; color:#666666;' }}">
                                    {{ $vat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#666666; font-weight:600;">Created At</label>
                            <div style="padding:12px; background:#f8f9fa; border:1px solid #333333; border-radius:4px; color:#000000; font-size:14px;">
                                {{ $vat->created_at->format('M d, Y H:i:s') }}
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#666666; font-weight:600;">Updated At</label>
                            <div style="padding:12px; background:#f8f9fa; border:1px solid #333333; border-radius:4px; color:#000000; font-size:14px;">
                                {{ $vat->updated_at->format('M d, Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div style="width:300px;">
                    <div style="background:#f8f9fa; border:2px solid #333333; border-radius:4px; padding:16px;">
                        <h6 style="margin:0 0 12px 0; color:#000000; font-weight:600;">
                            <i class="fas fa-info-circle mr-2"></i> Quick Actions
                        </h6>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <a href="{{ route('vats.edit', $vat->id) }}" style="padding:10px; background:#ffffff; border:1px solid #333333; color:#000000; text-decoration:none; display:block; text-align:center;">
                                <i class="fas fa-edit mr-2"></i> Edit VAT Rate
                            </a>
                            <button onclick="window.print()" style="padding:10px; background:#ffffff; border:1px solid #333333; color:#000000; cursor:pointer; width:100%;">
                                <i class="fas fa-print mr-2"></i> Print Details
                            </button>
                        </div>

                        <div style="background:#fff3cd; border:2px solid #ffeaa7; border-radius:4px; padding:16px; margin-top:16px;">
                            <h6 style="margin:0 0 8px 0; color:#856404; font-weight:600;">
                                <i class="fas fa-calculator mr-2"></i> VAT Calculation Example
                            </h6>
                            <div style="background:#ffffff; border:1px solid #333333; border-radius:4px; padding:12px; font-size:14px;">
                                <div style="margin-bottom:8px;"><strong>Base Amount:</strong> ৳1,000.00</div>
                                <div style="margin-bottom:8px;"><strong>Rate:</strong> {{ $vat->formatted_rate }}</div>
                                <div style="margin-bottom:8px;"><strong>Rate (Decimal):</strong> {{ number_format($vat->rate_as_decimal, 2) }}</div>
                                <div style="margin-bottom:8px;"><strong>VAT Amount:</strong> ৳150.00</div>
                                <div style="margin-bottom:8px;"><strong>Total Amount:</strong> ৳1,150.00</div>
                                <div style="margin-bottom:8px;"><strong>Example:</strong> For a ৳1,000 product at {{ $vat->formatted_rate }} VAT, the VAT amount is ৳150.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
window.printVat = function() {
    window.print();
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
@media print {
    .no-print { display: none !important; }
    .card { border: 2px solid #000 !important; }
    .btn { display: none !important; }
}
</style>
@stop
