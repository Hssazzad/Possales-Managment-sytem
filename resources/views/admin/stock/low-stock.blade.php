@extends('adminlte::page')

@section('title', 'Low Stock Items | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-exclamation-triangle mr-2" style="color:#e67e00;"></i> Low Stock Items
    </h1>
    <div>
        <button onclick="window.printStock()" class="btn btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333; margin-right:10px;">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <button onclick="window.exportToCSV()" class="btn btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </button>
        <a href="{{ route('stock.index') }}" class="btn btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
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
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Critical (≤2)</div>
                <div style="font-size:28px; font-weight:700; color:#cc0000; margin-top:4px;">{{ $products->where('stock_quantity', '<=', 2)->count() }}</div>
            </div>
        </div>

        {{-- Search/Filter Bar --}}
        <div style="background:#ffffff; border:2px solid #333333; border-radius:4px; padding:14px 16px; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:200px;">
                <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#999999; font-size:13px;"></i>
                <input type="text" id="stockSearch" placeholder="Search by name or code..." onkeyup="filterTable()"
                    style="width:100%; padding:8px 10px 8px 32px; border:1.5px solid #333333; border-radius:3px; font-size:13px; color:#000000; background:#ffffff; box-sizing:border-box;">
            </div>
            <div>
                <select id="stockFilter" onchange="filterTable()"
                    style="padding:8px 12px; border:1.5px solid #333333; border-radius:3px; font-size:13px; color:#000000; background:#ffffff;">
                    <option value="all">All Low Stock</option>
                    <option value="critical">Critical (≤2)</option>
                    <option value="low">Low Stock (3-5)</option>
                </select>
            </div>
            <div style="font-size:13px; color:#666666;">
                Showing <span id="rowCount">{{ $products->count() }}</span> low stock items
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#fff8ee; border-bottom:2px solid #ffd799; padding:14px 16px;">
                <h5 style="margin:0; font-weight:600; color:#e67e00; font-size:14px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Low Stock Items (≤5 units)
                </h5>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-responsive">
                    <table id="stockTable" style="margin:0; border-collapse:collapse; width:100%;">
                        <thead>
                            <tr style="background:#fff8ee; border-bottom:2px solid #ffd799;">
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px; white-space:nowrap;">SL.</th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px;">Image</th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px; cursor:pointer;" onclick="sortTable(2)">Product Name <i class="fas fa-sort" style="font-size:11px; color:#999;"></i></th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px; cursor:pointer;" onclick="sortTable(3)">Code <i class="fas fa-sort" style="font-size:11px; color:#999;"></i></th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px; cursor:pointer;" onclick="sortTable(4)">Stock <i class="fas fa-sort" style="font-size:11px; color:#999;"></i></th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px;">Unit</th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px; cursor:pointer;" onclick="sortTable(6)">Sale Price <i class="fas fa-sort" style="font-size:11px; color:#999;"></i></th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; border-right:1px solid #ffd799; font-size:13px;">Status</th>
                                <th style="padding:11px 14px; font-weight:600; color:#000; font-size:13px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stockBody">
                            @forelse($products as $product)
                            <tr class="stock-row" data-stock="{{ $product->stock_quantity }}" style="border-bottom:1px solid #eee;">
                                <td style="padding:11px 14px; border-right:1px solid #eee; font-size:13px; color:#555;">{{ $loop->index + 1 }}</td>
                                <td style="padding:8px 14px; border-right:1px solid #eee;">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:38px; height:38px; object-fit:cover; border-radius:3px; border:1px solid #ccc; display:block;">
                                    @else
                                        <div style="width:38px; height:38px; background:#f0f0f0; border:1px solid #ccc; border-radius:3px; display:flex; align-items:center; justify-content:center;">
                                            <i class="fas fa-box" style="color:#bbb; font-size:14px;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:11px 14px; border-right:1px solid #eee; font-size:13px; color:#000; font-weight:500;">{{ $product->name }}</td>
                                <td style="padding:11px 14px; border-right:1px solid #eee; font-size:13px; color:#444; font-family:monospace;">{{ $product->code ?? '-' }}</td>
                                <td style="padding:11px 14px; border-right:1px solid #eee; font-size:13px; font-weight:600;
                                    color:{{ $product->stock_quantity <= 2 ? '#cc0000' : '#e67e00' }};">
                                    {{ $product->stock_quantity }}
                                </td>
                                <td style="padding:11px 14px; border-right:1px solid #eee; font-size:13px; color:#444;">{{ $product->unit ?? 'pcs' }}</td>
                                <td style="padding:11px 14px; border-right:1px solid #eee; font-size:13px; color:#000; font-weight:500;">
                                    ৳ {{ number_format($product->price, 2) }}
                                </td>
                                <td style="padding:11px 14px; border-right:1px solid #eee;">
                                    @if($product->stock_quantity <= 2)
                                        <span style="background:#ffeeee; color:#cc0000; border:1px solid #ffcccc; padding:3px 8px; border-radius:3px; font-size:11px; font-weight:600; white-space:nowrap;">CRITICAL</span>
                                    @else
                                        <span style="background:#fff8ee; color:#e67e00; border:1px solid #ffd799; padding:3px 8px; border-radius:3px; font-size:11px; font-weight:600; white-space:nowrap;">LOW STOCK</span>
                                    @endif
                                </td>
                                <td style="padding:8px 14px;">
                                    <div style="display:flex; gap:6px;">
                                        <a href="{{ route('products.show', $product->id) }}" title="View"
                                            style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; background:#fff; color:#000; border:1.5px solid #333; border-radius:3px; text-decoration:none;">
                                            <i class="fas fa-eye" style="font-size:12px;"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" title="Edit"
                                            style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; background:#fff; color:#000; border:1.5px solid #333; border-radius:3px; text-decoration:none;">
                                            <i class="fas fa-edit" style="font-size:12px;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" style="text-align:center; padding:48px; color:#999;">
                                    <i class="fas fa-check-circle" style="font-size:36px; display:block; margin-bottom:12px; opacity:0.2;"></i>
                                    <div style="font-size:15px; font-weight:600; color:#666;">No low stock items</div>
                                    <div style="font-size:13px; color:#999; margin-top:8px;">All products have sufficient stock levels (≥6 units)</div>
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
// Pre-calculate totals once using plain for-loop (ES5 safe)
var TOTAL_COUNT = PRODUCTS.length;
var TOTAL_STOCK = 0;
var CRITICAL_COUNT = 0;
var LOW_COUNT = 0;

for (var _i = 0; _i < PRODUCTS.length; _i++) {
    var _p   = PRODUCTS[_i];
    var _qty = parseInt(_p.stock_quantity) || 0;
    TOTAL_STOCK += _qty;
    if (_qty <= 2) CRITICAL_COUNT++;
    else LOW_COUNT++;
}

// ── SEARCH + FILTER ──────────────────────────────────────────────────
function filterTable() {
    var search = document.getElementById('stockSearch').value.toLowerCase();
    var filter = document.getElementById('stockFilter').value;
    var rows   = document.querySelectorAll('#stockBody .stock-row');
    var vis    = 0;
    rows.forEach(function(row) {
        var name  = row.cells[2].textContent.toLowerCase();
        var code  = row.cells[3].textContent.toLowerCase();
        var stock = parseInt(row.getAttribute('data-stock')) || 0;
        var ms    = name.indexOf(search) !== -1 || code.indexOf(search) !== -1;
        var mf    = filter === 'all' ? true
                  : filter === 'critical' ? (stock > 0 && stock <= 2)
                  : filter === 'low' ? (stock > 2 && stock <= 5) : true;
        row.style.display = (ms && mf) ? '' : 'none';
        if (ms && mf) vis++;
    });
    document.getElementById('rowCount').textContent = vis;
}

// ── SORT ──────────────────────────────────────────────────────────────
var sortDir = {};
function sortTable(colIdx) {
    var tbody = document.getElementById('stockBody');
    var rows  = Array.prototype.slice.call(tbody.querySelectorAll('.stock-row'));
    var asc   = !sortDir[colIdx];
    sortDir   = {};
    sortDir[colIdx] = asc;
    rows.sort(function(a, b) {
        var ta = a.cells[colIdx].textContent.trim();
        var tb = b.cells[colIdx].textContent.trim();
        var na = parseFloat(ta.replace(/[^\d.]/g, ''));
        var nb = parseFloat(tb.replace(/[^\d.]/g, ''));
        if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
        return asc ? ta.localeCompare(tb) : tb.localeCompare(ta);
    });
    rows.forEach(function(r) { tbody.appendChild(r); });
}

// ── ROW HOVER ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stock-row').forEach(function(row) {
        row.addEventListener('mouseenter', function() { row.style.background = '#f9f9f9'; });
        row.addEventListener('mouseleave', function() { row.style.background = ''; });
    });
});

// ── PRINT ─────────────────────────────────────────────────────────────
window.printStock = function() {
    var now    = new Date();
    var nowStr = now.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' })
               + ' ' + now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' });

    var totalValueStr = TOTAL_STOCK.toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });

    // Build table rows using string concatenation — NO template literals inside document.write
    var rowsHtml = '';
    for (var i = 0; i < PRODUCTS.length; i++) {
        var p     = PRODUCTS[i];
        var qty   = parseInt(p.stock_quantity) || 0;
        var price = parseFloat(p.price)        || 0;
        var priceStr = price.toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
        var bg    = (i % 2 === 0) ? '#ffffff' : '#fafafa';

        var stockColor, statusLabel, statusBg, statusColor, statusBorder;
        if (qty <= 2) {
            stockColor = '#cc0000'; statusLabel = 'CRITICAL';
            statusBg = '#ffeeee'; statusColor = '#cc0000'; statusBorder = '#ffcccc';
        } else {
            stockColor = '#e67e00'; statusLabel = 'LOW STOCK';
            statusBg = '#fff8ee'; statusColor = '#e67e00'; statusBorder = '#ffd799';
        }

        var imgHtml = p.image_url
            ? '<img src="' + p.image_url + '" style="width:32px;height:32px;object-fit:cover;border-radius:2px;border:1px solid #ccc;">'
            : '<div style="width:32px;height:32px;background:#f0f0f0;border:1px solid #ccc;border-radius:2px;display:inline-block;"></div>';

        rowsHtml = rowsHtml
            + '<tr style="background:' + bg + ';">'
            + '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;color:#666;">' + (i + 1) + '</td>'
            + '<td style="padding:5px 10px;border:1px solid #ccc;text-align:center;">' + imgHtml + '</td>'
            + '<td style="padding:7px 10px;border:1px solid #ccc;font-weight:500;">' + (p.name || '') + '</td>'
            + '<td style="padding:7px 10px;border:1px solid #ccc;font-family:monospace;font-size:12px;">' + (p.code || '-') + '</td>'
            + '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;font-weight:700;color:' + stockColor + ';">' + qty + '</td>'
            + '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;">' + (p.unit || 'pcs') + '</td>'
            + '<td style="padding:7px 10px;border:1px solid #ccc;text-align:right;font-weight:500;">৳ ' + priceStr + '</td>'
            + '<td style="padding:5px 10px;border:1px solid #ccc;text-align:center;">'
            + '<span style="padding:2px 7px;border-radius:2px;font-size:10px;font-weight:700;'
            + 'background:' + statusBg + ';color:' + statusColor + ';border:1px solid ' + statusBorder + ';">'
            + statusLabel + '</span></td>'
            + '</tr>';
    }

    if (!rowsHtml) {
        rowsHtml = '<tr><td colspan="8" style="text-align:center;padding:24px;color:#999;">No low stock items found</td></tr>';
    }

    var win = window.open('', '_blank', 'width=1100,height=750');
    if (!win) { alert('Please allow popups for this site to use Print.'); return; }

    // Write head
    win.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Low Stock Items</title><style>');
    win.document.write('*{box-sizing:border-box;margin:0;padding:0;}');
    win.document.write('body{font-family:Arial,sans-serif;font-size:13px;color:#000;background:#fff;padding:24px 32px;}');
    win.document.write('.hdr{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid #ffd799;}');
    win.document.write('.hdr h1{font-size:20px;font-weight:700;color:#e67e00;}');
    win.document.write('.hdr .meta{font-size:12px;color:#555;text-align:right;line-height:1.8;}');
    win.document.write('.srow{display:flex;gap:12px;margin-bottom:18px;}');
    win.document.write('.sbox{flex:1;border:1px solid #ccc;padding:10px 14px;border-radius:3px;}');
    win.document.write('.slb{font-size:10px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:.4px;}');
    win.document.write('.svl{font-size:18px;font-weight:700;color:#000;margin-top:2px;}');
    win.document.write('.svl.red{color:#cc0000;}');
    win.document.write('.svl.orange{color:#e67e00;}');
    win.document.write('table{width:100%;border-collapse:collapse;margin-bottom:20px;}');
    win.document.write('thead tr{background:#fff8ee;border-bottom:2px solid #ffd799;}');
    win.document.write('th{padding:9px 10px;font-size:12px;font-weight:700;border:1px solid #ffd799;text-align:left;white-space:nowrap;}');
    win.document.write('.ftr{border-top:2px solid #ffd799;padding-top:12px;display:flex;justify-content:space-between;font-size:11px;color:#666;}');
    win.document.write('@media print{@page{margin:10mm;size:A4 landscape;}}');
    win.document.write('</style></head><body>');

    // Header
    win.document.write('<div class="hdr">');
    win.document.write('<div><h1>Low Stock Items Report</h1><div style="font-size:13px;color:#555;margin-top:4px;">Items requiring immediate restocking (≤5 units)</div></div>');
    win.document.write('<div class="meta"><strong>Printed:</strong> ' + nowStr + '<br><strong>Total Low Stock:</strong> ' + TOTAL_COUNT + '<br><strong>Critical Items:</strong> ' + CRITICAL_COUNT + '</div>');
    win.document.write('</div>');

    // Summary row
    win.document.write('<div class="srow">');
    win.document.write('<div class="sbox"><div class="slb">Low Stock Items</div><div class="svl orange">' + TOTAL_COUNT + '</div></div>');
    win.document.write('<div class="sbox"><div class="slb">Total Stock Units</div><div class="svl">' + TOTAL_STOCK + '</div></div>');
    win.document.write('<div class="sbox"><div class="slb">Critical Items</div><div class="svl red">' + CRITICAL_COUNT + '</div></div>');
    win.document.write('</div>');

    // Table
    win.document.write('<table><thead><tr>');
    win.document.write('<th style="width:38px;">SL.</th>');
    win.document.write('<th style="width:46px;">Image</th>');
    win.document.write('<th>Product Name</th>');
    win.document.write('<th style="width:100px;">Code</th>');
    win.document.write('<th style="width:70px;text-align:center;">Stock</th>');
    win.document.write('<th style="width:55px;text-align:center;">Unit</th>');
    win.document.write('<th style="width:100px;text-align:right;">Sale Price</th>');
    win.document.write('<th style="width:105px;text-align:center;">Status</th>');
    win.document.write('</tr></thead><tbody>' + rowsHtml + '</tbody>');

    // Footer text
    win.document.write('<div class="ftr">');
    win.document.write('<span>PosSales &mdash; Inventory Management System</span>');
    win.document.write('<span>Report generated: ' + nowStr + '</span>');
    win.document.write('</div>');

    // Auto-print script — escape closing tag so Blade/browser doesn't break
    win.document.write('<scr' + 'ipt>window.onload=function(){window.print();};<\/scr' + 'ipt>');
    win.document.write('</body></html>');
    win.document.close();
};

// ── EXPORT CSV ────────────────────────────────────────────────────────
window.exportToCSV = function() {
    if (!PRODUCTS || PRODUCTS.length === 0) {
        alert('No data available to export');
        return;
    }
    function esc(val) { return '"' + String(val || '').replace(/"/g, '""') + '"'; }
    var csv = 'SL.,Product Name,Code,Current Stock,Unit,Sale Price,Status\n';
    for (var i = 0; i < PRODUCTS.length; i++) {
        var p   = PRODUCTS[i];
        var qty = parseInt(p.stock_quantity) || 0;
        var st  = qty <= 2 ? 'Critical' : 'Low Stock';
        csv += (i+1) + ',' + esc(p.name) + ',' + esc(p.code) + ',' + qty + ','
             + esc(p.unit || 'pcs') + ',' + parseFloat(p.price||0).toFixed(2) + ',' + esc(st) + '\n';
    }
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'low_stock_' + new Date().toISOString().slice(0,10) + '.csv';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};
</script>
@stop

@section('adminlte_css')
<style>
body { background: #f4f4f4 !important; }
.content-wrapper { background: #f4f4f4 !important; }
#stockTable th { transition: background .15s; }
#stockTable th:hover { background: #ebebeb; }
</style>
@stop
