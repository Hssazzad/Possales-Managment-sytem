@extends('adminlte::page')

@section('title', 'Low Stock Items | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-exclamation-triangle mr-2" style="color:#e67e00;"></i> Low Stock Items
    </h1>
    <div>
        <button onclick="window.printStock()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333; margin-right:10px;">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <button onclick="window.exportToCSV()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </button>
        <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back to All Stock
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        {{-- Summary Cards --}}
        <div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:160px; background:#fff8ee; border:2px solid #ffd799; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#e67e00; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Low Stock Items</div>
                <div style="font-size:28px; font-weight:700; color:#e67e00; margin-top:4px;">{{ $products->count() }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Stock Units</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $products->sum('stock_quantity') }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Critical (2)</div>
                <div style="font-size:28px; font-weight:700; color:#cc0000; margin-top:4px;">{{ $products->where('stock_quantity', '<=', 2)->count() }}</div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#fff8ee; border-bottom:2px solid #ffd799; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#e67e00;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Low Stock Items (5 units)
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
                    <table class="table" style="margin:0; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#fff8ee; border-bottom:2px solid #ffd799;">
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">SL.</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">Image</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">Product Name</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">Code</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">Stock</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">Unit</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffd799;">Sale Price</th>
                                <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $loop->index + 1 }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #333333;">
                                    @else
                                        <div style="width:40px; height:40px; background:#f5f5f5; border:1px solid #333333; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#666666; font-size:12px;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->name }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->code ?? '-' }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; font-weight:600;
                                    color:{{ $product->stock_quantity <= 2 ? '#cc0000' : '#e67e00' }};">
                                    {{ $product->stock_quantity }}
                                </td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ $product->unit ?? 'pcs' }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333; color:#000000;">{{ number_format($product->price, 2) }}</td>
                                <td style="padding:12px; border-bottom:1px solid #333333;">
                                    <div class="btn-group btn-group-sm" style="gap:4px;">
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm" title="View" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4" style="color:#000000;">
                                    <i class="fas fa-check-circle fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                                    <h5 style="color:#000000;">No low stock items</h5>
                                    <p style="color:#666666;">All products have sufficient stock levels (6 units)</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Make functions globally available
window.exportToCSV = function() {
    try {
        const products = @json($products);
        
        if (!products || products.length === 0) {
            alert('No data available to export');
            return;
        }
        
        let csv = 'SL.,Product Name,Code,Current Stock,Unit,Sale Price,Status\n';
        
        products.forEach((product, index) => {
            const sl = index + 1;
            const name = (product.name || '').replace(/"/g, '""');
            const code = (product.code || '').replace(/"/g, '""');
            const stock = product.stock_quantity || 0;
            const unit = (product.unit || 'pcs').replace(/"/g, '""');
            const price = product.price || 0;
            const status = stock <= 2 ? 'Critical' : 'Low Stock';
            
            csv += `${sl},"${name}","${code}","${stock}","${unit}","${price}","${status}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'low_stock_' + new Date().toISOString().slice(0,10) + '.csv';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        console.log('CSV export completed');
    } catch (error) {
        console.error('CSV export error:', error);
        alert('Error exporting CSV: ' + error.message);
    }
};

window.printStock = function() {
    try {
        console.log('Print function called');
        window.print();
    } catch (error) {
        console.error('Print error:', error);
        alert('Error printing: ' + error.message);
    }
};

// Debug: Check if data is available
console.log('Products data:', @json($products));

// Auto-call export when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Low stock page loaded');
    console.log('Functions available:', typeof window.exportToCSV, typeof window.printStock);
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
@media print {
    body * { visibility: hidden; }
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .table-responsive { display: block !important; }
    .card { display: block !important; }
    .card-header { display: block !important; }
    .card-body { display: block !important; }
    table { display: block !important; }
    thead { display: table-header-group !important; }
    tbody { display: table-row-group !important; }
    tr { display: table-row !important; }
    th, td {
        display: table-cell !important;
        border: 1px solid #000 !important;
        padding: 8px !important;
        vertical-align: top !important;
    }
    th {
        background: #f5f5f5 !important;
        font-weight: bold !important;
        color: #000 !important;
    }
}
</style>
@stop
