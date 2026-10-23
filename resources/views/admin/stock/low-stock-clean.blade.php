@extends('adminlte::page')

@section('title', 'Low Stock Items | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
    <style>
        @media print {
            body * { visibility: hidden; }
            .no-print { display: none !important; }
            .list-container, .list-container * { visibility: visible; }
            .list-container { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none; }
            th:last-child, td:last-child { display: none !important; }
        }
    </style>
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        {{-- Top Toolbar --}}
        <div class="top-toolbar no-print">
            <form method="GET" action="{{ route('stock.low') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;flex:1;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product name, code or variation...">
                </div>
                <select name="filter" onchange="this.form.submit()" class="form-control" style="max-width:200px;">
                    <option value="all"       {{ request('filter','all') === 'all'       ? 'selected' : '' }}>All Low Stock</option>
                    <option value="critical"  {{ request('filter') === 'critical'        ? 'selected' : '' }}>Critical (&le;2)</option>
                    <option value="low"       {{ request('filter') === 'low'             ? 'selected' : '' }}>Low (3&ndash;5)</option>
                    <option value="variation" {{ request('filter') === 'variation'       ? 'selected' : '' }}>Variations Only</option>
                    <option value="product"   {{ request('filter') === 'product'         ? 'selected' : '' }}>Products Only</option>
                </select>
                <button type="submit" class="btn-export">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                @if(request('search') || (request('filter') && request('filter') !== 'all'))
                    <a href="{{ route('stock.low') }}" class="btn-add-new bg-secondary">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                @endif
            </form>

            <div class="action-buttons">
                <button onclick="window.print()" class="btn-export">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('stock.lowstock.export', request()->only(['search','filter'])) }}" class="btn-export" style="background-color:#10b981;color:white;border:none;text-decoration:none;display:inline-flex;align-items:center;padding:0.5rem 1rem;border-radius:4px;">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
                <a href="{{ route('stock.index') }}" class="btn-add-new bg-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="d-flex flex-wrap mb-3 no-print" style="gap:0.75rem;">
            <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                Total: <strong class="text-warning">{{ $all->count() }}</strong>
            </span>
            <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                Critical (&le;2): <strong class="text-danger">{{ $all->where('stock_quantity','<=',2)->count() }}</strong>
            </span>
            <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                Variations: <strong style="color:#7b1fa2;">{{ $all->where('is_variation',true)->count() }}</strong>
            </span>
            <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                Total Units: <strong class="text-primary">{{ $all->sum('stock_quantity') }}</strong>
            </span>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:60px;">Image</th>
                        <th>Product Name</th>
                        <th>Variation</th>
                        <th>Code</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Unit</th>
                        <th class="text-right">Price</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-print"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($all as $row)
                    <tr>
                        <td class="text-muted font-weight-bold">{{ $loop->iteration }}</td>

                        <td>
                            @if($row->image)
                                <img src="{{ asset('storage/' . $row->image) }}"
                                    style="width:35px;height:35px;object-fit:cover;border-radius:4px;border:1px solid #e5e7eb;"
                                    onerror="this.style.display='none'">
                            @else
                                <div style="width:35px;height:35px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;border-radius:4px;border:1px solid #e5e7eb;">
                                    <i class="fas fa-box text-muted" style="font-size:14px;"></i>
                                </div>
                            @endif
                        </td>

                        <td class="font-weight-bold text-dark">{{ $row->name }}</td>

                        <td>
                            @if($row->is_variation)
                                <span class="badge" style="background:#ede7f6;color:#4527a0;font-size:0.8rem;padding:4px 10px;border-radius:20px;">
                                    <i class="fas fa-tag" style="font-size:0.7rem;"></i>
                                    {{ $row->variation_label }}
                                </span>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>

                        <td class="text-muted">{{ $row->code ?? '-' }}</td>

                        <td class="text-center font-weight-bold">
                            @if(($row->stock_quantity ?? 0) <= 2)
                                <span class="status-badge status-inactive">{{ $row->stock_quantity ?? 0 }}</span>
                            @else
                                <span class="status-badge" style="background-color:#fef08a;color:#854d0e;">{{ $row->stock_quantity ?? 0 }}</span>
                            @endif
                        </td>

                        <td class="text-center text-muted">{{ $row->unit ?? 'pcs' }}</td>

                        <td class="text-right font-weight-bold">
                            ৳{{ number_format($row->price ?? 0, 2) }}
                        </td>

                        <td class="text-center">
                            @if(($row->stock_quantity ?? 0) <= 2)
                                <span class="badge badge-danger" style="font-size:0.78rem;">&#9888; CRITICAL</span>
                            @else
                                <span class="badge badge-warning" style="font-size:0.78rem;">&#9889; LOW</span>
                            @endif
                        </td>

                        <td class="text-center no-print">
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <a class="dropdown-item" href="{{ route('products.show', $row->id) }}">
                                        <i class="fas fa-eye text-primary mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item" href="{{ route('products.edit', $row->id) }}">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                <h5>No Low Stock Items Found</h5>
                                <p>All products and variations have sufficient stock.</p>
                                <a href="{{ route('stock.index') }}" class="btn-add-new mt-2 d-inline-block">
                                    <i class="fas fa-warehouse mr-1"></i> View All Stock
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($all->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 no-print" style="border-top:1px solid #e5e7eb;">
            <div class="text-muted text-sm">Showing {{ $all->count() }} items</div>
        </div>
        @endif

    </div>
</div>
@stop