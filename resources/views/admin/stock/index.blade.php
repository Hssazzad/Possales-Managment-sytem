@extends('adminlte::page')

@section('title', 'Stock List | PosSales')

@section('adminlte_css')
<link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
<style>
/* ============ Stock List (stk-) — page-scoped, no shared CSS touched ============ */
.stk-wrap { font-size: 14px; color: #0f172a; }
.stk-card { background: #fff; border: 1px solid #eef2f7; border-radius: 10px; box-shadow: 0 1px 3px rgba(15,23,42,.06); padding: 20px; }
.stk-toolbar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 14px; }
.stk-search { position: relative; flex: 1 1 280px; max-width: 460px; }
.stk-search > i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; }
.stk-search input { width: 100%; height: 40px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0 12px 0 36px; font-size: 14px; background: #fff; color: #0f172a; }
.stk-search input::placeholder { color: #9ca3af; }
.stk-search input:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.12); outline: none; }
.stk-actions { display: flex; gap: 10px; margin-left: auto; flex-wrap: wrap; }
.stk-btn { display: inline-flex; align-items: center; gap: 7px; font-size: 14px; font-weight: 500; border-radius: 8px; padding: 9px 18px; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; text-decoration: none; white-space: nowrap; }
.stk-btn:hover { background: #f9fafb; color: #111827; text-decoration: none; }
.stk-btn-green { background: #10b981; border-color: #10b981; color: #fff; }
.stk-btn-green:hover { background: #0da271; color: #fff; }
.stk-btn-gray { background: #6b7280; border-color: #6b7280; color: #fff; }
.stk-btn-gray:hover { background: #5b6470; color: #fff; }
.stk-scroll { overflow-x: auto; }
table.stk-table { width: 100%; border-collapse: collapse; background: #fff; min-width: 960px; }
.stk-table thead th { color: #6b7280; font-weight: 500; font-size: 13px; text-transform: uppercase; padding: 12px 15px; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap; background: #fff; }
.stk-table thead th.c, .stk-table td.c { text-align: center; }
.stk-table thead th.r, .stk-table td.r { text-align: right; }
.stk-table tbody td { padding: 14px 15px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; color: #374151; font-size: 14px; }
.stk-table tbody tr.stk-prow:hover > td { background: #f9fafb; }
.stk-table tbody tr.stk-prow.stk-open > td { background: #f8fafc; }
.stk-idx { display: inline-flex; align-items: center; gap: 6px; color: #6b7280; font-weight: 700; }
.stk-toggle { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; color: #6b7280; font-size: 11px; cursor: pointer; padding: 0; }
.stk-toggle:hover { background: #f3f4f6; color: #111827; }
.stk-toggle .chev { transition: transform .15s ease; }
.stk-toggle[aria-expanded="true"] .chev { transform: rotate(180deg); }
.stk-prod { display: flex; align-items: center; gap: 12px; min-width: 200px; }
.stk-prod img { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; background: #f9fafb; flex: none; }
.stk-prod-fb { width: 40px; height: 40px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; display: inline-flex; align-items: center; justify-content: center; color: #d1d5db; flex: none; }
.stk-name { font-size: 15px; font-weight: 700; color: #111827; line-height: 1.25; }
.stk-sub { font-size: 12.5px; color: #64748b; font-weight: 600; margin-top: 2px; }
.stk-code { display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12.5px; font-weight: 700; color: #4338ca; background: #eef2ff; border-radius: 6px; padding: 4px 10px; white-space: nowrap; }
.stk-var { display: inline-block; font-size: 12.5px; font-weight: 600; color: #fff; background: #14b8a6; border-radius: 6px; padding: 4px 10px; white-space: nowrap; }
.stk-var-none { color: #cbd5e1; }
.stk-barcode { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; color: #111827; white-space: nowrap; }
.stk-qty { display: inline-block; min-width: 30px; text-align: center; font-size: 13px; font-weight: 600; border-radius: 999px; padding: 4px 12px; }
.stk-qty-has { background: #fef08a; color: #854d0e; }
.stk-qty-none { background: #fee2e2; color: #991b1b; }
.stk-price { font-weight: 700; color: #111827; white-space: nowrap; }
.stk-act-toggle { background: none; border: none; color: #6b7280; font-size: 16px; cursor: pointer; padding: 4px 8px; border-radius: 6px; letter-spacing: 1px; }
.stk-act-toggle:hover { background: #f3f4f6; color: #111827; }
/* Detail row */
tr.stk-drow > td { background: #f8fafc; padding: 0 15px 16px 52px; border-bottom: 1px solid #e5e7eb; }
.stk-detail { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-top: 2px; }
.stk-detail-meta { display: flex; flex-wrap: wrap; gap: 6px 22px; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #475569; }
.stk-detail-meta strong { color: #0f172a; font-weight: 700; }
.stk-detail-meta .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
.stk-units-scroll { max-height: 260px; overflow-y: auto; }
table.stk-mini { width: 100%; border-collapse: collapse; background: #fff; }
.stk-mini thead th { position: sticky; top: 0; z-index: 1; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; text-align: left; padding: 9px 14px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
.stk-mini td { font-size: 13.5px; padding: 8px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
.stk-mini tr:last-child td { border-bottom: none; }
.stk-mini .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 600; }
.stk-st { display: inline-block; font-size: 12.5px; font-weight: 600; border-radius: 999px; padding: 2px 11px; border: 1px solid transparent; white-space: nowrap; }
.stk-st-available { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.stk-st-sold { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.stk-st-returned { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.stk-st-damaged { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
.stk-empty-units { padding: 14px; font-size: 13px; color: #64748b; }
.stk-foot { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
.stk-showing { color: #6b7280; font-size: 14px; margin-right: auto; }
.stk-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 700; color: #111827; border: 1px solid #e5e7eb; border-radius: 8px; padding: 7px 14px; background: #fff; }
.stk-pill .b { color: #2563eb; }
.stk-pill .g { color: #16a34a; }
.stk-none { text-align: center; padding: 48px 20px; color: #6b7280; }
.stk-none .t { font-size: 15px; font-weight: 700; color: #111827; margin: 10px 0 4px; }
@media (max-width: 900px) {
    .stk-card { padding: 14px; }
    .stk-actions { margin-left: 0; }
}
@media print {
    .stk-no-print { display: none !important; }
    .stk-card { box-shadow: none; border: none; padding: 0; }
    .stk-scroll { overflow: visible; }
    table.stk-table { min-width: 0; }
    tr.stk-drow > td { background: #fff !important; }
    .stk-units-scroll { max-height: none; overflow: visible; }
}
</style>
@stop

@section('content')
<div class="pt-2">
<div class="stk-wrap">
<div class="stk-card">

    {{-- Toolbar --}}
    <div class="stk-toolbar stk-no-print">
        <form method="GET" action="{{ route('stock.index') }}" class="stk-search" id="stk-search-form" role="search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" id="stk-search-input" value="{{ $search ?? '' }}" placeholder="Search product, code, variation, scan barcode…" autocomplete="off" aria-label="Search stock">
        </form>
        <div class="stk-actions">
            <button type="button" class="stk-btn" onclick="window.printStock()">
                <i class="fas fa-print"></i> Print
            </button>
            <button type="button" class="stk-btn stk-btn-green" onclick="window.exportToCSV()">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
            <a href="{{ route('products.index') }}" class="stk-btn stk-btn-gray">
                <i class="fas fa-arrow-left"></i> Products
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="stk-scroll">
        <table class="stk-table" id="stk-table">
            <thead>
                <tr>
                    <th style="width:64px;">#</th>
                    <th>Product Name</th>
                    <th>Product Code</th>
                    <th>Variation</th>
                    <th>Scan Barcode</th>
                    <th class="c">Stock Qty</th>
                    <th class="c">Unit</th>
                    <th class="r">Sale Price</th>
                    <th class="c stk-no-print"><i class="fas fa-cog"></i></th>
                </tr>
            </thead>
            <tbody id="stk-tbody">
                @forelse($rows as $i => $row)
                @php
                    $detailId = 'stk-detail-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $row['key']);
                    $hasUnits = collect($row['units'] ?? [])->count() > 0;
                @endphp
                <tr class="stk-prow" data-key="{{ $row['key'] }}">
                    <td>
                        <span class="stk-idx">
                            <button type="button" class="stk-toggle stk-no-print" data-target="{{ $detailId }}" aria-expanded="false" aria-controls="{{ $detailId }}" title="Show unit details" aria-label="Expand units for {{ $row['name'] }}">
                                <i class="fas fa-chevron-down chev"></i>
                            </button>
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td>
                        <div class="stk-prod">
                            <img src="{{ $row['image_url'] }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                            <span class="stk-prod-fb" style="display:none;"><i class="fas fa-box"></i></span>
                            <div>
                                <div class="stk-name">{{ $row['name'] }}</div>
                                <div class="stk-sub">Variation stock row</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="stk-code">{{ $row['product_code'] }}</span></td>
                    <td>
                        @if(!empty($row['variation_label']))
                            <span class="stk-var">{{ $row['variation_label'] }}</span>
                        @else
                            <span class="stk-var-none">&mdash;</span>
                        @endif
                    </td>
                    <td><span class="stk-barcode">{{ $row['scan_barcode'] }}</span></td>
                    <td class="c"><span class="stk-qty {{ (int) $row['stock_quantity'] > 0 ? 'stk-qty-has' : 'stk-qty-none' }}">{{ $row['stock_quantity'] }}</span></td>
                    <td class="c">{{ $row['unit'] }}</td>
                    <td class="r"><span class="stk-price">৳{{ $row['price_fmt'] }}</span></td>
                    <td class="c stk-no-print">
                        <div class="dropdown" style="display:inline-block;">
                            <button class="stk-act-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                <a class="dropdown-item" href="{{ route('products.show', $row['product_id']) }}">
                                    <i class="fas fa-eye text-primary mr-2"></i> View
                                </a>
                                <a class="dropdown-item" href="{{ route('products.edit', $row['product_id']) }}">
                                    <i class="fas fa-edit text-warning mr-2"></i> Edit
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="stk-drow" id="{{ $detailId }}" hidden>
                    <td colspan="9">
                        <div class="stk-detail">
                            <div class="stk-detail-meta">
                                <span>Product: <strong>{{ $row['name'] }}</strong></span>
                                <span>Product Code: <strong class="mono">{{ $row['product_code'] }}</strong></span>
                                <span>Variation: <strong>{{ $row['variation_label'] ?? '—' }}</strong></span>
                                <span>Barcode: <strong class="mono">{{ $row['scan_barcode'] }}</strong></span>
                            </div>
                            @if($hasUnits)
                            <div class="stk-units-scroll">
                                <table class="stk-mini">
                                    <thead>
                                        <tr><th scope="col">Unit Code</th><th scope="col">Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row['units'] as $unit)
                                        @php
                                            $st = strtolower((string) ($unit['status'] ?? 'available'));
                                            $stClass = $st === 'available' ? 'stk-st-available' : ($st === 'sold' ? 'stk-st-sold' : ($st === 'returned' ? 'stk-st-returned' : ($st === 'damaged' ? 'stk-st-damaged' : 'stk-st-sold')));
                                        @endphp
                                        <tr>
                                            <td class="mono">{{ $unit['unit_code'] }}</td>
                                            <td><span class="stk-st {{ $stClass }}">{{ ucfirst($unit['status']) }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="stk-empty-units">No individual unit records found for this variation.</div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="stk-none">
                            <i class="fas fa-boxes fa-2x" style="color:#cbd5e1;"></i>
                            <div class="t">No stock rows found</div>
                            <p style="margin:0;">Try a different search, or add products to get started.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="stk-foot">
        <span class="stk-showing" id="stk-showing">Showing all {{ $rows->count() }} stock rows</span>
        <span class="stk-pill">Total Units: <span class="b">{{ $totalUnits }}</span></span>
        <span class="stk-pill">Avg Stock: <span class="g">{{ $avgStock }}</span></span>
    </div>

</div>
</div>
</div>
@stop

@section('adminlte_js')
<script>
(function () {
    var tbody = document.getElementById('stk-tbody');
    var searchInput = document.getElementById('stk-search-input');
    var searchForm = document.getElementById('stk-search-form');
    var showing = document.getElementById('stk-showing');
    var totalRows = {{ $rows->count() }};
    var searchTimer = null;

    /* Expand / collapse unit details (vanilla JS, no framework) */
    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.stk-toggle') : null;
        if (!btn) return;
        var panel = document.getElementById(btn.getAttribute('data-target'));
        if (!panel) return;
        var prow = btn.closest('tr.stk-prow');
        var isHidden = panel.hasAttribute('hidden');
        if (isHidden) {
            panel.removeAttribute('hidden');
            btn.setAttribute('aria-expanded', 'true');
            btn.setAttribute('title', 'Hide unit details');
            if (prow) prow.classList.add('stk-open');
        } else {
            panel.setAttribute('hidden', '');
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('title', 'Show unit details');
            if (prow) prow.classList.remove('stk-open');
        }
    });

    /* Live client-side filter for instant feedback; server-side ?search= is the source of truth */
    function liveFilter() {
        var q = (searchInput.value || '').toLowerCase().trim();
        var visible = 0;
        var prows = tbody.querySelectorAll('tr.stk-prow');
        prows.forEach(function (prow) {
            var text = prow.textContent.toLowerCase();
            var detail = prow.nextElementSibling;
            var isDetail = detail && detail.classList.contains('stk-drow');
            var match = !q || text.indexOf(q) !== -1;
            // Also match unit codes inside the detail panel
            if (!match && isDetail) {
                match = detail.textContent.toLowerCase().indexOf(q) !== -1;
            }
            prow.style.display = match ? '' : 'none';
            if (isDetail && !match) {
                detail.setAttribute('hidden', '');
                var btn = prow.querySelector('.stk-toggle');
                if (btn) { btn.setAttribute('aria-expanded', 'false'); }
                prow.classList.remove('stk-open');
                detail.style.display = 'none';
            } else if (isDetail) {
                detail.style.display = '';
                if (detail.hasAttribute('hidden')) { /* keep collapsed */ }
            }
            if (match) visible++;
        });
        if (showing) {
            showing.textContent = q
                ? ('Showing ' + visible + ' of ' + totalRows + ' stock rows')
                : ('Showing all ' + totalRows + ' stock rows');
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            liveFilter();
            // Debounced server search so refresh/bookmark keeps working
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if (searchForm) searchForm.submit();
            }, 1200);
        });
        // Instant filter on load if ?search= present
        if (searchInput.value) liveFilter();
    }

    /* Export visible main rows to CSV (dynamic, from real data) */
    window.exportToCSV = function () {
        try {
            var rows = @json($exportRows);
            var prows = tbody.querySelectorAll('tr.stk-prow');
            var visibleIdx = [];
            prows.forEach(function (prow, idx) {
                if (prow.style.display !== 'none') visibleIdx.push(idx);
            });
            var data = visibleIdx.length ? visibleIdx.map(function (i) { return rows[i]; }) : rows;
            if (!data.length) { alert('No data available to export'); return; }
            function esc(v) { return '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"'; }
            var csv = '﻿SL.,Product Name,Product Code,Variation,Scan Barcode,Stock Qty,Unit,Sale Price\n';
            data.forEach(function (r, i) {
                csv += (i + 1) + ',' + esc(r.name) + ',' + esc(r.product_code) + ',' + esc(r.variation || '') + ','
                    + esc(r.scan_barcode) + ',' + (r.stock_quantity || 0) + ',' + esc(r.unit || '') + ','
                    + parseFloat(r.price || 0).toFixed(2) + '\n';
            });
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'stock_list_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('CSV export error:', error);
            alert('Error exporting CSV: ' + error.message);
        }
    };

    window.printStock = function () {
        try { window.print(); }
        catch (error) { alert('Error printing: ' + error.message); }
    };
})();
</script>
@stop
