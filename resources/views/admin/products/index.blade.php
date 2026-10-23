@extends('adminlte::page')

@section('title', 'Product List | ' . config('app.name'))

@section('adminlte_css')
<style>
    /* ============ Products index (px-) — page-scoped, no shared CSS touched ============ */
    .px-wrap { font-size: 14px; color: #0f172a; }
    .px-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 4px 0 12px; }
    .px-crumb { font-size: 12.5px; color: #64748b; margin-bottom: 2px; }
    .px-crumb a { color: #2563eb; text-decoration: none; }
    .px-crumb a:hover { text-decoration: underline; }
    .px-title { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; display: inline-flex; align-items: center; gap: 8px; }
    .px-total-pill { font-size: 12px; font-weight: 600; color: #334155; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 2px 10px; white-space: nowrap; }
    .px-btn { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 8px 14px; border: 1px solid transparent; cursor: pointer; text-decoration: none; white-space: nowrap; }
    .px-btn-primary { background: #0f172a; color: #fff; }
    .px-btn-primary:hover { background: #1e293b; color: #fff; text-decoration: none; }
    .px-btn-ghost { background: #fff; color: #334155; border-color: #cbd5e1; }
    .px-btn-ghost:hover { background: #f8fafc; color: #0f172a; text-decoration: none; }
    .px-btn-sm { padding: 6px 10px; font-size: 12.5px; }
    .px-btn:focus-visible, a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible { outline: 2px solid #2563eb; outline-offset: 1px; }

    .px-toolbar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 10px; }
    .px-search { position: relative; flex: 1 1 280px; max-width: 560px; }
    .px-search > i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 13px; }
    .px-search input { width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px 0 34px; font-size: 14px; background: #fff; color: #0f172a; }
    .px-search input::placeholder { color: #94a3b8; }
    .px-toolbar select.px-sort { height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; padding: 0 8px; background: #fff; color: #0f172a; max-width: 200px; }

    .px-panel { display: none; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
    .px-panel.open { display: block; }
    .px-panel-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
    .px-field label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; }
    .px-field select { width: 100%; height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; padding: 0 8px; background: #fff; color: #0f172a; }
    .px-panel-foot { display: flex; align-items: center; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
    .px-hint { font-size: 12px; color: #64748b; }
    .px-match { font-size: 12.5px; font-weight: 600; color: #0f172a; }

    .px-count { font-size: 12.5px; color: #475569; margin: 2px 0 8px; }
    .px-count strong { color: #0f172a; }

    .px-table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .px-scroll { overflow-x: auto; }
    table.px-table { width: 100%; border-collapse: collapse; background: #fff; }
    .px-table thead th { position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #475569; font-size: 12px; font-weight: 700; text-align: left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .px-table thead th.num, .px-table td.num { text-align: center; width: 44px; }
    .px-table thead th.r, .px-table td.r { text-align: right; }
    .px-table thead th.c, .px-table td.c { text-align: center; }
    .px-table thead th a { color: #475569; text-decoration: none; }
    .px-table thead th a:hover { color: #0f172a; }
    .px-table thead th a .arr { color: #2563eb; font-size: 11px; }
    .px-table tbody td { padding: 9px 12px; border-bottom: 1px solid #eef2f7; vertical-align: middle; font-size: 14px; }
    .px-table tbody tr.px-prow:hover > td { background: #f8fafc; }
    .px-table tbody tr.px-prow.selected > td { background: #eff6ff; }
    .px-table tbody tr.px-prow:last-child > td { border-bottom: none; }

    .px-prod { display: flex; align-items: center; gap: 10px; min-width: 220px; }
    .px-prod img { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; background: #f8fafc; flex: none; }
    .px-name { font-size: 15px; font-weight: 650; color: #0f172a; line-height: 1.25; }
    .px-name a { color: inherit; text-decoration: none; }
    .px-name a:hover { color: #2563eb; text-decoration: underline; }
    .px-meta { font-size: 12.5px; color: #64748b; margin-top: 1px; }
    .px-sku { display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12.5px; font-weight: 600; color: #0f172a; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 1px 7px; margin-top: 3px; }
    .px-price { font-size: 14px; font-weight: 700; color: #0f172a; white-space: nowrap; }
    .px-cost { font-size: 12px; color: #64748b; margin-top: 1px; white-space: nowrap; }
    .px-stock-n { font-size: 14px; font-weight: 700; color: #0f172a; white-space: nowrap; }
    .px-stock-n small { font-weight: 500; color: #64748b; }
    .px-toggle { background: none; border: none; padding: 0; margin-top: 2px; font-size: 12.5px; font-weight: 600; color: #2563eb; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
    .px-toggle:hover { text-decoration: underline; }
    .px-toggle .chev { transition: transform .15s ease; font-size: 11px; }
    .px-toggle[aria-expanded="true"] .chev { transform: rotate(180deg); }
    .px-barcode { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; letter-spacing: .06em; color: #0f172a; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 3px 8px; white-space: nowrap; }
    .px-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; border-radius: 999px; padding: 3px 10px; white-space: nowrap; border: 1px solid transparent; }
    .px-badge .dot { width: 7px; height: 7px; border-radius: 50%; flex: none; }
    .px-ok { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .px-ok .dot { background: #10b981; }
    .px-warn { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .px-warn .dot { background: #f59e0b; }
    .px-bad { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .px-bad .dot { background: #ef4444; }
    .px-act { display: flex; gap: 4px; justify-content: center; align-items: center; }
    .px-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #334155; font-size: 12.5px; cursor: pointer; text-decoration: none; }
    .px-icon-btn:hover { background: #f1f5f9; color: #0f172a; text-decoration: none; }
    .px-icon-btn.danger { color: #b91c1c; }
    .px-icon-btn.danger:hover { background: #fef2f2; color: #b91c1c; }
    .px-check { width: 16px; height: 16px; accent-color: #0f172a; cursor: pointer; }

    tr.px-drow > td { background: #f8fafc; padding: 0 12px 12px 52px; border-bottom: 1px solid #e2e8f0; }
    table.px-mini { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
    .px-mini th { font-size: 11.5px; font-weight: 700; color: #64748b; text-align: left; padding: 7px 10px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0; }
    .px-mini td { font-size: 13px; padding: 7px 10px; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
    .px-mini tr:last-child td { border-bottom: none; }
    .px-mini .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12.5px; }

    .px-empty { text-align: center; padding: 56px 20px; color: #64748b; }
    .px-empty .t { font-size: 15px; font-weight: 700; color: #0f172a; margin: 10px 0 4px; }
    .px-empty p { font-size: 13px; margin: 0; }

    .px-bulk { position: fixed; left: 50%; bottom: 18px; transform: translateX(-50%); z-index: 1050; display: none; align-items: center; gap: 10px; background: #0f172a; color: #fff; border-radius: 10px; padding: 10px 14px; box-shadow: 0 10px 30px rgba(2,6,23,.35); max-width: calc(100vw - 24px); flex-wrap: wrap; }
    .px-bulk.show { display: inline-flex; }
    .px-bulk .n { font-size: 13px; font-weight: 700; }
    .px-bulk .px-btn-primary { background: #fff; color: #0f172a; }
    .px-bulk .px-btn-link { background: none; border: none; color: #cbd5e1; font-size: 13px; font-weight: 600; cursor: pointer; }
    .px-bulk .px-btn-link:hover { color: #fff; }

    .px-pager { margin-top: 12px; }
    .px-alert { border-radius: 6px; font-size: 13px; font-weight: 600; padding: 10px 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .px-alert.ok { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .px-alert button { margin-left: auto; background: none; border: none; font-size: 18px; cursor: pointer; color: inherit; line-height: 1; padding: 0; }

    @media (max-width: 900px) {
        .px-table thead { display: none; }
        table.px-table, .px-table tbody, .px-table tr, .px-table td { display: block; width: 100%; }
        .px-table tbody tr.px-prow { border: 1px solid #e2e8f0; border-radius: 8px; margin: 0 10px 10px; }
        .px-table tbody tr.px-prow > td { border-bottom: 1px dashed #eef2f7; padding: 8px 12px; }
        .px-table tbody tr.px-prow > td:last-child { border-bottom: none; }
        .px-table td[data-label]::before { content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
        .px-table td.num, .px-table td.c, .px-table td.r, .px-table thead th.num { text-align: left; width: auto; }
        tr.px-drow > td { padding: 0 10px 10px; }
        .px-act { justify-content: flex-start; }
        .px-head { margin-top: 2px; }
    }
</style>
@stop

@section('content')
<div class="px-wrap">

    @if(session('success'))
    <div class="px-alert ok">
        <i class="fas fa-check-circle"></i>
        <span style="flex:1;">{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
    </div>
    @endif

    {{-- Page head --}}
    <div class="px-head">
        <div>
            <div class="px-crumb"><a href="{{ route('dashboard') }}">Dashboard</a> / Products / <span>All Products</span></div>
            <h1 class="px-title">All Products <span class="px-total-pill">{{ $totalProducts }} products</span></h1>
        </div>
        <a href="{{ route('products.create') }}" class="px-btn px-btn-primary"><i class="fas fa-plus"></i> New Product</a>
    </div>

    {{-- Toolbar: search + filter toggle + sort (all map to existing backend params) --}}
    <div class="px-toolbar">
        <form method="GET" action="{{ route('products.index') }}" class="px-search" style="display:contents">
            <div class="px-search">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products by name, product code, barcode…" autocomplete="off" autofocus aria-label="Search products">
            </div>
            @if(request('sort_by'))<input type="hidden" name="sort_by" value="{{ request('sort_by') }}">@endif
            @if(request('sort_dir'))<input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">@endif
        </form>
        <button type="button" class="px-btn px-btn-ghost" id="px-filter-btn" aria-expanded="false" aria-controls="px-filter-panel"><i class="fas fa-filter"></i> Filter</button>
        <select class="px-sort" id="px-sort" aria-label="Sort products"
                data-base="{{ route('products.index') }}" data-search="{{ request('search') }}">
            @php
                $curSort = ($sortBy ?? 'created_at') . ':' . ($sortDir ?? 'desc');
                $sortOpts = [
                    'created_at:desc' => 'Newest first',
                    'created_at:asc' => 'Oldest first',
                    'name:asc' => 'Name A–Z',
                    'name:desc' => 'Name Z–A',
                    'sale_price:asc' => 'Price: low to high',
                    'sale_price:desc' => 'Price: high to low',
                    'stock_quantity:asc' => 'Stock: low to high',
                    'stock_quantity:desc' => 'Stock: high to low',
                    'code:asc' => 'Product Code',
                ];
            @endphp
            @foreach($sortOpts as $val => $label)
                <option value="{{ $val }}" {{ $curSort === $val ? 'selected' : '' }}>Sort: {{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Filter panel: client-side, current page only (no backend change) --}}
    <div class="px-panel" id="px-filter-panel">
        <div class="px-panel-grid">
            <div class="px-field">
                <label for="px-f-cat">Category</label>
                <select id="px-f-cat">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="px-field">
                <label for="px-f-brand">Brand</label>
                <select id="px-f-brand">
                    <option value="">All brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="px-field">
                <label for="px-f-stock">Stock status</label>
                <select id="px-f-stock">
                    <option value="">All statuses</option>
                    <option value="in">In stock</option>
                    <option value="low">Low stock</option>
                    <option value="out">Out of stock</option>
                </select>
            </div>
        </div>
        <div class="px-panel-foot">
            <button type="button" class="px-btn px-btn-ghost px-btn-sm" id="px-f-reset">Reset</button>
            <span class="px-hint">Filters apply to this page only.</span>
            <span class="px-match" id="px-f-count" hidden></span>
        </div>
    </div>

    {{-- Count line (existing variables only) --}}
    <div class="px-count" id="px-count-line">
        @if($products->lastPage() > 1)
            Showing <strong>{{ $displayedRows }}</strong> unit rows from <strong>{{ $products->count() }}</strong> products on this page
            (Page {{ $products->currentPage() }} of {{ $products->lastPage() }})
            <span style="color:#94a3b8;">|</span> Total: <strong>{{ $totalProductCodeRows }}</strong> unit rows from <strong>{{ $totalProducts }}</strong> products
        @else
            Showing <strong>{{ $products->count() }}</strong> products (<strong>{{ $displayedRows }}</strong> unit rows)
            <span style="color:#94a3b8;">|</span> Total: <strong>{{ $totalProducts }}</strong> products
        @endif
        @if(request()->filled('sort_by'))
            <span style="color:#94a3b8;">|</span> Sorted by <strong>{{ ucfirst(str_replace('_', ' ', $sortBy)) }}</strong> ({{ strtoupper($sortDir) }})
            <a href="{{ route('products.index', array_filter(['search' => request('search')])) }}" style="color:#b91c1c;">Reset sort</a>
        @endif
    </div>

    <div class="px-table-card">
        <div class="px-scroll">
            <table class="px-table">
                <thead>
                    <tr>
                        <th class="c" style="width:36px;"><input type="checkbox" class="px-check" id="px-sel-all" aria-label="Select all products on this page"></th>
                        <th class="num">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_dir' => ($sortBy === 'created_at' && $sortDir === 'asc') ? 'desc' : 'asc']) }}"># {!! $sortBy === 'created_at' ? '<span class="arr">'.($sortDir === 'asc' ? '▲' : '▼').'</span>' : '' !!}</a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => ($sortBy === 'name' && $sortDir === 'asc') ? 'desc' : 'asc']) }}">Product {!! $sortBy === 'name' ? '<span class="arr">'.($sortDir === 'asc' ? '▲' : '▼').'</span>' : '' !!}</a>
                        </th>
                        <th class="r">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'sale_price', 'sort_dir' => ($sortBy === 'sale_price' && $sortDir === 'asc') ? 'desc' : 'asc']) }}">Price {!! $sortBy === 'sale_price' ? '<span class="arr">'.($sortDir === 'asc' ? '▲' : '▼').'</span>' : '' !!}</a>
                        </th>
                        <th class="c">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'stock_quantity', 'sort_dir' => ($sortBy === 'stock_quantity' && $sortDir === 'asc') ? 'desc' : 'asc']) }}">Stock {!! $sortBy === 'stock_quantity' ? '<span class="arr">'.($sortDir === 'asc' ? '▲' : '▼').'</span>' : '' !!}</a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'barcode', 'sort_dir' => ($sortBy === 'barcode' && $sortDir === 'asc') ? 'desc' : 'asc']) }}">Barcode {!! $sortBy === 'barcode' ? '<span class="arr">'.($sortDir === 'asc' ? '▲' : '▼').'</span>' : '' !!}</a>
                        </th>
                        <th class="c">Status</th>
                        <th class="c">Actions</th>
                    </tr>
                </thead>
                <tbody id="px-tbody">
                    @forelse($products as $product)
                        @php
                            // Same data computation as before — presentation only.
                            $availableUnits = !empty($hasProductUnits)
                                ? ($product->productUnits ?? collect())->where('status', 'available')->values()
                                : collect();
                            $allUnits = !empty($hasProductUnits)
                                ? ($product->productUnits ?? collect())->values()
                                : collect();
                            $variations = $product->productVariations ?? collect();
                            $totalVariationStock = $availableUnits->isNotEmpty()
                                ? $availableUnits->count()
                                : (int) $variations->sum('stock');
                            if ($totalVariationStock <= 0 && $availableUnits->isEmpty() && $variations->isEmpty()) {
                                $totalVariationStock = (int) ($product->stock_quantity ?? 0);
                            }

                            if ($allUnits->isNotEmpty()) {
                                $detailKind = 'units';
                                $detailRows = $allUnits->map(function ($unit) use ($product) {
                                    return (object) [
                                        'variation' => $unit->variation?->variation_value ?? '—',
                                        'code' => $unit->unit_code ?? $unit->product_code ?? '—',
                                        'barcode' => $product->barcode ?? '—',
                                        'status' => ucfirst((string) ($unit->status ?? 'available')),
                                    ];
                                });
                            } elseif ($variations->isNotEmpty()) {
                                $detailKind = 'variations';
                                $detailRows = $variations->map(function ($var) {
                                    return (object) [
                                        'variation' => $var->variation_value ?? 'Default',
                                        'code' => $var->product_code ?? '—',
                                        'barcode' => $var->barcode ?? '—',
                                        'status' => ((int) ($var->stock ?? 0)) > 0 ? 'In stock' : 'Out of stock',
                                    ];
                                });
                            } else {
                                $detailKind = null;
                                $detailRows = collect();
                            }

                            $isOut = $totalVariationStock <= 0;
                            $isLow = !$isOut && $product->isLowStock();
                            $statusKey = $isOut ? 'out' : ($isLow ? 'low' : 'in');
                            $statusLabel = $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock');
                            $statusClass = $isOut ? 'px-bad' : ($isLow ? 'px-warn' : 'px-ok');
                            $rowNo = $products->firstItem() + $loop->index;
                            $detailId = 'px-detail-' . $product->id;
                        @endphp
                        <tr class="px-prow" data-id="{{ $product->id }}"
                            data-cat="{{ $product->category_id ?? '' }}" data-brand="{{ $product->brand_id ?? '' }}"
                            data-stock="{{ $totalVariationStock }}" data-status="{{ $statusKey }}">
                            <td class="c" data-label="Select">
                                <input type="checkbox" class="px-check px-row-check" value="{{ $product->id }}"
                                       data-avail="{{ max(1, min(500, $totalVariationStock > 0 ? $totalVariationStock : 1)) }}"
                                       aria-label="Select {{ $product->name }}">
                            </td>
                            <td class="num" data-label="#">{{ $rowNo }}</td>
                            <td data-label="Product">
                                <div class="px-prod">
                                    <img src="{{ $product->image_url }}" onerror="this.src='{{ asset('images/no-product.png') }}'" alt="">
                                    <div>
                                        <div class="px-name"><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></div>
                                        <div class="px-meta">{{ $product->brand?->name ?? 'No brand' }} · {{ $product->category?->name ?? 'No category' }}</div>
                                        <span class="px-sku">Product Code&nbsp;{{ $product->code ?? $product->sku ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="r" data-label="Price">
                                <div class="px-price">৳{{ number_format((float) ($product->sale_price ?? 0), 2) }}</div>
                                <div class="px-cost">Cost ৳{{ number_format((float) ($product->purchase_price ?? 0), 2) }}</div>
                            </td>
                            <td class="c" data-label="Stock">
                                <div class="px-stock-n">{{ $totalVariationStock }} <small>{{ $totalVariationStock == 1 ? 'unit' : 'units' }}</small></div>
                                @if($detailRows->count() > 1)
                                    <button type="button" class="px-toggle" data-target="{{ $detailId }}" aria-expanded="false" aria-controls="{{ $detailId }}">
                                        Show {{ $detailRows->count() }} {{ $detailKind }} <i class="fas fa-chevron-down chev"></i>
                                    </button>
                                @endif
                            </td>
                            <td data-label="Barcode"><span class="px-barcode">{{ $product->barcode ?? '—' }}</span></td>
                            <td class="c" data-label="Status"><span class="px-badge {{ $statusClass }}"><span class="dot"></span>{{ $statusLabel }}</span></td>
                            <td class="c" data-label="Actions">
                                <div class="px-act">
                                    <a href="{{ route('products.show', $product->id) }}" class="px-icon-btn" aria-label="View {{ $product->name }}" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="px-icon-btn" aria-label="Edit {{ $product->name }}" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('products.destroy', $product->id) }}" class="action-form" style="display:inline;margin:0;padding:0;" onsubmit="return confirm('Delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-icon-btn danger" aria-label="Delete {{ $product->name }}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @if($detailRows->count() > 1)
                        <tr class="px-drow" id="{{ $detailId }}" hidden data-detail-for="{{ $product->id }}">
                            <td colspan="8">
                                <table class="px-mini">
                                    <thead>
                                        <tr><th scope="col">Variation</th><th scope="col">Product Code</th><th scope="col">Barcode</th><th scope="col">Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detailRows as $d)
                                        <tr>
                                            <td>{{ $d->variation }}</td>
                                            <td class="mono">{{ $d->code }}</td>
                                            <td class="mono">{{ $d->barcode }}</td>
                                            <td>{{ $d->status }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="px-empty">
                                    <i class="fas fa-box-open fa-2x" style="color:#94a3b8;"></i>
                                    <div class="t">No products found</div>
                                    <p>Try changing your search, or add your first product to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-pager px-4 pb-3">
            @if($products->hasPages())
                {{ $products->links() }}
            @endif
        </div>
    </div>
</div>

{{-- Bulk bar: selection count + Print Labels (existing labels.generate endpoint) + Clear --}}
<div class="px-bulk" id="px-bulk" role="status" aria-live="polite">
    <span class="n" id="px-bulk-n">0 selected</span>
    <button type="button" class="px-btn px-btn-primary px-btn-sm" id="px-bulk-print"><i class="fas fa-print"></i> Print Labels</button>
    <button type="button" class="px-btn-link" id="px-bulk-clear">Clear</button>
</div>

{{-- Hidden bulk-print form → existing POST /printlabels/generate (new tab) --}}
<form id="px-bulk-form" method="POST" action="{{ route('labels.generate') }}" target="_blank" style="display:none">
    @csrf
    <div id="px-bulk-fields"></div>
</form>

<script>
(function () {
    var tbody = document.getElementById('px-tbody');
    var selAll = document.getElementById('px-sel-all');
    var bulk = document.getElementById('px-bulk');
    var bulkN = document.getElementById('px-bulk-n');

    function rows() {
        return Array.prototype.slice.call(tbody.querySelectorAll('tr.px-prow:not([hidden])'));
    }
    function checked() {
        return rows().filter(function (r) {
            var c = r.querySelector('.px-row-check');
            return c && c.checked;
        });
    }
    function refreshBulk() {
        var n = checked().length;
        bulkN.textContent = n + (n === 1 ? ' selected' : ' selected');
        bulk.classList.toggle('show', n > 0);
        rows().forEach(function (r) {
            var c = r.querySelector('.px-row-check');
            r.classList.toggle('selected', !!(c && c.checked));
        });
        var all = tbody.querySelectorAll('.px-row-check').length > 0;
        var allChecked = all && tbody.querySelectorAll('.px-row-check:checked').length === tbody.querySelectorAll('.px-row-check').length;
        if (selAll) selAll.checked = allChecked;
    }

    tbody.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('px-row-check')) refreshBulk();
    });
    if (selAll) selAll.addEventListener('change', function () {
        rows().forEach(function (r) {
            var c = r.querySelector('.px-row-check');
            if (c) c.checked = selAll.checked;
        });
        refreshBulk();
    });

    /* Expand / collapse unit details */
    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.px-toggle') : null;
        if (!btn) return;
        var panel = document.getElementById(btn.getAttribute('data-target'));
        if (!panel) return;
        var open = panel.hasAttribute('hidden');
        if (open) {
            panel.removeAttribute('hidden');
            btn.setAttribute('aria-expanded', 'true');
            btn.firstChild.textContent = btn.firstChild.textContent.replace('Show', 'Hide');
        } else {
            panel.setAttribute('hidden', '');
            btn.setAttribute('aria-expanded', 'false');
            btn.firstChild.textContent = btn.firstChild.textContent.replace('Hide', 'Show');
        }
    });

    /* Bulk print → existing labels.generate endpoint */
    document.getElementById('px-bulk-print').addEventListener('click', function () {
        var fields = document.getElementById('px-bulk-fields');
        fields.innerHTML = '';
        var items = checked();
        if (!items.length) return;
        items.forEach(function (r, i) {
            var c = r.querySelector('.px-row-check');
            var qty = Math.max(1, parseInt(c.getAttribute('data-avail'), 10) || 1);
            fields.innerHTML += '<input type="hidden" name="products[' + i + '][id]" value="' + c.value + '">'
                + '<input type="hidden" name="products[' + i + '][quantity]" value="' + qty + '">';
        });
        fields.innerHTML += '<input type="hidden" name="settings[columns]" value="1">'
            + '<input type="hidden" name="settings[label_size]" value="medium">'
            + '<input type="hidden" name="settings[font_size]" value="normal">'
            + '<input type="hidden" name="settings[show_name]" value="1">'
            + '<input type="hidden" name="settings[show_price]" value="1">'
            + '<input type="hidden" name="settings[show_sku]" value="1">';
        document.getElementById('px-bulk-form').submit();
    });
    document.getElementById('px-bulk-clear').addEventListener('click', function () {
        tbody.querySelectorAll('.px-row-check:checked').forEach(function (c) { c.checked = false; });
        if (selAll) selAll.checked = false;
        refreshBulk();
    });

    /* Filter panel toggle */
    var fBtn = document.getElementById('px-filter-btn');
    var fPanel = document.getElementById('px-filter-panel');
    fBtn.addEventListener('click', function () {
        var open = fPanel.classList.toggle('open');
        fBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    /* Client-side filters (current page only) */
    var fCat = document.getElementById('px-f-cat');
    var fBrand = document.getElementById('px-f-brand');
    var fStock = document.getElementById('px-f-stock');
    var fCount = document.getElementById('px-f-count');
    function applyFilters() {
        var cat = fCat.value, brand = fBrand.value, st = fStock.value;
        var visible = 0, total = 0;
        rows().forEach(function (r) { r.removeAttribute('hidden'); });
        tbody.querySelectorAll('tr.px-prow').forEach(function (r) {
            total++;
            var ok = true;
            if (cat && (r.getAttribute('data-cat') || '') !== cat) ok = false;
            if (brand && (r.getAttribute('data-brand') || '') !== brand) ok = false;
            if (st) {
                var s = r.getAttribute('data-status');
                if (st === 'in' && s !== 'in') ok = false;
                if (st === 'low' && s !== 'low') ok = false;
                if (st === 'out' && s !== 'out') ok = false;
            }
            var det = r.nextElementSibling;
            var isDet = det && det.classList.contains('px-drow');
            if (!ok) {
                r.setAttribute('hidden', '');
                if (isDet) det.setAttribute('hidden', '');
            } else {
                visible++;
            }
        });
        var filtering = cat || brand || st;
        if (filtering) {
            fCount.hidden = false;
            fCount.textContent = visible + ' of ' + total + ' on this page';
        } else {
            fCount.hidden = true;
        }
        refreshBulk();
    }
    [fCat, fBrand, fStock].forEach(function (el) { el.addEventListener('change', applyFilters); });
    document.getElementById('px-f-reset').addEventListener('click', function () {
        fCat.value = ''; fBrand.value = ''; fStock.value = '';
        applyFilters();
    });

    /* Sort dropdown → same backend params as header links */
    var sortSel = document.getElementById('px-sort');
    sortSel.addEventListener('change', function () {
        var parts = sortSel.value.split(':');
        var params = new URLSearchParams();
        if (sortSel.getAttribute('data-search')) params.set('search', sortSel.getAttribute('data-search'));
        params.set('sort_by', parts[0]);
        params.set('sort_dir', parts[1]);
        window.location.href = sortSel.getAttribute('data-base') + '?' + params.toString();
    });

    refreshBulk();
})();
</script>
@stop