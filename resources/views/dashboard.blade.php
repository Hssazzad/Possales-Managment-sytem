@extends('adminlte::page')

@section('title', 'Dashboard | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold" style="color:#343a40; font-size:1.3rem;">
        <i class="fas fa-tachometer-alt mr-2" style="color:#007bff;"></i> Dashboard
    </h1>
    <span class="text-muted" style="font-size:0.82rem;">
        <i class="far fa-calendar-alt mr-1"></i> {{ now()->format('l, d F Y') }}
    </span>
</div>
@stop

@section('content')

{{-- Stats Cards --}}
<div class="row mt-2">

    <div class="col-lg-3 col-sm-6 col-12 mb-3">
        <div class="card h-100 shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <p class="mb-1 font-weight-bold text-uppercase" style="font-size:0.7rem; letter-spacing:1.2px; color:#64748b;">Today Sales</p>
                    <h3 class="font-weight-bold mb-1" style="color:#0f172a; font-size:1.6rem;">৳0.00</h3>
                    <small style="color:#16a34a; font-size:0.75rem;"><i class="fas fa-arrow-up mr-1"></i>0% from yesterday</small>
                </div>
                <div style="background:#f1f5f9; border-radius:12px; width:54px; height:54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-shopping-bag" style="color:#003366; font-size:1.4rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-12 mb-3">
        <div class="card h-100 shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <p class="mb-1 font-weight-bold text-uppercase" style="font-size:0.7rem; letter-spacing:1.2px; color:#64748b;">Total Sales</p>
                    <h3 class="font-weight-bold mb-1" style="color:#0f172a; font-size:1.6rem;">৳0.00</h3>
                    <small style="color:#16a34a; font-size:0.75rem;"><i class="fas fa-arrow-up mr-1"></i>0% this month</small>
                </div>
                <div style="background:#f1f5f9; border-radius:12px; width:54px; height:54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-chart-line" style="color:#16a34a; font-size:1.4rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-12 mb-3">
        <div class="card h-100 shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <p class="mb-1 font-weight-bold text-uppercase" style="font-size:0.7rem; letter-spacing:1.2px; color:#64748b;">Total Income</p>
                    <h3 class="font-weight-bold mb-1" style="color:#0f172a; font-size:1.6rem;">৳0.00</h3>
                    <small style="color:#003366; font-size:0.75rem;"><i class="fas fa-minus mr-1"></i>0% this month</small>
                </div>
                <div style="background:#f1f5f9; border-radius:12px; width:54px; height:54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-wallet" style="color:#003366; font-size:1.4rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-12 mb-3">
        <div class="card h-100 shadow-sm border-0" style="border-radius:8px;">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <p class="mb-1 font-weight-bold text-uppercase" style="font-size:0.7rem; letter-spacing:1.2px; color:#64748b;">Total Expense</p>
                    <h3 class="font-weight-bold mb-1" style="color:#0f172a; font-size:1.6rem;">৳0.00</h3>
                    <small style="color:#dc3545; font-size:0.75rem;"><i class="fas fa-arrow-down mr-1"></i>0% this month</small>
                </div>
                <div style="background:#f1f5f9; border-radius:12px; width:54px; height:54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-file-invoice-dollar" style="color:#dc3545; font-size:1.4rem;"></i>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Charts Row --}}
<div class="row">

    <div class="col-md-8 mb-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-radius:8px 8px 0 0; border-bottom:1px solid #f1f5f9;">
                <h6 class="m-0 font-weight-bold" style="color:#1e293b; font-size:0.9rem;">
                    <i class="fas fa-chart-area mr-2" style="color:#003366;"></i>Profit & Loss Overview
                </h6>
                <select class="form-control form-control-sm" style="width:90px; border:1px solid #e9ecef; border-radius:6px; font-size:0.8rem;">
                    <option>2026</option>
                    <option>2025</option>
                </select>
            </div>
            <div class="card-body pt-3 pb-2">
                <canvas id="profitLossChart" height="105"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-radius:8px 8px 0 0; border-bottom:1px solid #f1f5f9;">
                <h6 class="m-0 font-weight-bold" style="color:#1e293b; font-size:0.9rem;">
                    <i class="fas fa-chart-pie mr-2" style="color:#003366;"></i>Overall Reports
                </h6>
                <select class="form-control form-control-sm" style="width:90px; border:1px solid #e9ecef; border-radius:6px; font-size:0.8rem;">
                    <option>2026</option>
                    <option>2025</option>
                </select>
            </div>
            <div class="card-body text-center pb-2">
                <div style="position:relative; max-width:200px; margin:0 auto;">
                    <canvas id="overallChart" height="200"></canvas>
                </div>
                <div class="mt-3 text-left px-2">
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f5f5f5;">
                        <span style="font-size:0.82rem;"><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#f39c12; margin-right:6px;"></span>Purchase</span>
                        <span class="font-weight-bold" style="font-size:0.82rem;">৳0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f5f5f5;">
                        <span style="font-size:0.82rem;"><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#007bff; margin-right:6px;"></span>Sales</span>
                        <span class="font-weight-bold" style="font-size:0.82rem;">৳0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f5f5f5;">
                        <span style="font-size:0.82rem;"><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#28a745; margin-right:6px;"></span>Income</span>
                        <span class="font-weight-bold" style="font-size:0.82rem;">৳0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span style="font-size:0.82rem;"><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#dc3545; margin-right:6px;"></span>Expense</span>
                        <span class="font-weight-bold" style="font-size:0.82rem;">৳0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Recent Tables --}}
<div class="row">
    <div class="col-12 mb-3">
        <div class="card shadow-sm border-0" style="border-radius:8px;">
            <div class="card-header bg-white p-0" style="border-radius:8px 8px 0 0; border-bottom:1px solid #f1f5f9;">
                <ul class="nav nav-tabs border-0" id="recentTab">
                    <li class="nav-item">
                        <a class="nav-link active px-4 py-3 font-weight-bold tab-active-link" data-toggle="tab" href="#recentSales" style="font-size:0.85rem; border:none; border-radius:0;">
                            <i class="fas fa-shopping-cart mr-1"></i> Recent Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-4 py-3 text-muted tab-inactive-link" data-toggle="tab" href="#recentPurchase" style="font-size:0.85rem; border:none; border-radius:0;">
                            <i class="fas fa-truck mr-1"></i> Recent Purchase
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body tab-content p-0">

                <div class="tab-pane active" id="recentSales">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th class="border-0 pl-4 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Date</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Invoice</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Customer</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Total</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Paid</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Due</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-2x d-block mb-2" style="color:#dee2e6;"></i>
                                    <span class="text-muted" style="font-size:0.85rem;">No sales records found</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="recentPurchase">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#f8f9fa;">
                            <tr>
                                <th class="border-0 pl-4 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Date</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Invoice</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Supplier</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Total</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Paid</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Due</th>
                                <th class="border-0 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#6c757d; font-weight:700;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-2x d-block mb-2" style="color:#dee2e6;"></i>
                                    <span class="text-muted" style="font-size:0.85rem;">No purchase records found</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body,
    .content-wrapper { background: #f4f6f9 !important; font-family: 'Inter', sans-serif !important; }

    /* Sidebar */
    .main-sidebar,
    .main-sidebar::before { background: #343a40 !important; }
    .brand-link { background: #2c3136 !important; border-bottom: 1px solid #4a5568 !important; }
    .brand-link .brand-text { color: #fff !important; font-weight: 700 !important; }

    /* Sidebar Nav */
    .nav-sidebar .nav-item > .nav-link { color: #ced4da !important; font-size: 0.875rem !important; transition: all 0.2s; }
    .nav-sidebar .nav-item > .nav-link:hover { background: rgba(0,123,255,0.12) !important; color: #fff !important; }
    .nav-sidebar .nav-item > .nav-link.active { background: #007bff !important; color: #fff !important; border-radius: 6px !important; }
    .nav-sidebar .nav-treeview .nav-link { color: #adb5bd !important; font-size: 0.82rem !important; }
    .nav-sidebar .nav-treeview .nav-link:hover { color: #fff !important; background: rgba(255,255,255,0.08) !important; }
    .nav-sidebar .nav-treeview .nav-link.active { color: #007bff !important; background: transparent !important; }

    /* Cards */
    .card { border: none !important; }

    /* Topnav */
    .main-header { border-bottom: 1px solid #e9ecef !important; box-shadow: 0 1px 8px rgba(0,0,0,0.06) !important; }

    /* Tables */
    .table td, .table th { vertical-align: middle !important; }
    .table-hover tbody tr:hover { background: #f0f4ff !important; }

    /* Tabs */
    .tab-active-link { color: #007bff !important; border-bottom: 2px solid #007bff !important; }
    .tab-inactive-link:hover { color: #007bff !important; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 10px; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Profit/Loss Line Chart
new Chart(document.getElementById('profitLossChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [
            {
                label: 'Profit',
                data: [0,0,0,0,0,0,0,0,0,0,0,0],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40,167,69,0.07)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#28a745',
                pointRadius: 3,
                pointHoverRadius: 6,
            },
            {
                label: 'Loss',
                data: [0,0,0,0,0,0,0,0,0,0,0,0],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220,53,69,0.07)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#dc3545',
                pointRadius: 3,
                pointHoverRadius: 6,
            },
            {
                label: 'Sales',
                data: [0,0,0,0,0,0,0,0,0,0,0,0],
                borderColor: '#003366',
                backgroundColor: 'rgba(0,51,102,0.07)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#003366',
                pointRadius: 3,
                pointHoverRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: { usePointStyle: true, padding: 20, font: { family: 'Inter', size: 11 } }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { family: 'Inter', size: 11 }, color: '#6c757d' }
            },
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Inter', size: 11 }, color: '#6c757d' }
            }
        }
    }
});

// Doughnut Chart
new Chart(document.getElementById('overallChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Purchase', 'Sales', 'Income', 'Expense'],
        datasets: [{
            data: [25, 35, 25, 15],
            backgroundColor: ['#64748b', '#003366', '#16a34a', '#dc3545'],
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(c) { return ' ' + c.label + ': ৳0'; }
                }
            }
        }
    }
});
</script>
@stop
