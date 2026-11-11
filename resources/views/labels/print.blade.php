@extends('adminlte::page')

@section('title', 'Print Labels')

@section('content_header')
<div style="display:flex;align-items:center;gap:10px">
    <div style="background:#003d5c;width:4px;height:32px;border-radius:2px"></div>
    <div>
        <h4 style="color:#003d5c;font-weight:700;margin:0">
            <i class="fas fa-barcode mr-2" style="color:#d4af37"></i>Label Print System
        </h4>
        <small style="color:#6c757d;font-size:.78rem">Select products → Generate one label per product</small>
    </div>
</div>
@stop

@section('content')
<link rel="stylesheet" href="{{ asset('css/print-labels.css') }}">

{{-- Product List (full width) --}}
<div class="gov-card">
    <div class="gov-card-header">
        <i class="fas fa-list-ul"></i> Product List
        <span class="hdr-badge" id="total-count">{{ $products->total() }}</span>
    </div>

    <div class="search-bar">
        <i class="fas fa-search" style="color:#6c757d"></i>
        <input type="text" id="search-input" placeholder="Search by name, product code..." value="{{ $search ?? '' }}">
    </div>

    <div style="overflow-x:auto">
        <table class="gov-table">
            <thead>
                <tr>
                    <th style="width:36px">
                        <input type="checkbox" class="lbl-chk" id="sel-all">
                    </th>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price (BDT)</th>
                    <th>Barcode</th>
                </tr>
            </thead>
            <tbody id="product-tbody">
                @foreach($products as $p)
                    @php
                        $displayCode    = $p->code ?? $p->sku ?? '—';
                        $displayBarcode = $p->barcode ?? '';
                        $units          = $p->productUnits ?? collect();
                        $availableStock = $units->where('status', 'available')->count();
                    @endphp
                    <tr data-id="{{ $p->id }}"
                        data-name="{{ $p->name }}"
                        data-sku="{{ $displayCode }}"
                        data-price="{{ number_format((float)$p->price,2) }}"
                        data-barcode="{{ $displayBarcode }}"
                        data-stock="{{ max(1, $availableStock) }}">
                        <td><input type="checkbox" class="lbl-chk row-chk"></td>
                        <td style="color:#6c757d;font-size:.8rem">{{ $p->id }}</td>
                        <td style="font-weight:600;color:#1a2b3c">
                            {{ $p->name }}
                        </td>
                        <td style="font-weight:600;color:#003d5c">{{ number_format((float)$p->price,2) }}</td>
                        <td><span class="sku-pill">{{ $displayBarcode }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="tbl-loading" style="display:none;text-align:center;padding:2rem;color:#6c757d;font-size:.86rem">
            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading…
        </div>
    </div>

    <div class="gov-pagination" id="pgn-wrap"></div>
</div>

{{-- Generate button --}}
<button class="btn-gen" id="btn-gen" disabled>
    <i class="fas fa-barcode mr-1"></i> Generate Labels
    <span class="hdr-badge" id="queue-count" style="margin-left:4px">0</span>
</button>

{{-- Modal --}}
<div id="modal-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.4);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:1.5rem;width:420px;max-width:95vw;max-height:90vh;overflow-y:auto;position:relative">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem">
            <h5 style="margin:0;font-weight:700;color:#003d5c"><i class="fas fa-print mr-2" style="color:#d4af37"></i>Print Queue &amp; Settings</h5>
            <button id="modal-close" style="border:none;background:none;font-size:1.3rem;line-height:1;cursor:pointer;color:#6c757d">&times;</button>
        </div>

        {{-- Queue --}}
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:#6c757d;letter-spacing:.05em;margin-bottom:.5rem">
            Queue
        </div>
        <div class="queue-scroll" id="queue-list">
            <div class="queue-empty-msg" id="queue-empty" style="text-align:center;padding:1.5rem;color:#aab5be;font-size:.86rem">
                <i class="fas fa-inbox"></i> No products selected yet.
            </div>
        </div>

        <hr style="border:none;border-top:1px solid #e9ecef;margin:1rem 0">

        {{-- Settings --}}
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:#6c757d;letter-spacing:.05em;margin-bottom:.75rem">
            Label Settings
        </div>
        <div class="settings-body">
            <div class="setting-row">
                <label>Columns</label>
                <select id="set-cols">
                    <option value="1" selected>1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>
            <div class="setting-row">
                <label>Label size</label>
                <select id="set-size">
                    <option value="small">Small</option>
                    <option value="medium" selected>Medium</option>
                    <option value="large">Large</option>
                </select>
            </div>
            <div class="setting-row">
                <label>Font size</label>
                <select id="set-font">
                    <option value="small">Small</option>
                    <option value="normal" selected>Normal</option>
                    <option value="large">Large</option>
                </select>
            </div>
            <div class="setting-row">
                <label>Show name</label>
                <input type="checkbox" id="set-name" class="lbl-chk" checked>
            </div>
            <div class="setting-row">
                <label>Show price</label>
                <input type="checkbox" id="set-price" class="lbl-chk" checked>
            </div>
            <div class="setting-row">
                <label>Show Product Code</label>
                <input type="checkbox" id="set-sku" class="lbl-chk" checked>
            </div>
        </div>

        <button class="btn-gen" id="btn-submit" style="width:100%;margin-top:1rem;justify-content:center">
            <i class="fas fa-barcode mr-1"></i> Generate &amp; Print
        </button>
    </div>
</div>

{{-- Hidden POST form --}}
<form id="gen-form" method="POST" action="{{ route('labels.generate') }}" target="_blank" style="display:none">
    @csrf
    <div id="form-fields"></div>
</form>

@stop

@push('js')
<script>
var SEARCH_URL = "{{ route('labels.search') }}";
var SEL = {};
var curPage = 1;
var lastPage = {{ $products->lastPage() }};
var totalProds = {{ $products->total() }};
var searchTerm = @json($search ?? '');
var searchTimer;

var tbody     = document.getElementById('product-tbody');
var qList     = document.getElementById('queue-list');
var qEmpty    = document.getElementById('queue-empty');
var qCount    = document.getElementById('queue-count');
var totalEl   = document.getElementById('total-count');
var btnGen    = document.getElementById('btn-gen');
var searchInp = document.getElementById('search-input');
var pgnWrap   = document.getElementById('pgn-wrap');
var loading   = document.getElementById('tbl-loading');
var selAll    = document.getElementById('sel-all');
var overlay   = document.getElementById('modal-overlay');

/* -- Modal open/close -- */
btnGen.addEventListener('click', function() {
    renderQueue();
    overlay.style.display = 'flex';
});
document.getElementById('modal-close').addEventListener('click', function() {
    overlay.style.display = 'none';
});
overlay.addEventListener('click', function(e) {
    if (e.target === overlay) overlay.style.display = 'none';
});

/* -- AJAX load page -- */
function loadPage(page) {
    loading.style.display = 'block';
    tbody.style.opacity = '.35';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', SEARCH_URL + '?q=' + encodeURIComponent(searchTerm) + '&page=' + page, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            loading.style.display = 'none';
            tbody.style.opacity = '1';
            if (xhr.status === 200) {
                var data   = JSON.parse(xhr.responseText);
                curPage    = data.current_page;
                lastPage   = data.last_page;
                totalProds = data.total;
                totalEl.textContent = data.total;
                renderRows(data.data);
                renderPgn();
            }
        }
    };
    xhr.send();
}

/* -- Render rows -- */
function renderRows(rows) {
    tbody.innerHTML = '';
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:1.8rem;color:#aab5be">No products found.</td></tr>';
        selAll.checked = false;
        return;
    }
    for (var i = 0; i < rows.length; i++) {
        var p   = rows[i];
        var chk = SEL[p.id] ? 'checked' : '';
        var tr  = document.createElement('tr');
        tr.className       = SEL[p.id] ? 'row-sel' : '';
        tr.dataset.id      = p.id;
        tr.dataset.name    = p.name;
        tr.dataset.sku     = p.sku;
        tr.dataset.price   = p.price;
        tr.dataset.barcode = p.barcode;
        tr.dataset.stock   = p.stock || 1;
        var badge = (p.stock > 1) ? ' <span class="badge badge-info" style="font-size:11px;margin-left:4px;">' + p.stock + ' pcs</span>' : '';
        tr.innerHTML =
            '<td><input type="checkbox" class="lbl-chk row-chk" ' + chk + '></td>' +
            '<td style="color:#6c757d;font-size:.8rem">' + p.id + '</td>' +
            '<td style="font-weight:600;color:#1a2b3c">' + esc(p.name) + badge + unitTable(p) + '</td>' +
            '<td style="font-weight:600;color:#003d5c">' + p.price + '</td>' +
            '<td><span class="sku-pill">' + esc(p.barcode) + '</span></td>';
        tbody.appendChild(tr);
    }
    bindRowChk();
    syncSelAll();
}

/* -- Pagination -- */
function renderPgn() {
    var h = '<button class="pgn-btn" id="pgn-prev" ' + (curPage <= 1 ? 'disabled' : '') + '>&laquo; Prev</button>';
    var range = pageRange(curPage, lastPage);
    for (var i = 0; i < range.length; i++) {
        if (range[i] === '…') { h += '<span style="padding:0 3px;color:#aaa">…</span>'; continue; }
        h += '<button class="pgn-btn ' + (range[i] === curPage ? 'active' : '') + '" data-p="' + range[i] + '">' + range[i] + '</button>';
    }
    h += '<button class="pgn-btn" id="pgn-next" ' + (curPage >= lastPage ? 'disabled' : '') + '>Next &raquo;</button>';
    h += '<span class="pgn-info">Page ' + curPage + '/' + lastPage + ' &nbsp;&nbsp; ' + totalProds + ' products</span>';
    pgnWrap.innerHTML = h;
    document.getElementById('pgn-prev').addEventListener('click', function() { loadPage(curPage - 1); });
    document.getElementById('pgn-next').addEventListener('click', function() { loadPage(curPage + 1); });
    var btns = pgnWrap.querySelectorAll('[data-p]');
    for (var j = 0; j < btns.length; j++) {
        btns[j].addEventListener('click', function() { loadPage(+this.dataset.p); });
    }
}

function pageRange(cur, last) {
    if (last <= 7) { var r = []; for (var i = 1; i <= last; i++) r.push(i); return r; }
    if (cur <= 4)      return [1,2,3,4,5,'…',last];
    if (cur >= last-3) return [1,'…',last-4,last-3,last-2,last-1,last];
    return [1,'…',cur-1,cur,cur+1,'…',last];
}

/* -- Row checkboxes -- */
function bindRowChk() {
    var chks = tbody.querySelectorAll('.row-chk');
    for (var i = 0; i < chks.length; i++) {
        chks[i].addEventListener('change', function() {
            var row = this.closest('tr');
            var id  = +row.dataset.id;
            if (this.checked) {
                var stockQty = parseInt(row.dataset.stock, 10) || 1;
                SEL[id] = { id: id, name: row.dataset.name, sku: row.dataset.sku, price: row.dataset.price, stock: stockQty, qty: SEL[id] ? SEL[id].qty : stockQty };
                row.classList.add('row-sel');
            } else {
                delete SEL[id];
                row.classList.remove('row-sel');
            }
            syncSelAll();
            updateCount();
        });
    }
}

selAll.addEventListener('change', function() {
    var chks = tbody.querySelectorAll('.row-chk');
    for (var i = 0; i < chks.length; i++) {
        var row = chks[i].closest('tr');
        var id  = +row.dataset.id;
        chks[i].checked = this.checked;
        if (this.checked) {
            var stockQty = parseInt(row.dataset.stock, 10) || 1;
            SEL[id] = { id: id, name: row.dataset.name, sku: row.dataset.sku, price: row.dataset.price, stock: stockQty, qty: SEL[id] ? SEL[id].qty : stockQty };
            row.classList.add('row-sel');
        } else {
            delete SEL[id];
            row.classList.remove('row-sel');
        }
    }
    updateCount();
});

function syncSelAll() {
    var chks = tbody.querySelectorAll('.row-chk');
    var all  = chks.length > 0;
    for (var i = 0; i < chks.length; i++) { if (!chks[i].checked) { all = false; break; } }
    selAll.checked = all;
}

function updateCount() {
    var n = Object.keys(SEL).length;
    qCount.textContent = n;
    btnGen.disabled = !n;
}

/* -- Queue (rendered inside modal) -- */
function renderQueue() {
    var items = Object.values(SEL);
    qList.innerHTML = '';
    if (!items.length) { qList.appendChild(qEmpty); return; }
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var div  = document.createElement('div');
        div.className  = 'queue-item';
        div.dataset.id = item.id;
        div.innerHTML  =
            '<div class="queue-item-info">' +
                '<div class="queue-item-name">' + esc(item.name) + '</div>' +
                '<div class="queue-item-sku">'  + esc(item.sku)  + '</div>' +
            '</div>' +
            '<input type="number" class="queue-qty" value="' + item.qty + '" min="1" max="' + item.stock + '" data-id="' + item.id + '">' +
            '<button class="queue-del" data-id="' + item.id + '">&times;</button>';
        qList.appendChild(div);
    }
    var qtys = qList.querySelectorAll('.queue-qty');
    for (var j = 0; j < qtys.length; j++) {
        qtys[j].addEventListener('change', function() {
            var id  = +this.dataset.id;
            var max = Math.max(1, SEL[id].stock || 1);
            SEL[id].qty = Math.max(1, Math.min(max, +this.value || 1));
            this.value = SEL[id].qty;
        });
    }
    var dels = qList.querySelectorAll('.queue-del');
    for (var k = 0; k < dels.length; k++) {
        dels[k].addEventListener('click', function() {
            var id  = +this.dataset.id;
            delete SEL[id];
            var row = tbody.querySelector('tr[data-id="' + id + '"]');
            if (row) { row.querySelector('.row-chk').checked = false; row.classList.remove('row-sel'); }
            syncSelAll();
            updateCount();
            renderQueue();
            if (!Object.keys(SEL).length) overlay.style.display = 'none';
        });
    }
}

/* -- Submit -- */
document.getElementById('btn-submit').addEventListener('click', function() {
    var items  = Object.values(SEL);
    var fields = document.getElementById('form-fields');
    fields.innerHTML = '';
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var qInp = qList.querySelector('.queue-qty[data-id="' + item.id + '"]');
        var qty  = qInp ? Math.max(1, +qInp.value || 1) : item.qty;
        fields.innerHTML += '<input type="hidden" name="products[' + i + '][id]"       value="' + item.id + '">';
        fields.innerHTML += '<input type="hidden" name="products[' + i + '][quantity]" value="' + qty + '">';
    }
    fields.innerHTML += '<input type="hidden" name="settings[columns]"    value="' + document.getElementById('set-cols').value + '">';
    fields.innerHTML += '<input type="hidden" name="settings[label_size]" value="' + document.getElementById('set-size').value + '">';
    fields.innerHTML += '<input type="hidden" name="settings[font_size]"  value="' + document.getElementById('set-font').value + '">';
    fields.innerHTML += '<input type="hidden" name="settings[show_name]"  value="' + (document.getElementById('set-name').checked  ? '1' : '0') + '">';
    fields.innerHTML += '<input type="hidden" name="settings[show_price]" value="' + (document.getElementById('set-price').checked ? '1' : '0') + '">';
    fields.innerHTML += '<input type="hidden" name="settings[show_sku]"   value="' + (document.getElementById('set-sku').checked   ? '1' : '0') + '">';
    document.getElementById('gen-form').submit();
});

/* -- Search -- */
searchInp.addEventListener('input', function() {
    clearTimeout(searchTimer);
    var val = this.value.trim();
    searchTimer = setTimeout(function() { searchTerm = val; loadPage(1); }, 350);
});

/* -- Init -- */
bindRowChk();
renderPgn();

function esc(s) {
    return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function unitTable(product) {
    return '';
}
</script>
@endpush
