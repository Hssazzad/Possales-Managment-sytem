@extends('adminlte::page')

@section('title', 'Dashboard | BongoBay')

@section('content_header')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $userName = auth()->user()->name ?? 'Manager';
@endphp
<div class="dash-hero">
    <div class="dash-hero-info">
        <span class="dash-hero-eyebrow"><i class="fas fa-circle"></i> {{ now()->format('l, d F Y') }}</span>
        <h1 class="dash-greeting">{{ $greeting }}, {{ $userName }}</h1>
        <p class="dash-sub">{{ $todaySalesCount }} sale{{ $todaySalesCount == 1 ? '' : 's' }} today &middot; ৳{{ number_format($todayCollection ?? 0, 2) }} collected &middot; {{ $lowStockCount }} low-stock item{{ $lowStockCount == 1 ? '' : 's' }} need attention</p>
    </div>
    <div class="dash-hero-actions">
        <a href="{{ route('purchases.create') }}" class="dash-btn dash-btn-ghost"><i class="fas fa-truck"></i> New Purchase</a>
        <a href="{{ route('pos.index') }}" class="dash-btn dash-btn-primary"><i class="fas fa-plus"></i> New Sale</a>
    </div>
</div>
@stop

@section('content')

@if(session('success'))
    <div id="loginFlash" class="dash-flash">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    <script>
        setTimeout(function(){ var el = document.getElementById('loginFlash'); if(el) { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(function(){ el.remove(); }, 300); } }, 3000);
    </script>
@endif

{{-- KPI Cards --}}
<div class="row mt-1">
    <div class="col-xl-3 col-sm-6 col-12 mb-3">
        <div class="card dash-kpi h-100" style="--accent:#2563eb;">
            <div class="card-body">
                <div class="dash-kpi-top">
                    <span class="dash-kpi-label">Today's Sales</span>
                    <span class="dash-icon dash-icon-blue"><i class="fas fa-shopping-bag"></i></span>
                </div>
                <h3 class="dash-kpi-value">৳{{ number_format($todaySales, 2) }}</h3>
                <div class="dash-kpi-trend">
                    <span class="dash-pill {{ $todaySalesChange >= 0 ? 'dash-pill-up' : 'dash-pill-down' }}">
                        <i class="fas fa-arrow-{{ $todaySalesChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ number_format(abs($todaySalesChange), 1) }}%
                    </span>
                    <span class="dash-kpi-hint">vs yesterday &middot; {{ $todaySalesCount }} orders</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12 mb-3">
        <div class="card dash-kpi h-100" style="--accent:#16a34a;">
            <div class="card-body">
                <div class="dash-kpi-top">
                    <span class="dash-kpi-label">Total Sales</span>
                    <span class="dash-icon dash-icon-green"><i class="fas fa-chart-line"></i></span>
                </div>
                <h3 class="dash-kpi-value">৳{{ number_format($totalSales, 2) }}</h3>
                <div class="dash-kpi-trend">
                    <span class="dash-pill {{ $monthlySalesChange >= 0 ? 'dash-pill-up' : 'dash-pill-down' }}">
                        <i class="fas fa-arrow-{{ $monthlySalesChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ number_format(abs($monthlySalesChange), 1) }}%
                    </span>
                    <span class="dash-kpi-hint">vs last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12 mb-3">
        <div class="card dash-kpi h-100" style="--accent:#7c3aed;">
            <div class="card-body">
                <div class="dash-kpi-top">
                    <span class="dash-kpi-label">Net Profit</span>
                    <span class="dash-icon dash-icon-violet"><i class="fas fa-wallet"></i></span>
                </div>
                <h3 class="dash-kpi-value">৳{{ number_format($totalIncome, 2) }}</h3>
                <div class="dash-kpi-trend">
                    <span class="dash-pill dash-pill-neutral"><i class="fas fa-equals"></i> Sales &minus; cost</span>
                    <span class="dash-kpi-hint">after returns</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12 mb-3">
        <div class="card dash-kpi h-100" style="--accent:#e11d48;">
            <div class="card-body">
                <div class="dash-kpi-top">
                    <span class="dash-kpi-label">Total Expenses</span>
                    <span class="dash-icon dash-icon-rose"><i class="fas fa-file-invoice-dollar"></i></span>
                </div>
                <h3 class="dash-kpi-value">৳{{ number_format($totalExpense, 2) }}</h3>
                <div class="dash-kpi-trend">
                    <span class="dash-pill {{ $monthlyExpenseChange >= 0 ? 'dash-pill-down' : 'dash-pill-up' }}">
                        <i class="fas fa-arrow-{{ $monthlyExpenseChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ number_format(abs($monthlyExpenseChange), 1) }}%
                    </span>
                    <span class="dash-kpi-hint">vs last month</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick actions + Finance snapshot --}}
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card dash-panel h-100">
            <div class="card-header dash-panel-head">
                <h6><i class="fas fa-bolt"></i> Quick Actions</h6>
                <span class="text-muted small">Jump straight to the task</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3 mb-2">
                        <a href="{{ route('pos.index') }}" class="dash-action">
                            <span class="dash-icon dash-icon-blue"><i class="fas fa-shopping-cart"></i></span>
                            <strong>New Sale</strong>
                            <small>Open POS terminal</small>
                            <i class="fas fa-arrow-right dash-action-arrow"></i>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <a href="{{ route('purchases.create') }}" class="dash-action">
                            <span class="dash-icon dash-icon-amber"><i class="fas fa-truck"></i></span>
                            <strong>New Purchase</strong>
                            <small>Add stock from supplier</small>
                            <i class="fas fa-arrow-right dash-action-arrow"></i>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <a href="{{ route('products.create') }}" class="dash-action">
                            <span class="dash-icon dash-icon-green"><i class="fas fa-box"></i></span>
                            <strong>Add Product</strong>
                            <small>Register new item</small>
                            <i class="fas fa-arrow-right dash-action-arrow"></i>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <a href="{{ route('customers.create') }}" class="dash-action">
                            <span class="dash-icon dash-icon-violet"><i class="fas fa-user-plus"></i></span>
                            <strong>Add Customer</strong>
                            <small>Create customer profile</small>
                            <i class="fas fa-arrow-right dash-action-arrow"></i>
                        </a>
                    </div>
                </div>
                <div class="dash-glance">
                    <div class="dash-glance-item">
                        <span class="dash-icon dash-icon-blue"><i class="fas fa-box"></i></span>
                        <div><strong>{{ number_format($totalProducts) }}</strong><span>Products</span></div>
                    </div>
                    <div class="dash-glance-item">
                        <span class="dash-icon dash-icon-green"><i class="fas fa-users"></i></span>
                        <div><strong>{{ number_format($totalCustomers) }}</strong><span>Customers</span></div>
                    </div>
                    <div class="dash-glance-item">
                        <span class="dash-icon dash-icon-amber"><i class="fas fa-truck"></i></span>
                        <div><strong>{{ number_format($totalSuppliers) }}</strong><span>Suppliers</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card dash-panel h-100">
            <div class="card-header dash-panel-head">
                <h6><i class="fas fa-coins"></i> Finance Snapshot</h6>
                <span class="dash-badge-year">{{ now()->year }}</span>
            </div>
            <div class="card-body">
                <a href="{{ route('dues.index') }}" class="dash-finance-row">
                    <span class="dash-icon dash-icon-green"><i class="fas fa-hand-holding-usd"></i></span>
                    <div class="dash-finance-text"><strong>Receivable</strong><small>Customer dues to collect</small></div>
                    <span class="dash-finance-value">৳{{ number_format($customerDue ?? 0, 2) }}</span>
                </a>
                <a href="{{ route('supplier.dues') }}" class="dash-finance-row">
                    <span class="dash-icon dash-icon-rose"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="dash-finance-text"><strong>Payable</strong><small>Supplier dues to pay</small></div>
                    <span class="dash-finance-value">৳{{ number_format($supplierDue ?? 0, 2) }}</span>
                </a>
                <a href="{{ route('stock.index') }}" class="dash-finance-row">
                    <span class="dash-icon dash-icon-blue"><i class="fas fa-warehouse"></i></span>
                    <div class="dash-finance-text"><strong>Inventory Value</strong><small>Stock at cost price</small></div>
                    <span class="dash-finance-value">৳{{ number_format($inventoryValue ?? 0, 2) }}</span>
                </a>
                <div class="dash-finance-row">
                    <span class="dash-icon dash-icon-amber"><i class="fas fa-shopping-basket"></i></span>
                    <div class="dash-finance-text"><strong>Today's Purchase</strong><small>Fresh stock added today</small></div>
                    <span class="dash-finance-value">৳{{ number_format($todayPurchase ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card dash-panel h-100">
            <div class="card-header dash-panel-head">
                <h6><i class="fas fa-chart-area"></i> Sales Performance</h6>
                <span class="dash-badge-year">Jan – Dec {{ now()->year }}</span>
            </div>
            <div class="card-body">
                <div style="height:280px;"><canvas id="profitLossChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card dash-panel h-100">
            <div class="card-header dash-panel-head">
                <h6><i class="fas fa-chart-pie"></i> Money Flow</h6>
                <span class="dash-badge-year">All time</span>
            </div>
            <div class="card-body">
                <div class="dash-doughnut-wrap">
                    <canvas id="overallChart"></canvas>
                    <div class="dash-doughnut-center">
                        <span class="dash-doughnut-total">৳{{ number_format($totalSales + $totalPurchase, 0) }}</span>
                        <span class="dash-doughnut-caption">Turnover</span>
                    </div>
                </div>
                <div class="dash-legend">
                    <div class="dash-legend-row"><span><i class="dot" style="background:#2563eb;"></i>Sales</span><strong>৳{{ number_format($totalSales, 0) }}</strong></div>
                    <div class="dash-legend-row"><span><i class="dot" style="background:#f59e0b;"></i>Purchase</span><strong>৳{{ number_format($totalPurchase, 0) }}</strong></div>
                    <div class="dash-legend-row"><span><i class="dot" style="background:#16a34a;"></i>Profit</span><strong>৳{{ number_format($totalIncome, 0) }}</strong></div>
                    <div class="dash-legend-row"><span><i class="dot" style="background:#e11d48;"></i>Expense</span><strong>৳{{ number_format($totalExpense, 0) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity + Alerts --}}
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card dash-panel">
            <div class="card-header dash-tabs-head">
                <ul class="nav nav-tabs border-0" id="recentTab">
                    <li class="nav-item">
                        <a class="nav-link active dash-tab" data-toggle="tab" href="#recentSales">
                            <i class="fas fa-shopping-cart"></i> Recent Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link dash-tab" data-toggle="tab" href="#recentPurchase">
                            <i class="fas fa-truck"></i> Recent Purchases
                        </a>
                    </li>
                </ul>
                <a href="{{ route('sales.index') }}" class="dash-viewall" id="viewAllSales">View all <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body tab-content p-0">
                <div class="tab-pane active" id="recentSales">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0 dash-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSales as $sale)
                            <tr>
                                <td class="text-nowrap">{{ $sale->created_at->format('d M Y') }}</td>
                                <td class="font-weight-bold text-primary">{{ $sale->invoice_number }}</td>
                                <td>{{ $sale->customer_name ?: 'Walk-in Customer' }}</td>
                                <td class="text-right font-weight-bold">৳{{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-right text-success">৳{{ number_format($sale->paid_amount, 2) }}</td>
                                <td class="text-right {{ $sale->due_amount > 0 ? 'text-danger' : '' }}">৳{{ number_format($sale->due_amount, 2) }}</td>
                                <td>
                                    @if($sale->payment_status === 'paid')
                                        <span class="dash-status dash-status-paid">Paid</span>
                                    @elseif($sale->payment_status === 'returned')
                                        <span class="dash-status dash-status-returned">Returned</span>
                                    @elseif($sale->payment_status === 'partial' || $sale->due_amount > 0)
                                        <span class="dash-status dash-status-due">Due</span>
                                    @else
                                        <span class="dash-status dash-status-info">{{ ucfirst($sale->payment_status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                    <span class="text-muted">No sales yet. Create your first sale from the POS.</span>
                                    <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm mt-2">Open POS</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="tab-pane" id="recentPurchase">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0 dash-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Supplier</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPurchases as $purchase)
                            <tr>
                                <td class="text-nowrap">{{ $purchase->created_at->format('d M Y') }}</td>
                                <td class="font-weight-bold text-primary">{{ $purchase->invoice_number }}</td>
                                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                <td class="text-right font-weight-bold">৳{{ number_format($purchase->total_amount, 2) }}</td>
                                <td class="text-right text-success">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="text-right {{ $purchase->due_amount > 0 ? 'text-danger' : '' }}">৳{{ number_format($purchase->due_amount, 2) }}</td>
                                <td>
                                    @if($purchase->payment_status === 'paid')
                                        <span class="dash-status dash-status-paid">Paid</span>
                                    @elseif($purchase->due_amount > 0)
                                        <span class="dash-status dash-status-due">Due</span>
                                    @else
                                        <span class="dash-status dash-status-info">{{ ucfirst($purchase->payment_status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                    <span class="text-muted">No purchases yet.</span>
                                    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm mt-2">New Purchase</a>
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

    <div class="col-lg-4 mb-3">
        <div class="card dash-panel h-100">
            <div class="card-header dash-panel-head">
                <h6><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h6>
                <span class="dash-alert-badge">{{ $lowStockCount ?? 0 }} items</span>
            </div>
            <div class="card-body">
                @forelse($lowStockProducts ?? [] as $product)
                    <div class="dash-alert-row">
                        <div class="dash-alert-info">
                            <strong>{{ \Illuminate\Support\Str::limit($product->name, 34) }}</strong>
                            <small>{{ $product->stock_quantity }} in stock &middot; min {{ $product->min_stock_level ?? 5 }}</small>
                            <div class="dash-alert-bar"><span style="width:{{ $product->min_stock_level > 0 ? min(100, ($product->stock_quantity / max(1, $product->min_stock_level)) * 100) : 100 }}%"></span></div>
                        </div>
                        <a href="{{ route('products.edit', $product->id) }}" class="dash-btn dash-btn-small">Restock</a>
                    </div>
                @empty
                    <div class="dash-alert-empty">
                        <i class="fas fa-check-circle"></i>
                        <strong>All stocked up</strong>
                        <small>No low-stock items right now.</small>
                    </div>
                @endforelse
                <a href="{{ route('stock.low') }}" class="dash-viewall d-block mt-3">View all low stock <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --dash-bg: #f1f4f9;
        --dash-card: #ffffff;
        --dash-border: #e8edf3;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-blue: #2563eb;
        --dash-green: #16a34a;
        --dash-amber: #d97706;
        --dash-rose: #e11d48;
        --dash-violet: #7c3aed;
    }
    body, .content-wrapper { background: var(--dash-bg) !important; font-family: 'Inter', sans-serif !important; }
    .card { border: 1px solid var(--dash-border) !important; }

    /* Hero */
    .dash-hero {
        position: relative; overflow: hidden;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #1d4ed8 100%);
        border-radius: 16px; padding: 22px 26px; margin-bottom: 16px;
        box-shadow: 0 8px 24px rgba(15,23,42,0.18);
    }
    .dash-hero::before, .dash-hero::after {
        content: ''; position: absolute; border-radius: 50%;
    }
    .dash-hero::before { width: 260px; height: 260px; right: -70px; top: -120px; background: radial-gradient(circle, rgba(255,255,255,0.14) 0%, transparent 70%); }
    .dash-hero::after { width: 180px; height: 180px; right: 120px; bottom: -110px; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); }
    .dash-hero-info { position: relative; z-index: 1; }
    .dash-hero-eyebrow { display: inline-flex; align-items: center; gap: 7px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #93c5fd; }
    .dash-hero-eyebrow i { font-size: 0.45rem; }
    .dash-greeting { margin: 6px 0 4px; font-weight: 800; font-size: 1.55rem; color: #fff; letter-spacing: -0.3px; }
    .dash-sub { margin: 0; color: #cbd5e1; font-size: 0.85rem; }
    .dash-hero-actions { position: relative; z-index: 1; display: flex; gap: 10px; flex-wrap: wrap; }
    .dash-btn {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 0.82rem; font-weight: 600;
        padding: 9px 16px; border-radius: 10px; text-decoration: none !important;
        transition: all 0.15s; border: 1px solid transparent;
    }
    .dash-btn-primary { background: #fff; color: #0f172a !important; }
    .dash-btn-primary:hover { background: #eff6ff; color: #0f172a !important; transform: translateY(-1px); }
    .dash-btn-ghost { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.25); color: #fff !important; }
    .dash-btn-ghost:hover { background: rgba(255,255,255,0.2); color: #fff !important; }
    .dash-btn-small { padding: 5px 10px; font-size: 0.75rem; background: #fff7ed; border-color: #fed7aa; color: #9a3412 !important; }
    .dash-btn-small:hover { background: #ffedd5; }

    /* Flash */
    .dash-flash {
        position: fixed; top: 15px; right: 15px; z-index: 9999;
        background: #dcfce7; color: #15803d; border: 1px solid #86efac;
        border-radius: 10px; padding: 10px 16px; font-size: 13px; font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        display: flex; align-items: center; gap: 8px;
        animation: dashSlideIn 0.3s ease-out;
    }
    @keyframes dashSlideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* KPI */
    .dash-kpi { border-radius: 16px !important; box-shadow: 0 1px 4px rgba(15,23,42,0.05) !important; transition: transform 0.15s, box-shadow 0.15s; position: relative; overflow: hidden; }
    .dash-kpi::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--accent, #2563eb); }
    .dash-kpi:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,0.08) !important; }
    .dash-kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .dash-kpi-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--dash-muted); }
    .dash-icon {
        width: 40px; height: 40px; border-radius: 11px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.95rem; flex-shrink: 0;
    }
    .dash-icon-blue { background: #dbeafe; color: #1d4ed8; }
    .dash-icon-green { background: #dcfce7; color: #15803d; }
    .dash-icon-violet { background: #ede9fe; color: #6d28d9; }
    .dash-icon-rose { background: #ffe4e6; color: #be123c; }
    .dash-icon-amber { background: #fef3c7; color: #b45309; }
    .dash-kpi-value { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; color: var(--dash-text); margin: 0 0 10px; }
    .dash-kpi-trend { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
    .dash-pill {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.72rem; font-weight: 700;
        padding: 3px 10px; border-radius: 20px;
    }
    .dash-pill i { font-size: 0.62rem; }
    .dash-pill-up { background: #dcfce7; color: #15803d; }
    .dash-pill-down { background: #fee2e2; color: #b91c1c; }
    .dash-pill-neutral { background: #f1f5f9; color: #475569; }
    .dash-kpi-hint { font-size: 0.75rem; color: var(--dash-muted); }

    /* Panels */
    .dash-panel { border-radius: 16px !important; box-shadow: 0 1px 4px rgba(15,23,42,0.05) !important; overflow: hidden; }
    .dash-panel-head {
        display: flex; justify-content: space-between; align-items: center; gap: 10px;
        background: #fff !important; border-bottom: 1px solid var(--dash-border) !important;
        padding: 14px 20px !important;
    }
    .dash-panel-head h6 { margin: 0; font-weight: 700; font-size: 0.9rem; color: var(--dash-text); }
    .dash-panel-head h6 i { color: var(--dash-blue); margin-right: 8px; }
    .dash-badge-year { font-size: 0.72rem; font-weight: 700; color: var(--dash-muted); background: #f1f5f9; padding: 4px 12px; border-radius: 20px; }
    .dash-alert-badge { font-size: 0.72rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 4px 12px; border-radius: 20px; }

    /* Quick actions */
    .dash-action {
        position: relative; display: flex; flex-direction: column; gap: 3px;
        background: #f8fafc; border: 1px solid var(--dash-border); border-radius: 14px;
        padding: 14px; text-decoration: none !important; color: var(--dash-text) !important;
        transition: all 0.15s; min-height: 110px; justify-content: center;
    }
    .dash-action:hover { background: #fff; border-color: var(--dash-blue); transform: translateY(-2px); box-shadow: 0 6px 14px rgba(15,23,42,0.08); }
    .dash-action strong { font-size: 0.9rem; font-weight: 700; }
    .dash-action small { font-size: 0.75rem; color: var(--dash-muted); }
    .dash-action .dash-icon { margin-bottom: 6px; }
    .dash-action-arrow { position: absolute; right: 12px; top: 12px; font-size: 0.7rem; color: var(--dash-muted); }
    .dash-glance { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 12px; border-top: 1px solid #f1f5f9; padding: 14px 6px 0; margin-top: 12px; }
    .dash-glance-item { display: flex; align-items: center; gap: 10px; }
    .dash-glance-item strong { display: block; font-size: 1.15rem; font-weight: 800; color: var(--dash-text); line-height: 1.2; }
    .dash-glance-item span span { font-size: 0.75rem; color: var(--dash-muted); font-weight: 500; }

    /* Finance */
    .dash-finance-row {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 4px; border-bottom: 1px solid #f1f5f9; text-decoration: none !important;
    }
    .dash-finance-row:last-child { border-bottom: none; }
    a.dash-finance-row:hover { background: #f8fafc; border-radius: 10px; }
    .dash-finance-text { flex: 1; min-width: 0; }
    .dash-finance-text strong { display: block; font-size: 0.88rem; font-weight: 700; color: var(--dash-text); }
    .dash-finance-text small { font-size: 0.75rem; color: var(--dash-muted); }
    .dash-finance-value { font-size: 0.95rem; font-weight: 800; color: var(--dash-text); white-space: nowrap; }

    /* Doughnut */
    .dash-doughnut-wrap { position: relative; max-width: 210px; margin: 4px auto 0; }
    .dash-doughnut-center {
        position: absolute; inset: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        pointer-events: none;
    }
    .dash-doughnut-total { font-size: 1.05rem; font-weight: 800; color: var(--dash-text); }
    .dash-doughnut-caption { font-size: 0.7rem; color: var(--dash-muted); text-transform: uppercase; letter-spacing: 0.8px; }
    .dash-legend { margin-top: 16px; }
    .dash-legend-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 2px; font-size: 0.82rem; border-bottom: 1px solid #f1f5f9;
    }
    .dash-legend-row:last-child { border-bottom: none; }
    .dash-legend-row .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }

    /* Alerts */
    .dash-alert-row { display: flex; align-items: center; gap: 10px; padding: 11px 0; border-bottom: 1px solid #f1f5f9; }
    .dash-alert-row:last-child { border-bottom: none; }
    .dash-alert-info { flex: 1; min-width: 0; }
    .dash-alert-info strong { display: block; font-size: 0.85rem; font-weight: 700; color: var(--dash-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dash-alert-info small { font-size: 0.75rem; color: var(--dash-muted); }
    .dash-alert-bar { height: 5px; background: #f1f5f9; border-radius: 99px; margin-top: 6px; overflow: hidden; }
    .dash-alert-bar span { display: block; height: 100%; background: linear-gradient(90deg, #f59e0b, #f97316); border-radius: 99px; }
    .dash-alert-empty { text-align: center; padding: 26px 10px; }
    .dash-alert-empty i { font-size: 1.8rem; color: var(--dash-green); display: block; margin-bottom: 8px; }
    .dash-alert-empty strong { display: block; font-size: 0.9rem; font-weight: 700; color: var(--dash-text); }
    .dash-alert-empty small { font-size: 0.8rem; color: var(--dash-muted); }

    /* Tabs + table */
    .dash-tabs-head { display: flex; justify-content: space-between; align-items: center; background: #fff !important; border-bottom: 1px solid var(--dash-border) !important; padding: 0 20px 0 8px !important; }
    .dash-tab { font-size: 0.85rem; font-weight: 700; color: var(--dash-muted) !important; border: none !important; padding: 14px 16px !important; }
    .dash-tab i { margin-right: 6px; }
    .dash-tab.active { color: var(--dash-blue) !important; box-shadow: inset 0 -2px 0 var(--dash-blue); }
    .dash-viewall { font-size: 0.8rem; font-weight: 600; color: var(--dash-blue); text-decoration: none !important; white-space: nowrap; }
    .dash-viewall:hover { text-decoration: underline !important; }
    .dash-table { font-size: 0.85rem; }
    .dash-table thead th {
        background: #f8fafc; border: none !important;
        font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.8px;
        color: var(--dash-muted); font-weight: 700; padding: 12px 12px !important;
    }
    .dash-table thead th:first-child { padding-left: 20px !important; }
    .dash-table tbody td { padding: 11px 12px !important; vertical-align: middle !important; }
    .dash-table tbody td:first-child { padding-left: 20px !important; }
    .dash-table tbody tr:hover { background: #f0f5ff !important; }
    .dash-status {
        display: inline-block; font-size: 0.7rem; font-weight: 700;
        padding: 3px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.4px;
    }
    .dash-status-paid { background: #dcfce7; color: #15803d; }
    .dash-status-due { background: #fef3c7; color: #b45309; }
    .dash-status-returned { background: #fee2e2; color: #b91c1c; }
    .dash-status-info { background: #e0e7ff; color: #3730a3; }

    /* Sidebar polish */
    .main-sidebar, .main-sidebar::before { background: #1f2937 !important; }
    .brand-link { background: #111827 !important; border-bottom: 1px solid #374151 !important; }
    .brand-link .brand-text { color: #fff !important; font-weight: 700 !important; }
    .nav-sidebar .nav-item > .nav-link { color: #cbd5e1 !important; font-size: 0.875rem !important; transition: all 0.2s; }
    .nav-sidebar .nav-item > .nav-link:hover { background: rgba(37,99,235,0.15) !important; color: #fff !important; }
    .nav-sidebar .nav-item > .nav-link.active { background: var(--dash-blue) !important; color: #fff !important; border-radius: 8px !important; }
    .main-header { border-bottom: 1px solid #e9ecef !important; box-shadow: 0 1px 8px rgba(0,0,0,0.06) !important; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    @media (max-width: 576px) {
        .dash-hero { padding: 18px; }
        .dash-greeting { font-size: 1.2rem; }
        .dash-kpi-value { font-size: 1.3rem; }
        .dash-hero-actions { width: 100%; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Performance — gradient line chart
(function () {
    var ctx = document.getElementById('profitLossChart').getContext('2d');
    function gradient(color) {
        var g = ctx.createLinearGradient(0, 0, 0, 260);
        g.addColorStop(0, color.replace('1)', '0.22)'));
        g.addColorStop(1, color.replace('1)', '0)'));
        return g;
    }
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($monthlyData['labels']),
            datasets: [
                {
                    label: 'Sales',
                    data: @json($monthlyData['sales']),
                    borderColor: 'rgba(37,99,235,1)',
                    backgroundColor: gradient('rgba(37,99,235,1)'),
                    borderWidth: 2.5, tension: 0.42, fill: true,
                    pointBackgroundColor: '#2563eb', pointBorderColor: '#fff',
                    pointRadius: 3.5, pointHoverRadius: 6, pointBorderWidth: 2
                },
                {
                    label: 'Profit',
                    data: @json($monthlyData['profit']),
                    borderColor: 'rgba(22,163,74,1)',
                    backgroundColor: gradient('rgba(22,163,74,1)'),
                    borderWidth: 2.5, tension: 0.42, fill: true,
                    pointBackgroundColor: '#16a34a', pointBorderColor: '#fff',
                    pointRadius: 3.5, pointHoverRadius: 6, pointBorderWidth: 2
                },
                {
                    label: 'Purchase',
                    data: @json($monthlyData['purchase']),
                    borderColor: 'rgba(217,119,6,1)',
                    backgroundColor: gradient('rgba(217,119,6,1)'),
                    borderWidth: 2, tension: 0.42, fill: true,
                    borderDash: [6, 4],
                    pointBackgroundColor: '#d97706', pointBorderColor: '#fff',
                    pointRadius: 3, pointHoverRadius: 6, pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, padding: 18, font: { family: 'Inter', size: 11, weight: '600' } } },
                tooltip: {
                    backgroundColor: '#0f172a', padding: 12, cornerRadius: 10,
                    titleFont: { family: 'Inter', size: 12, weight: '700' },
                    callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ৳' + Number(c.parsed.y).toLocaleString('en-US', { maximumFractionDigits: 0 }); } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(15,23,42,0.05)' },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b', callback: function (v) { return v >= 1000 ? (v / 1000) + 'k' : v; } }
                },
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' } }
            }
        }
    });
})();

// Money Flow — doughnut with correct values in tooltips
new Chart(document.getElementById('overallChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Purchase', 'Sales', 'Profit', 'Expense'],
        datasets: [{
            data: [{{ (float) $totalPurchase }}, {{ (float) $totalSales }}, {{ (float) $totalIncome }}, {{ (float) $totalExpense }}],
            backgroundColor: ['#f59e0b', '#2563eb', '#16a34a', '#e11d48'],
            borderWidth: 3,
            borderColor: '#ffffff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a', padding: 12, cornerRadius: 10,
                callbacks: { label: function (c) { return ' ' + c.label + ': ৳' + Number(c.parsed).toLocaleString('en-US', { maximumFractionDigits: 0 }); } }
            }
        }
    }
});

// View-all link follows the active tab
document.querySelectorAll('#recentTab .dash-tab').forEach(function (tab) {
    tab.addEventListener('shown.bs.tab', function (e) {
        var link = document.getElementById('viewAllSales');
        if (e.target.getAttribute('href') === '#recentPurchase') {
            link.setAttribute('href', '{{ route('purchases.index') }}');
        } else {
            link.setAttribute('href', '{{ route('sales.index') }}');
        }
    });
});
</script>
@stop
