@extends('adminlte::page')

@section('title', 'Low Stock Items | PosSales')

@section('content_header')
<div style="background: linear-gradient(to right, #003d5c 0%, #00516d 100%); border-bottom: 4px solid #d4af37; padding: 0;">
    <div style="max-width: 100%; padding: 1.5rem 2rem;">
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <p style="margin: 0; color: #d4af37; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">INVENTORY ALERT SYSTEM</p>
                    <h1 style="margin: 0.5rem 0 0 0; color: white; font-size: 2rem; font-weight: 700; font-family: Georgia, serif;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 1rem; color: #d4af37;"></i>
                        Low Stock Items Monitor
                    </h1>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #d4af37; font-size: 0.8rem; font-weight: 600;">Alert System</p>
                    <p style="margin: 0.25rem 0 0 0; color: #a8a8a8; font-size: 0.75rem;">{{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
        <div style="padding-top: 1rem; border-top: 1px solid #00516d; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="{{ route('dashboard') }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-home"></i> Home
                </a>
                <span style="color: #666;"><i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i></span>
                <a href="{{ route('stock.index') }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-warehouse"></i> Stock
                </a>
                <span style="color: #666;"><i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i></span>
                <span style="color: #e5e5e5; font-size: 0.85rem;">Low Stock Items</span>
            </nav>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button onclick="window.printStock()" style="background: #1976d2; color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 2px; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-print" style="margin-right: 0.5rem;"></i> Print
                </button>
                <button onclick="window.exportToCSV()" style="background: #4caf50; color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 2px; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-download" style="margin-right: 0.5rem;"></i> Export CSV
                </button>
                <a href="{{ route('stock.index') }}" style="background: #e0e0e0; color: #003d5c; padding: 0.6rem 1.2rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center;">
                    <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div style="background: #f5f5f5; padding: 2rem 0; min-height: calc(100vh - 200px);">
    <div style="max-width: 100%; margin: 0 auto; padding: 0 1rem;">

        {{-- Stats Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="background:white;border:1px solid #d0d0d0;border-radius:2px;box-shadow:0 2px 4px rgba(0,0,0,0.08);overflow:hidden;">
                <div style="background:linear-gradient(135deg,#f57c00,#e65100);padding:1.2rem;text-align:center;">
                    <p style="margin:0;color:white;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">
                        <i class="fas fa-exclamation-circle"></i> Total Low Stock Rows
                    </p>
                </div>
                <div style="padding:1.5rem;text-align:center;">
                    <p style="margin:0;color:#f57c00;font-size:2.5rem;font-weight:700;">{{ $all->count() }}</p>
                    <small style="color:#999;">products &amp; variations</small>
                </div>
            </div>

            <div style="background:white;border:1px solid #d0d0d0;border-radius:2px;box-shadow:0 2px 4px rgba(0,0,0,0.08);overflow:hidden;">
                <div style="background:linear-gradient(135deg,#d32f2f,#c62828);padding:1.2rem;text-align:center;">
                    <p style="margin:0;color:white;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">
                        <i class="fas fa-exclamation-triangle"></i> Critical (≤2)
                    </p>
                </div>
                <div style="padding:1.5rem;text-align:center;">
                    <p style="margin:0;color:#d32f2f;font-size:2.5rem;font-weight:700;">{{ $all->where('stock_quantity', '<=', 2)->count() }}</p>
                    <small style="color:#999;">urgent restocking</small>
                </div>
            </div>

            <div style="background:white;border:1px solid #d0d0d0;border-radius:2px;box-shadow:0 2px 4px rgba(0,0,0,0.08);overflow:hidden;">
                <div style="background:linear-gradient(135deg,#7b1fa2,#6a1b9a);padding:1.2rem;text-align:center;">
                    <p style="margin:0;color:white;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">
                        <i class="fas fa-tags"></i> Variation Rows
                    </p>
                </div>
                <div style="padding:1.5rem;text-align:center;">
                    <p style="margin:0;color:#7b1fa2;font-size:2.5rem;font-weight:700;">{{ $all->where('is_variation', true)->count() }}</p>
                    <small style="color:#999;">variation low stock</small>
                </div>
            </div>

            <div style="background:white;border:1px solid #d0d0d0;border-radius:2px;box-shadow:0 2px 4px rgba(0,0,0,0.08);overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1976d2,#1565c0);padding:1.2rem;text-align:center;">
                    <p style="margin:0;color:white;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">
                        <i class="fas fa-warehouse"></i> Total Units
                    </p>
                </div>
                <div style="padding:1.5rem;text-align:center;">
                    <p style="margin:0;color:#1976d2;font-size:2.5rem;font-weight:700;">{{ $all->sum('stock_quantity') }}</p>
                    <small style="color:#999;">in low stock items</small>
                </div>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div style="background:white;border:1px solid #d0d0d0;border-radius:2px;padding:1.5rem;margin-bottom:2rem;box-shadow:0 2px 4px rgba(0,0,0,0.08);">
            <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
                <div style="display:flex;align-items:center;background:white;border:1px solid #bdbdbd;border-radius:2px;padding:0.75rem 1rem;flex:1;min-width:250px;">
                    <i class="fas fa-search" style="color:#999;margin-right:0.75rem;font-size:0.9rem;"></i>
                    <input type="text" id="stockSearch" placeholder="Search by product name, code or variation..."
                        onkeyup="filterTable()"
                        style="flex:1;border:none;outline:none;font-size:0.95rem;background:transparent;">
                </div>
                <select id="stockFilter" onchange="filterTable()"
                    style="padding:0.75rem 1rem;border:1px solid #bdbdbd;border-radius:2px;font-size:0.9rem;background:white;color:#003d5c;outline:none;cursor:pointer;">
                    <option value="all">All Low Stock</option>
                    <option value="critical">Critical (≤2)</option>
                    <option value="low">Low (3–5)</option>
                    <option value="variation">Variations Only</option>
                    <option value="product">Products Only</option>
                </select>
                <div style="font-size:0.9rem;color:#666;white-space:nowrap;">
                    Showing <span id="rowCount" style="font-weight:700;color:#003d5c;">{{ $all->count() }}</span> items
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div style="background:white;border:1px solid #d0d0d0;box-shadow:0 2px 4px rgba(0,0,0,0.08);">
            <div style="background:linear-gradient(to right,#f57c00,#e65100);padding:1.5rem;border-bottom:3px solid #d4af37;">
                <h2 style="margin:0;color:white;font-size:1.3rem;font-weight:700;">
                    <i class="fas fa-exclamation-triangle" style="margin-right:0.75rem;"></i>
                    Low Stock Items (≤5 Units) — Products &amp; Variations
                </h2>
            </div>

            @if($all->count() > 0)
            <div style="overflow-x:auto;">
                <table id="stockTable" style="width:100%;border-collapse:collapse;min-width:900px;">
                    <thead>
                        <tr style="background:linear-gradient(to right,#f57c00,#e65100);border-bottom:2px solid #d4af37;">
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:center;width:50px;">#</th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:center;width:50px;">IMG</th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:left;cursor:pointer;" onclick="sortTable(2)">Product Name <i class="fas fa-sort"></i></th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:left;width:120px;">Variation</th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:left;width:100px;cursor:pointer;" onclick="sortTable(4)">Code <i class="fas fa-sort"></i></th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:center;width:110px;cursor:pointer;" onclick="sortTable(5)">Stock <i class="fas fa-sort"></i></th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:center;width:80px;">Unit</th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:right;width:120px;cursor:pointer;" onclick="sortTable(7)">Price <i class="fas fa-sort"></i></th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:center;width:110px;">Status</th>
                            <th style="padding:1rem;color:white;font-size:0.85rem;text-transform:uppercase;text-align:center;width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="stockBody">
                        @foreach($all as $row)
                        <tr class="stock-row"
                            data-stock="{{ $row->stock_quantity }}"
                            data-type="{{ $row->is_variation ? 'variation' : 'product' }}"
                            style="border-bottom:1px solid #e0e0e0;"
                            onmouseover="this.style.backgroundColor='#f5f5f5';"
                            onmouseout="this.style.backgroundColor='white';">

                            <td style="padding:0.9rem;text-align:center;color:#003d5c;font-weight:700;">{{ $loop->iteration }}</td>

                            <td style="padding:0.7rem;text-align:center;">
                                @php
                                    $img = $row->image_url ?? $row->image ?? null;
                                    $imgSrc = $img ? asset('storage/' . $img) : null;
                                @endphp
                                @if($imgSrc)
                                    <img src="{{ $imgSrc }}" style="width:34px;height:34px;object-fit:cover;border-radius:2px;border:1px solid #bdbdbd;" onerror="this.style.display='none'">
                                @else
                                    <div style="width:34px;height:34px;background:#f0f0f0;display:inline-flex;align-items:center;justify-content:center;border-radius:2px;border:1px solid #bdbdbd;">
                                        <i class="fas fa-box" style="color:#999;font-size:13px;"></i>
                                    </div>
                                @endif
                            </td>

                            <td style="padding:0.9rem;color:#003d5c;font-weight:600;">{{ $row->name }}</td>

                            <td style="padding:0.9rem;">
                                @if($row->is_variation)
                                    <span style="background:#ede7f6;color:#4527a0;padding:0.3rem 0.7rem;border-radius:20px;font-size:0.8rem;font-weight:700;white-space:nowrap;">
                                        <i class="fas fa-tag" style="font-size:0.7rem;margin-right:3px;"></i>
                                        {{ $row->variation_label }}
                                    </span>
                                @else
                                    <span style="color:#bdbdbd;font-size:0.8rem;">—</span>
                                @endif
                            </td>

                            <td style="padding:0.9rem;font-family:monospace;color:#003d5c;font-weight:600;">{{ $row->code ?? '-' }}</td>

                            <td style="padding:0.9rem;text-align:center;">
                                <span style="background:{{ ($row->stock_quantity ?? 0) <= 2 ? '#ffebee' : '#fff3e0' }};color:{{ ($row->stock_quantity ?? 0) <= 2 ? '#d32f2f' : '#f57c00' }};padding:0.4rem 0.8rem;border-radius:20px;font-weight:700;display:inline-block;">
                                    {{ $row->stock_quantity ?? 0 }}
                                </span>
                            </td>

                            <td style="padding:0.9rem;text-align:center;color:#003d5c;">{{ $row->unit ?? 'pcs' }}</td>

                            <td style="padding:0.9rem;text-align:right;color:#003d5c;font-weight:700;">
                                ৳{{ number_format($row->sale_price ?? $row->price ?? 0, 2) }}
                            </td>

                            <td style="padding:0.9rem;text-align:center;">
                                @if(($row->stock_quantity ?? 0) <= 2)
                                    <span style="background:#ffebee;color:#b71c1c;padding:0.3rem 0.7rem;border-radius:20px;font-size:0.8rem;font-weight:700;">⚠ CRITICAL</span>
                                @else
                                    <span style="background:#fff3e0;color:#e65100;padding:0.3rem 0.7rem;border-radius:20px;font-size:0.8rem;font-weight:700;">⚡ LOW</span>
                                @endif
                            </td>

                            <td style="padding:0.9rem;text-align:center;">
                                <div style="display:flex;gap:0.4rem;justify-content:center;">
                                    <a href="{{ route('products.show', $row->id) }}" style="background:#e3f2fd;color:#1976d2;padding:0.4rem 0.6rem;border:1px solid #90caf9;border-radius:2px;text-decoration:none;font-size:0.8rem;" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $row->id) }}" style="background:#fff3e0;color:#e65100;padding:0.4rem 0.6rem;border:1px solid #ffb74d;border-radius:2px;text-decoration:none;font-size:0.8rem;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding:4rem 2rem;text-align:center;">
                <i class="fas fa-check-circle" style="font-size:4rem;color:#4caf50;margin-bottom:1rem;display:block;"></i>
                <h4 style="margin:0 0 0.5rem 0;color:#003d5c;">No Low Stock Items</h4>
                <p style="color:#666;">All products and variations have sufficient stock (≥6 units).</p>
                <a href="{{ route('stock.index') }}" style="background:#003d5c;color:white;padding:0.75rem 1.5rem;border-radius:2px;text-decoration:none;display:inline-flex;align-items:center;margin-top:1rem;">
                    <i class="fas fa-warehouse" style="margin-right:0.5rem;"></i> View All Stock
                </a>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('adminlte_js')
<script>
var ALL_ROWS = @json($all->map(function($r) {
    return [
        'name'            => $r->name,
        'code'            => $r->code ?? '-',
        'stock_quantity'  => $r->stock_quantity ?? 0,
        'unit'            => $r->unit ?? 'pcs',
        'price'           => $r->sale_price ?? $r->price ?? 0,
        'is_variation'    => $r->is_variation,
        'variation_label' => $r->variation_label,
    ];
}));

var sortDir = {};

function filterTable() {
    var search = document.getElementById('stockSearch').value.toLowerCase();
    var filter = document.getElementById('stockFilter').value;
    var rows   = document.querySelectorAll('#stockBody .stock-row');
    var count  = 0;

    rows.forEach(function (row) {
        var name    = row.cells[2].textContent.toLowerCase();
        var varName = row.cells[3].textContent.toLowerCase();
        var code    = row.cells[4].textContent.toLowerCase();
        var stock   = parseInt(row.getAttribute('data-stock')) || 0;
        var type    = row.getAttribute('data-type');

        var matchSearch = !search ||
            name.indexOf(search) !== -1 ||
            varName.indexOf(search) !== -1 ||
            code.indexOf(search) !== -1;

        var matchFilter = filter === 'all'       ? true
                        : filter === 'critical'  ? stock <= 2
                        : filter === 'low'       ? (stock > 2 && stock <= 5)
                        : filter === 'variation' ? type === 'variation'
                        : filter === 'product'   ? type === 'product'
                        : true;

        if (matchSearch && matchFilter) {
            row.style.display = '';
            count++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('rowCount').textContent = count;
}

function sortTable(colIdx) {
    var tbody = document.getElementById('stockBody');
    var rows  = Array.prototype.slice.call(tbody.querySelectorAll('.stock-row'));
    var asc   = !sortDir[colIdx];
    sortDir   = {};
    sortDir[colIdx] = asc;

    rows.sort(function (a, b) {
        var tA = a.cells[colIdx].textContent.trim();
        var tB = b.cells[colIdx].textContent.trim();
        var nA = parseFloat(tA.replace(/[^\d.]/g, ''));
        var nB = parseFloat(tB.replace(/[^\d.]/g, ''));

        if (!isNaN(nA) && !isNaN(nB)) return asc ? nA - nB : nB - nA;
        return asc ? tA.localeCompare(tB) : tB.localeCompare(tA);
    });

    rows.forEach(function (r) { tbody.appendChild(r); });
}

window.printStock = function () {
    var now    = new Date();
    var nowStr = now.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' })
               + ' ' + now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' });

    var rowsHtml = '';
    ALL_ROWS.forEach(function (p, i) {
        var qty    = parseInt(p.stock_quantity) || 0;
        var price  = parseFloat(p.price || 0).toFixed(2);
        var bg     = i % 2 === 0 ? '#fff' : '#fafafa';
        var sc     = qty <= 2 ? '#d32f2f' : '#f57c00';
        var stBg   = qty <= 2 ? '#ffebee' : '#fff3e0';
        var stClr  = qty <= 2 ? '#b71c1c' : '#e65100';
        var stLbl  = qty <= 2 ? 'CRITICAL' : 'LOW STOCK';
        var varLbl = p.is_variation ? p.variation_label : '—';

        rowsHtml +=
            '<tr style="background:' + bg + ';">' +
            '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;">' + (i+1) + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #ccc;">' + (p.name || '') + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #ccc;font-size:11px;">' + varLbl + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #ccc;font-family:monospace;font-size:12px;">' + (p.code || '-') + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;font-weight:700;color:' + sc + ';">' + qty + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;">' + (p.unit || 'pcs') + '</td>' +
            '<td style="padding:7px 10px;border:1px solid #ccc;text-align:right;">৳ ' + price + '</td>' +
            '<td style="padding:5px 10px;border:1px solid #ccc;text-align:center;"><span style="padding:2px 7px;border-radius:3px;font-size:10px;font-weight:700;background:' + stBg + ';color:' + stClr + ';">' + stLbl + '</span></td>' +
            '</tr>';
    });

    var win = window.open('', '_blank', 'width=1200,height=800');
    if (!win) { alert('Please allow popups.'); return; }
    win.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Low Stock Report</title>');
    win.document.write('<style>body{font-family:Arial,sans-serif;font-size:13px;padding:24px;}table{width:100%;border-collapse:collapse;}th{background:#fff3e0;border:1px solid #f57c00;padding:9px 10px;font-size:12px;font-weight:700;}@media print{@page{size:A4 landscape;margin:10mm;}}</style>');
    win.document.write('</head><body>');
    win.document.write('<h2 style="color:#f57c00;margin-bottom:4px;">Low Stock Items Report</h2>');
    win.document.write('<p style="color:#666;font-size:12px;margin-bottom:16px;">Generated: ' + nowStr + ' &nbsp;|&nbsp; Total rows: ' + ALL_ROWS.length + '</p>');
    win.document.write('<table><thead><tr><th>#</th><th>Product</th><th>Variation</th><th>Code</th><th style="text-align:center;">Stock</th><th style="text-align:center;">Unit</th><th style="text-align:right;">Price</th><th style="text-align:center;">Status</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>');
    win.document.write('<script>window.onload=function(){window.print();};<\/script></body></html>');
    win.document.close();
};

window.exportToCSV = function () {
    function esc(v) { return '"' + String(v||'').replace(/"/g,'""') + '"'; }
    var csv = 'SL.,Product,Variation,Code,Stock,Unit,Price,Status\n';
    ALL_ROWS.forEach(function (p, i) {
        var qty    = parseInt(p.stock_quantity) || 0;
        var status = qty <= 2 ? 'Critical' : 'Low Stock';
        csv += (i+1) + ',' + esc(p.name) + ',' + esc(p.is_variation ? p.variation_label : '') + ','
             + esc(p.code) + ',' + qty + ',' + esc(p.unit||'pcs') + ','
             + parseFloat(p.price||0).toFixed(2) + ',' + esc(status) + '\n';
    });
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href = url; a.download = 'low_stock_' + new Date().toISOString().slice(0,10) + '.csv';
    a.style.display = 'none';
    document.body.appendChild(a); a.click();
    document.body.removeChild(a); URL.revokeObjectURL(url);
};
</script>
@endsection