@extends('adminlte::page')

@section('title', 'New Purchase | PosSales')

@section('adminlte_css')
    {{-- Select2 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css">
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        /* ── CSS Variables ── */
        :root {
            --primary:        #3b82f6;
            --primary-dark:   #2563eb;
            --primary-light:  #eff6ff;
            --primary-ring:   rgba(59,130,246,.18);
            --success:        #10b981;
            --danger:         #ef4444;
            --warning:        #f59e0b;
            --text-dark:      #0f172a;
            --text-mid:       #475569;
            --text-soft:      #64748b;
            --border:         #e2e8f0;
            --bg-page:        #f1f5f9;
            --bg-card:        #ffffff;
            --radius:         12px;
            --radius-sm:      8px;
            --shadow-sm:      0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md:      0 4px 16px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.04);
            --shadow-card:    0 2px 8px rgba(0,0,0,.07), 0 0 0 1px var(--border);
        }

        .content-wrapper { background: var(--bg-page) !important; }

        /* ── Layout ── */
        .purchase-layout {
            display: flex; gap: 22px; align-items: flex-start;
        }
        .left-panel  { flex: 0 0 62%; min-width: 0; }
        .right-panel {
            flex: 0 0 36%; position: sticky; top: 15px;
            max-height: calc(100vh - 80px);
            display: flex; flex-direction: column; overflow: hidden;
        }

        /* ── Cards ── */
        .modern-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            padding: 24px 26px;
            border: none;
        }
        .section-title {
            font-size: 13px; font-weight: 700; letter-spacing: .4px;
            text-transform: uppercase; color: var(--text-mid);
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 13px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title i { color: var(--primary); font-size: 14px; }

        /* ── Form controls ── */
        .form-control {
            border-radius: var(--radius-sm) !important;
            border: 1.5px solid var(--border) !important;
            font-size: 13.5px !important;
            color: var(--text-dark) !important;
            transition: border-color .18s, box-shadow .18s;
            background: #fff !important;
        }
        .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px var(--primary-ring) !important;
        }
        label.small {
            font-size: 11.5px; font-weight: 700; color: var(--text-mid);
            margin-bottom: 5px; display: block; letter-spacing: .2px;
        }

        /* ── Financial Fields Row ── */
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 18px;
        }
        .fin-field label.small { color: var(--text-soft); }
        .fin-field .form-control { font-weight: 600; }

        /* ── Items table ── */
        .items-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .items-table thead tr { background: #f8fafc; }
        .items-table th {
            padding: 11px 14px; font-size: 11px; font-weight: 800;
            color: var(--text-soft); letter-spacing: .5px; text-transform: uppercase;
            border-bottom: 2px solid var(--border);
        }
        .items-table td {
            padding: 11px 14px; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle; font-size: 13.5px;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody tr:hover td { background: #fafbff; transition: background .15s; }

        .inline-input {
            width: 72px; border: 1.5px solid var(--border); border-radius: 7px;
            padding: 5px 6px; text-align: center; font-size: 13px;
            background: #fff; color: var(--text-dark);
            transition: border-color .15s, box-shadow .15s;
        }
        .inline-input:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }
        .inline-input-wide { width: 100px; }

        /* ── Totals box ── */
        .totals-box {
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
            border-radius: var(--radius-sm);
            padding: 18px 20px;
            border: 1.5px solid #dbeafe;
        }
        .totals-box table td { padding: 6px 8px; font-size: 13.5px; }
        .totals-box .grand-total td {
            font-size: 17px; font-weight: 800; color: var(--primary);
            border-top: 1.5px solid #dbeafe; padding-top: 10px;
        }

        /* ── Payment row ── */
        .payment-row { margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; }
        .payment-row .pay-group { flex: 1 1 140px; }

        .pay-full-btn {
            background: var(--success); color: #fff; border: none;
            border-radius: 6px; padding: 5px 12px; font-size: 11.5px;
            font-weight: 700; cursor: pointer; white-space: nowrap;
            transition: background .18s, transform .12s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .pay-full-btn:hover { background: #059669; transform: translateY(-1px); }

        /* ── Submit ── */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff; border: none;
            border-radius: var(--radius-sm); padding: 13px 24px;
            font-weight: 800; font-size: 14px; width: 100%; margin-top: 20px;
            transition: transform .15s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(59,130,246,.35);
            letter-spacing: .3px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59,130,246,.45);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit i { margin-right: 7px; }

        /* ── Cart clear btn ── */
        .btn-clear-cart {
            background: transparent; border: 1.5px solid #fca5a5;
            color: var(--danger); border-radius: 6px; padding: 4px 11px;
            font-size: 12px; font-weight: 700; cursor: pointer;
            transition: all .18s; display: none;
        }
        .btn-clear-cart:hover { background: #fef2f2; border-color: var(--danger); }

        /* ── Catalog / Right Panel ── */
        .catalog-search-row { display: flex; flex-direction: column; gap: 8px; padding: 4px 0 14px; }
        .catalog-filter-row { display: flex; gap: 8px; }
        .catalog-filter-row .form-control { font-size: 12px !important; }

        .prod-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(118px, 1fr));
            gap: 10px; overflow-y: auto; padding-right: 2px; flex: 1;
        }
        .prod-grid::-webkit-scrollbar { width: 4px; }
        .prod-grid::-webkit-scrollbar-track { background: transparent; }
        .prod-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .prod-card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 10px; padding: 11px 10px 10px; cursor: pointer;
            transition: border-color .18s, transform .18s, box-shadow .18s;
            position: relative; overflow: hidden;
        }
        .prod-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .prod-card.active {
            border-color: var(--primary);
            background: var(--primary-light);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }
        .prod-card-name {
            font-size: 11.5px; font-weight: 700; color: var(--text-dark);
            line-height: 1.35; height: 31px; overflow: hidden;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .prod-card-price { font-size: 12px; font-weight: 800; color: var(--primary); margin-top: 5px; }
        .prod-card-stock {
            font-size: 10.5px; margin-top: 4px;
            font-weight: 600; display: flex; align-items: center; gap: 3px;
        }
        .stock-dot {
            width: 6px; height: 6px; border-radius: 50%; display: inline-block; flex-shrink: 0;
        }
        .stock-dot.in  { background: var(--success); }
        .stock-dot.low { background: var(--warning); }
        .stock-dot.out { background: var(--danger); }
        .prod-card-badge {
            position: absolute; top: 6px; right: 6px;
            background: #dbeafe; color: var(--primary);
            font-size: 8.5px; font-weight: 800; border-radius: 4px;
            padding: 2px 5px; letter-spacing: .4px;
        }

        /* ── Quick-Add Overlay ── */
        #quick-add {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1.5px solid var(--border);
            border-radius: 0 0 var(--radius) var(--radius);
            padding: 18px 20px;
            box-shadow: 0 -8px 30px rgba(0,0,0,.1);
            display: none; z-index: 20;
        }
        #qa-header {
            display: flex; align-items: flex-start;
            justify-content: space-between; margin-bottom: 12px;
        }
        #qa-name {
            font-size: 14px; font-weight: 800; color: var(--text-dark);
            line-height: 1.3; flex: 1; padding-right: 10px;
        }
        .qa-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .qa-row .form-control { font-size: 13px !important; }
        .btn-qa-add {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff; border: none;
            border-radius: var(--radius-sm); width: 100%; padding: 10px;
            font-weight: 800; font-size: 13.5px; cursor: pointer;
            transition: transform .15s, box-shadow .18s;
            box-shadow: 0 3px 10px rgba(59,130,246,.3);
        }
        .btn-qa-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(59,130,246,.4);
        }
        .btn-qa-close {
            background: #f1f5f9; border: none; color: var(--text-soft);
            font-size: 17px; line-height: 1; padding: 4px 8px; cursor: pointer;
            border-radius: 6px; transition: background .15s; flex-shrink: 0;
        }
        .btn-qa-close:hover { background: #e2e8f0; color: var(--text-dark); }

        /* ── Input group ── */
        .input-group-text {
            border-radius: var(--radius-sm) 0 0 var(--radius-sm) !important;
            border: 1.5px solid var(--border) !important;
            border-right: none !important;
            background: #f8fafc; font-weight: 700;
            color: var(--text-mid); font-size: 13px;
        }
        .input-group .form-control:not(:first-child) {
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0 !important;
            border-left: none !important;
        }

        /* ── Select2 overrides ── */
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            border-radius: var(--radius-sm) !important;
            border: 1.5px solid var(--border) !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.25rem + 2px) !important;
            color: var(--text-dark) !important;
            font-size: 13.5px !important;
        }
        .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px var(--primary-ring) !important;
        }

        /* ── Divider ── */
        .section-divider {
            border: none; border-top: 1.5px dashed #e2e8f0; margin: 20px 0;
        }

        /* ── Responsive ── */
        @media (max-width: 1100px) {
            .purchase-layout { flex-direction: column; }
            .left-panel, .right-panel { width: 100%; flex: unset; position: static; max-height: none; }
            .prod-grid { max-height: 340px; }
            .financial-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .financial-grid { grid-template-columns: 1fr; }
        }
    </style>
@stop

@section('content')
<div class="pt-4 pb-5 px-2">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 font-weight-bold" style="color:var(--text-dark)">
            <i class="fas fa-cart-plus text-primary mr-2"></i> New Purchase Order
        </h4>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-lg">
            <i class="fas fa-list mr-1"></i> All Purchases
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchase-form" novalidate>
        @csrf
        <div class="purchase-layout">

            {{-- ═══════════════════════════════════════════
                 LEFT PANEL
            ═══════════════════════════════════════════ --}}
            <div class="left-panel">

                {{-- Basic Details --}}
                <div class="modern-card mb-4">
                    <div class="section-title">
                        <i class="fas fa-file-invoice"></i> Basic Details
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small">Supplier *</label>
                            <select name="supplier_id" id="supplier_id" class="form-control" required style="width:100%">
                                <option value="">Select Supplier</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small">Purchase Date *</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small">Invoice Number</label>
                            <input type="text" name="invoice_number" id="invoice_number"
                                   placeholder="Auto-generated" class="form-control"
                                   style="font-family: monospace; letter-spacing:.4px; font-weight:600">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="small">Notes / Remarks</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm"
                                      placeholder="Any remarks or notes about this purchase…"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Purchase Items --}}
                <div class="modern-card">
                    <div class="section-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-shopping-basket"></i> Purchase Items</span>
                        <button type="button" class="btn-clear-cart" id="clear-cart-btn">
                            <i class="fas fa-trash-alt mr-1"></i> Clear All
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product / Variation</th>
                                    <th class="text-center" style="width:90px">Qty</th>
                                    <th class="text-center" style="width:120px">Unit Price (৳)</th>
                                    <th class="text-right" style="width:120px">Total (৳)</th>
                                    <th style="width:44px"></th>
                                </tr>
                            </thead>
                            <tbody id="purchase-items-body">
                                <tr id="empty-row">
                                    <td colspan="5" class="text-center py-5" style="color:var(--text-soft)">
                                        <i class="fas fa-box-open d-block mb-2" style="font-size:26px;opacity:.3"></i>
                                        <span style="font-size:13px">Select products from the catalog →</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr class="section-divider">

                    {{-- Totals --}}
                    <div class="row">
                        <div class="col-md-7 ml-auto">
                            <div class="totals-box">
                                <table class="w-100">
                                    <tr>
                                        <td class="text-muted" style="font-size:13px">Subtotal</td>
                                        <td class="text-right font-weight-bold">
                                            ৳<span id="subtotal-display">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted" style="font-size:13px">Discount</td>
                                        <td class="text-right" style="font-size:13px">
                                            - ৳<span id="discount-display">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted" style="font-size:13px">Tax</td>
                                        <td class="text-right" style="font-size:13px">
                                            + ৳<span id="tax-display">0.00</span>
                                        </td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Total Amount</td>
                                        <td class="text-right">৳<span id="total-display">0.00</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Adjustment Fields --}}
                    <div class="financial-grid">
                        <div class="fin-field">
                            <label class="small">Discount Amount (৳)</label>
                            <input type="number" name="discount_amount" id="discount_amount"
                                   class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="fin-field">
                            <label class="small">Tax Amount (৳)</label>
                            <input type="number" name="tax_amount" id="tax_amount"
                                   class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="fin-field">
                            <label class="small">Paid Amount (৳) *</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="paid_amount" id="paid_amount"
                                       class="form-control font-weight-bold"
                                       style="color:var(--success)"
                                       value="0" step="0.01" min="0">
                            </div>
                            <button type="button" class="pay-full-btn mt-1" id="pay-full-btn">
                                <i class="fas fa-check-circle"></i> Pay Full
                            </button>
                        </div>
                        <div class="fin-field">
                            <label class="small">Due Amount (৳)</label>
                            <input type="number" id="due_display" class="form-control bg-light"
                                   readonly value="0"
                                   style="font-weight:700;color:var(--danger)">
                        </div>
                        <div class="fin-field">
                            <label class="small">Change Amount (৳)</label>
                            <input type="number" id="change_display" class="form-control bg-light"
                                   readonly value="0"
                                   style="font-weight:700;color:var(--success)">
                        </div>
                        <div class="fin-field">
                            <label class="small">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile Banking</option>
                            </select>
                        </div>
                    </div>

                    {{-- Hidden Inputs mapped to DB columns --}}
                    <input type="hidden" name="subtotal"         id="subtotal-input"       value="0">
                    <input type="hidden" name="total_amount"     id="total-input"          value="0">
                    <input type="hidden" name="due_amount"       id="due-input"            value="0">
                    <input type="hidden" name="change_amount"    id="change-input"         value="0">
                    <input type="hidden" name="purchase_items"   id="items-json-input">

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> COMPLETE PURCHASE
                    </button>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════
                 RIGHT PANEL – CATALOG
            ═══════════════════════════════════════════ --}}
            <div class="right-panel modern-card">
                <div class="section-title mb-0 pb-3" style="border-bottom:1.5px solid #f1f5f9">
                    <i class="fas fa-th-large"></i> Product Catalog
                </div>

                <div class="catalog-search-row">
                    <input type="text" id="catalog-search" class="form-control form-control-sm"
                           placeholder="&#xf002;  Search by name or code…"
                           style="font-family:'FontAwesome', sans-serif">
                    <div class="catalog-filter-row">
                        <select id="cat-filter" class="form-control form-control-sm" onchange="applyFilters()">
                            <option value="">All Categories</option>
                        </select>
                        <select id="brand-filter" class="form-control form-control-sm" onchange="applyFilters()">
                            <option value="">All Brands</option>
                        </select>
                    </div>
                </div>

                <div id="product-grid" class="prod-grid">
                    <div class="text-center w-100 pt-4 text-muted" style="grid-column:1/-1;font-size:13px">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading products…
                    </div>
                </div>

                {{-- Quick-Add Overlay --}}
                <div id="quick-add">
                    <div id="qa-header">
                        <span id="qa-name">Product Name</span>
                        <button type="button" class="btn-qa-close" id="qa-close">&times;</button>
                    </div>
                    <div id="qa-variation-box" style="display:none; margin-bottom:10px">
                        <label class="small">Variation</label>
                        <select id="qa-variation" class="form-control form-control-sm"></select>
                    </div>
                    <div class="qa-row">
                        <div style="flex:1">
                            <label class="small">Quantity</label>
                            <input type="number" id="qa-qty" class="form-control form-control-sm"
                                   value="1" min="0.01" step="any">
                        </div>
                        <div style="flex:1.5">
                            <label class="small">Unit Price (৳)</label>
                            <input type="number" id="qa-price" class="form-control form-control-sm"
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                    <button type="button" id="qa-add-btn" class="btn-qa-add">
                        <i class="fas fa-plus mr-1"></i> Add to Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('adminlte_js')
    {{-- Select2 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    {{-- Toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
(function () {
    'use strict';

    /* ── State ── */
    let allProducts = [];
    let cart        = [];

    /* ── Toastr defaults ── */
    toastr.options = {
        positionClass:   'toast-top-right',
        timeOut:         2500,
        closeButton:     true,
        progressBar:     true,
        newestOnTop:     true,
    };

    /* ══════════════════════════════════════════
       INIT
    ══════════════════════════════════════════ */
    $(document).ready(function () {

        /* Auto-generate Invoice Number: PUR-YYYYMMDD-RANDOM */
        generateInvoiceNumber();

        /* Select2 Supplier */
        $('#supplier_id').select2({
            theme:       'bootstrap4',
            placeholder: 'Search supplier…',
            allowClear:  true,
            ajax: {
                url:            '/admin/suppliers/search',
                dataType:       'json',
                delay:          250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(s => ({ id: s.id, text: s.name || s.display_text }))
                }),
                cache: true
            }
        });

        /* Load Products */
        fetch('/admin/products/pos-search?search=')
            .then(r => r.json())
            .then(data => {
                allProducts = data.data || data;
                renderGrid(allProducts);
                populateFilters();
            })
            .catch(() => {
                $('#product-grid').html(
                    '<div class="text-danger text-center w-100 pt-4" style="grid-column:1/-1;font-size:13px">' +
                    '<i class="fas fa-exclamation-circle mr-1"></i> Failed to load products</div>'
                );
            });

        /* Search input */
        $('#catalog-search').on('input', debounce(applyFilters, 200));

        /* Quick-add close */
        $('#qa-close').on('click', () => slideUp('#quick-add'));

        /* Pay Full button */
        $('#pay-full-btn').on('click', function () {
            const total = parseFloat($('#total-input').val()) || 0;
            $('#paid_amount').val(total.toFixed(2));
            updateTotals();
        });

        /* Live recalculate on any financial field change */
        $('#paid_amount, #discount_amount, #tax_amount').on('input', updateTotals);

        /* Clear cart */
        $('#clear-cart-btn').on('click', function () {
            if (!cart.length) return;
            if (confirm('Clear all items from the cart?')) {
                cart = [];
                renderCart();
            }
        });

        /* Form submit validation */
        $('#purchase-form').on('submit', function (e) {
            if (cart.length === 0) {
                e.preventDefault();
                toastr.error('Please add at least one product before submitting.', 'Cart is Empty');
                return false;
            }
            if (!$('#supplier_id').val()) {
                e.preventDefault();
                toastr.error('Please select a supplier.', 'Supplier Required');
                $('#supplier_id').next('.select2-container').find('.select2-selection').css('border-color','var(--danger)');
                return false;
            }
            /* Sync hidden inputs one final time */
            syncHiddenInputs();
        });
    });

    /* ══════════════════════════════════════════
       AUTO-INVOICE NUMBER
    ══════════════════════════════════════════ */
    function generateInvoiceNumber() {
        const now   = new Date();
        const yyyy  = now.getFullYear();
        const mm    = String(now.getMonth() + 1).padStart(2, '0');
        const dd    = String(now.getDate()).padStart(2, '0');
        const rand  = Math.random().toString(36).substring(2, 7).toUpperCase();
        const inv   = `PUR-${yyyy}${mm}${dd}-${rand}`;
        $('#invoice_number').val(inv);
    }

    /* ══════════════════════════════════════════
       CATALOG
    ══════════════════════════════════════════ */
    function renderGrid(products) {
        const grid = $('#product-grid').empty();
        if (!products.length) {
            grid.html('<div class="text-muted text-center w-100 pt-4" style="grid-column:1/-1;font-size:13px">No products found.</div>');
            return;
        }
        products.forEach(p => {
            const stock    = p.stock !== undefined ? parseInt(p.stock) : null;
            const stockHtml = buildStockHtml(stock, p.variations);
            const varBadge  = (p.variations && p.variations.length > 0)
                ? '<span class="prod-card-badge">VAR</span>' : '';

            grid.append(
                `<div class="prod-card" data-id="${p.id}" onclick='window.openQuickAdd(${JSON.stringify(p)})'>
                    ${varBadge}
                    <div class="prod-card-name">${escHtml(p.name)}</div>
                    <div class="prod-card-price">৳${parseFloat(p.purchase_price || p.price || 0).toFixed(2)}</div>
                    ${stockHtml}
                </div>`
            );
        });
    }

    function buildStockHtml(stock, variations) {
        if (variations && variations.length > 0) {
            const total = variations.reduce((s, v) => s + (parseInt(v.stock) || 0), 0);
            return stockBadge(total);
        }
        if (stock === null) return '';
        return stockBadge(stock);
    }

    function stockBadge(qty) {
        if (qty > 10) return `<div class="prod-card-stock"><span class="stock-dot in"></span><span style="color:var(--success)">${qty} in stock</span></div>`;
        if (qty > 0)  return `<div class="prod-card-stock"><span class="stock-dot low"></span><span style="color:var(--warning)">Low (${qty})</span></div>`;
        return `<div class="prod-card-stock"><span class="stock-dot out"></span><span style="color:var(--danger)">Out of stock</span></div>`;
    }

    function populateFilters() {
        const cats   = [...new Set(allProducts.map(p => p.category?.name || p.category).filter(Boolean))];
        const brands = [...new Set(allProducts.map(p => p.brand?.name   || p.brand  ).filter(Boolean))];
        cats.forEach  (c => $('#cat-filter').append(`<option value="${c}">${escHtml(c)}</option>`));
        brands.forEach(b => $('#brand-filter').append(`<option value="${b}">${escHtml(b)}</option>`));
    }

    window.applyFilters = function () {
        const term  = $('#catalog-search').val().toLowerCase().trim();
        const cat   = $('#cat-filter').val();
        const brand = $('#brand-filter').val();

        const filtered = allProducts.filter(p => {
            const matchSearch = !term ||
                p.name.toLowerCase().includes(term) ||
                (p.code && p.code.toLowerCase().includes(term));
            const matchCat   = !cat   || (p.category?.name === cat   || p.category === cat);
            const matchBrand = !brand || (p.brand?.name    === brand || p.brand    === brand);
            return matchSearch && matchCat && matchBrand;
        });
        renderGrid(filtered);
    };

    /* ══════════════════════════════════════════
       QUICK-ADD
    ══════════════════════════════════════════ */
    window.openQuickAdd = function (p) {
        /* Highlight selected card */
        $('.prod-card').removeClass('active');
        $(`.prod-card[data-id="${p.id}"]`).addClass('active');

        $('#qa-name').text(p.name);
        $('#qa-price').val(parseFloat(p.purchase_price || p.price || 0).toFixed(2));
        $('#qa-qty').val(1);

        const varBox    = $('#qa-variation-box').hide();
        const varSelect = $('#qa-variation').empty();

        if (p.variations && p.variations.length > 0) {
            p.variations.forEach(v => {
                varSelect.append(
                    `<option value="${v.id}" data-val="${escHtml(v.variation_value)}">
                        ${escHtml(v.variation_value)} (Stock: ${v.stock ?? '?'})
                    </option>`
                );
            });
            varBox.slideDown(200);
        }

        $('#qa-add-btn').off('click').on('click', () => addToCart(p));

        const overlay = document.getElementById('quick-add');
        if (overlay.style.display === 'none' || overlay.style.display === '') {
            $(overlay).slideDown(220);
        }
    };

    function addToCart(p) {
        const hasVar  = $('#qa-variation-box').is(':visible');
        const varId   = hasVar ? $('#qa-variation').val()                          : null;
        const varName = hasVar ? $('#qa-variation option:selected').data('val')    : '';
        const qty     = parseFloat($('#qa-qty').val())   || 1;
        const price   = parseFloat($('#qa-price').val()) || 0;

        if (qty <= 0) {
            toastr.warning('Please enter a valid quantity (greater than 0).', 'Invalid Qty');
            return;
        }
        if (price < 0) {
            toastr.warning('Price cannot be negative.', 'Invalid Price');
            return;
        }

        /* cartKey ensures product+variation uniqueness */
        const cartKey     = varId ? `${p.id}_${varId}` : String(p.id);
        const existingIdx = cart.findIndex(i => i.cartKey === cartKey);

        if (existingIdx > -1) {
            cart[existingIdx].qty   += qty;
            cart[existingIdx].total  = +(cart[existingIdx].qty * cart[existingIdx].price).toFixed(4);
        } else {
            cart.push({
                cartKey,
                product_id:   p.id,                                     /* required key */
                variation_id: varId ? parseInt(varId) : null,           /* required key */
                name:         p.name + (varName ? ` — ${varName}` : ''),
                qty:          qty,                                       /* required key */
                price:        price,
                total:        +(qty * price).toFixed(4)                 /* required key */
            });
        }

        renderCart();
        slideUp('#quick-add');
        $('.prod-card').removeClass('active');
        toastr.success(`<b>${escHtml(p.name)}</b> added to order.`, 'Added', { timeOut: 1600 });
    }

    /* ══════════════════════════════════════════
       CART
    ══════════════════════════════════════════ */
    function renderCart() {
        const body = $('#purchase-items-body').empty();

        if (cart.length === 0) {
            $('#clear-cart-btn').hide();
            body.html(
                '<tr id="empty-row"><td colspan="5" class="text-center py-5" style="color:var(--text-soft)">' +
                '<i class="fas fa-box-open d-block mb-2" style="font-size:26px;opacity:.3"></i>' +
                '<span style="font-size:13px">Select products from the catalog →</span></td></tr>'
            );
            updateTotals();
            return;
        }

        $('#clear-cart-btn').show();

        cart.forEach((item, idx) => {
            body.append(`
                <tr>
                    <td>
                        <div class="font-weight-bold" style="font-size:13.5px">${escHtml(item.name)}</div>
                    </td>
                    <td class="text-center">
                        <input type="number" class="inline-input"
                               value="${item.qty}" min="0.01" step="any"
                               onchange="window.updateQty(${idx}, this.value)">
                    </td>
                    <td class="text-center">
                        <input type="number" class="inline-input inline-input-wide"
                               value="${item.price}" min="0" step="0.01"
                               onchange="window.updatePrice(${idx}, this.value)">
                    </td>
                    <td class="text-right font-weight-bold" style="color:var(--text-dark)">
                        ৳${item.total.toFixed(2)}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm"
                                style="color:var(--danger);padding:3px 8px"
                                onclick="window.removeItem(${idx})" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        updateTotals();
    }

    window.updateQty = function (idx, val) {
        const q = parseFloat(val);
        if (isNaN(q) || q <= 0) return;
        cart[idx].qty   = q;
        cart[idx].total = +(q * cart[idx].price).toFixed(4);
        renderCart();
    };

    window.updatePrice = function (idx, val) {
        const p = parseFloat(val);
        if (isNaN(p) || p < 0) return;
        cart[idx].price = p;
        cart[idx].total = +(cart[idx].qty * p).toFixed(4);
        renderCart();
    };

    window.removeItem = function (idx) {
        cart.splice(idx, 1);
        renderCart();
    };

    /* ══════════════════════════════════════════
       TOTALS  (maps to all DB columns)
       subtotal, tax_amount, discount_amount,
       total_amount, paid_amount, due_amount, change_amount
    ══════════════════════════════════════════ */
    function updateTotals() {
        const subtotal  = cart.reduce((sum, i) => sum + i.total, 0);
        const discount  = Math.max(0, parseFloat($('#discount_amount').val()) || 0);
        const tax       = Math.max(0, parseFloat($('#tax_amount').val())      || 0);
        const total     = Math.max(0, subtotal - discount + tax);
        const paid      = Math.max(0, parseFloat($('#paid_amount').val())     || 0);
        const due       = Math.max(0, total - paid);
        const change    = paid > total ? +(paid - total).toFixed(2) : 0;

        /* Display */
        $('#subtotal-display').text(subtotal.toFixed(2));
        $('#discount-display').text(discount.toFixed(2));
        $('#tax-display').text(tax.toFixed(2));
        $('#total-display').text(total.toFixed(2));

        /* Read-only output fields */
        $('#due_display').val(due.toFixed(2)).css('color', due > 0 ? 'var(--danger)' : 'var(--success)');
        $('#change_display').val(change.toFixed(2)).css('color', change > 0 ? 'var(--success)' : 'var(--text-soft)');

        syncHiddenInputs(subtotal, total, due, change, discount, tax);
    }

    function syncHiddenInputs(subtotal, total, due, change, discount, tax) {
        /* Recalculate if called without args (e.g. on renderCart) */
        if (subtotal === undefined) {
            subtotal = cart.reduce((sum, i) => sum + i.total, 0);
            discount = Math.max(0, parseFloat($('#discount_amount').val()) || 0);
            tax      = Math.max(0, parseFloat($('#tax_amount').val())      || 0);
            total    = Math.max(0, subtotal - discount + tax);
            const paid = Math.max(0, parseFloat($('#paid_amount').val()) || 0);
            due      = Math.max(0, total - paid);
            change   = paid > total ? +(paid - total).toFixed(2) : 0;
        }

        $('#subtotal-input').val(subtotal.toFixed(2));
        $('#total-input').val(total.toFixed(2));
        $('#due-input').val(due.toFixed(2));
        $('#change-input').val(change.toFixed(2));

        /* Serialize cart: only the 4 required keys go in the JSON */
        const payload = cart.map(i => ({
            product_id:   i.product_id,
            variation_id: i.variation_id,
			unit_price:   i.price,
            qty:          i.qty,
            total:        i.total,
        }));
        $('#items-json-input').val(JSON.stringify(payload));
    }

    /* ══════════════════════════════════════════
       HELPERS
    ══════════════════════════════════════════ */
    function slideUp(sel)  { $(sel).slideUp(200); }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function debounce(fn, delay) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

})();
</script>
@stop