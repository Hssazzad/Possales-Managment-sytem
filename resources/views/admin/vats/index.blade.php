@extends('adminlte::page')

@section('title', 'VAT Rates | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-percentage mr-2" style="color:#000000;"></i> VAT Rates
    </h1>
    <div>
        <a href="{{ route('vats.create') }}" class="btn btn-secondary btn-sm" style="background:#000000; color:#ffffff; border:2px solid #000000; margin-right:10px;">
            <i class="fas fa-plus mr-1"></i> Add VAT Rate
        </a>
        <button onclick="window.printVats()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333; margin-right:10px;">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <button onclick="window.exportToCSV()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </button>
    </div>
</div>
@stop

@section('content')
@if(session('success'))
    <div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px; margin-bottom:20px; border-radius:4px;">
        {{ session('success') }}
    </div>
@endif

{{-- Summary Cards --}}
<div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
    <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
        <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total VAT Rates</div>
        <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $vats->count() }}</div>
    </div>
    <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
        <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Active Rates</div>
        <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $vats->where('is_active', true)->count() }}</div>
    </div>
    <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
        <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Inactive Rates</div>
        <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $vats->where('is_active', false)->count() }}</div>
    </div>
</div>

{{-- VAT Rates Table --}}
<div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
    <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
        <h5 style="margin:0; font-weight:600; color:#000000;">
            <i class="fas fa-list mr-2" style="color:#000000;"></i> VAT Rates Details
        </h5>
    </div>
    <div class="card-body" style="padding:0;">
        @if($vats->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:left; color:#000000; font-weight:600;">SL.</th>
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:left; color:#000000; font-weight:600;">VAT Name</th>
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:left; color:#000000; font-weight:600;">Rate</th>
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:left; color:#000000; font-weight:600;">Description</th>
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:left; color:#000000; font-weight:600;">Status</th>
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:left; color:#000000; font-weight:600;">Created At</th>
                            <th style="padding:12px; border-bottom:2px solid #333333; text-align:center; color:#000000; font-weight:600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vats as $index => $vat)
                        <tr style="{{ $index % 2 == 0 ? 'background:#ffffff;' : 'background:#f8f9fa;' }}">
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000;">{{ $index + 1 }}</td>
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000;">{{ $vat->name }}</td>
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000; font-weight:600;">{{ $vat->formatted_rate }}</td>
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000; font-weight:600;">{{ number_format($vat->rate_as_decimal, 2) }}</td>
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000;">{{ $vat->description }}</td>
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000;">
                                <span style="padding:4px 8px; border-radius:12px; font-size:12px; {{ $vat->is_active ? 'background:#d4edda; color:#155724;' : 'background:#f8d9fa; color:#666666;' }}">
                                    {{ $vat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="padding:12px; border-bottom:1px solid #333333; color:#000000;">{{ $vat->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding:12px; border-bottom:1px solid #333333; text-align:center;">
                                <div class="btn-group btn-group-sm" style="gap:4px;">
                                    <a href="{{ route('vats.show', $vat->id) }}" class="btn btn-sm" title="View" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px; text-decoration:none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('vats.edit', $vat->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px; text-decoration:none;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('vats.destroy', $vat->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this VAT rate?')" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#cc0000; border:2px solid #cc0000; padding:6px 10px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center; padding:60px 20px; color:#000000;">
                <i class="fas fa-percentage fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                <h5 style="color:#000000;">No VAT rates found</h5>
                <p style="color:#666666;">Start adding VAT rates to see them here.</p>
            </div>
        @endif
    </div>
</div>
@stop

@section('adminlte_js')
<script>
window.printVats = function() {
    window.print();
}

window.exportToCSV = function() {
    let vats = @json($vats);
    let csv = 'SL,VAT Name,Rate,Description,Status,Created At\n';

    vats.forEach((vat, index) => {
        csv += `${index + 1},"${vat.name}","${vat.formatted_rate}","${vat.description}","${vat.is_active ? 'Active' : 'Inactive'}","${vat.created_at->format('M d, Y H:i')}"\n`;
    });

    let blob = new Blob([csv], { type: 'text/csv' });
    let url = window.URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = 'vat_rates_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
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
    th, td { border: 1px solid #000 !important; }
}
</style>
@stop
