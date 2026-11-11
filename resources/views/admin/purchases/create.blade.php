@extends('adminlte::page')

@section('title', 'New Purchase | BongoBay')

@section('content_header')
<div class="purchase-hero">
    <div class="purchase-hero-info">
        <span class="purchase-hero-eyebrow"><i class="fas fa-circle"></i> Inventory &middot; Purchase Entry</span>
        <h1 class="purchase-hero-title">Create Purchase Order</h1>
        <p class="purchase-hero-sub">Add stock from your supplier &mdash; pick products from the catalog, set prices, then complete the purchase.</p>
    </div>
    <div class="purchase-hero-actions">
        <a href="{{ route('purchases.index') }}" class="purchase-hero-btn purchase-hero-btn-ghost"><i class="fas fa-list"></i> All Purchases</a>
        <a href="{{ route('products.create') }}" class="purchase-hero-btn purchase-hero-btn-white"><i class="fas fa-plus"></i> Add Product</a>
    </div>
</div>
@stop

@section('content')
<div class="purchase-wrap">

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-1 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <form action="{{ route('purchases.store') }}" method="POST" id="purchase-form" novalidate>
        @csrf
        <div class="purchase-layout">

            {{-- LEFT --}}
            <div class="purchase-main">

                {{-- Basic Details --}}
                <div class="premium-card mb-4">
                    <div class="premium-card-head">
                        <span class="premium-head-icon"><i class="fas fa-file-invoice"></i></span>
                        <div>
                            <h3>Basic Details</h3>
                            <p>Who supplied this stock and when?</p>
                        </div>
                        <span class="premium-step">Step 1</span>
                    </div>
                    <div class="premium-card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="premium-label">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-control premium-select" required style="width:100%">
                                    <option value="">Select Supplier</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="premium-label">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" class="form-control premium-input" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="premium-label">Invoice Number</label>
                                <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number') }}"
                                       placeholder="Auto-generated" class="form-control premium-input premium-mono">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-0">
                                <label class="premium-label">Notes / Remarks</label>
                                <textarea name="notes" rows="2" class="form-control premium-input"
                                          placeholder="Any remarks about this purchase…">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="premium-card">
                    <div class="premium-card-head">
                        <span class="premium-head-icon premium-head-icon-green"><i class="fas fa-shopping-basket"></i></span>
                        <div>
                            <h3>Purchase Items</h3>
                            <p id="items-summary">No items yet &mdash; pick products from the catalog.</p>
                        </div>
                        <button type="button" class="premium-clear-btn" id="clear-cart-btn" style="display:none;">
                            <i class="fas fa-trash-alt mr-1"></i> Clear All
                        </button>
                    </div>
                    <div class="premium-card-body">
                        <div class="table-responsive">
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>Product / Variation</th>
                                        <th class="text-center" style="width:90px">Qty</th>
                                        <th class="text-center" style="width:130px">Unit Price (৳)</th>
                                        <th class="text-right" style="width:120px">Total (৳)</th>
                                        <th style="width:44px"></th>
                                    </tr>
                                </thead>
                                <tbody id="purchase-items-body">
                                    <tr id="empty-row">
                                        <td colspan="5" class="text-center" style="padding:38px 16px;">
                                            <i class="fas fa-box-open d-block mb-2" style="font-size:30px;opacity:.25"></i>
                                            <span style="font-size:13px;color:#94a3b8">Select products from the catalog &rarr;</span>
                                            <small class="d-block mt-1" style="color:#cbd5e1">Click any product card to add it to this order.</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="premium-divider"></div>

                        <div class="row align-items-start">
                            <div class="col-md-5">
                                <div class="premium-totals-box">
                                    <table class="w-100">
                                        <tr>
                                            <td class="text-muted" style="font-size:13px">Subtotal</td>
                                            <td class="text-right font-weight-bold">৳<span id="subtotal-display">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="font-size:13px">Discount</td>
                                            <td class="text-right" style="font-size:13px">&minus; ৳<span id="discount-display">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="font-size:13px">Tax</td>
                                            <td class="text-right" style="font-size:13px">+ ৳<span id="tax-display">0.00</span></td>
                                        </tr>
                                        <tr class="grand-total">
                                            <td>Total Amount</td>
                                            <td class="text-right">৳<span id="total-display">0.00</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="premium-pay-hint">
                                    <i class="fas fa-lightbulb mr-1"></i> Due &amp; change update automatically as you pay.
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="premium-fin-grid">
                                    <div class="premium-fin">
                                        <label class="premium-label">Discount (৳)</label>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control premium-input" value="{{ old('discount_amount', 0) }}" min="0" step="0.01">
                                    </div>
                                    <div class="premium-fin">
                                        <label class="premium-label">Tax (৳)</label>
                                        <input type="number" name="tax_amount" id="tax_amount" class="form-control premium-input" value="{{ old('tax_amount', 0) }}" min="0" step="0.01">
                                    </div>
                                    <div class="premium-fin">
                                        <label class="premium-label">Paid Amount (৳) <span class="text-danger">*</span></label>
                                        <input type="number" name="paid_amount" id="paid_amount" class="form-control premium-input premium-input-paid" value="{{ old('paid_amount', 0) }}" step="0.01" min="0">
                                        <button type="button" class="premium-pay-full" id="pay-full-btn"><i class="fas fa-check-circle"></i> Pay Full</button>
                                    </div>
                                    <div class="premium-fin">
                                        <label class="premium-label">Due (৳)</label>
                                        <input type="text" id="due_display" class="form-control premium-input premium-readonly" readonly value="0.00">
                                    </div>
                                    <div class="premium-fin">
                                        <label class="premium-label">Change (৳)</label>
                                        <input type="text" id="change_display" class="form-control premium-input premium-readonly" readonly value="0.00">
                                    </div>
                                    <div class="premium-fin">
                                        <label class="premium-label">Payment Method</label>
                                        <select name="payment_method" class="form-control premium-input">
                                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                            <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="subtotal" id="subtotal-input" value="0">
                        <input type="hidden" name="total_amount" id="total-input" value="0">
                        <input type="hidden" name="due_amount" id="due-input" value="0">
                        <input type="hidden" name="change_amount" id="change-input" value="0">
                        <input type="hidden" name="purchase_items" id="items-json-input">

                        <button type="submit" class="premium-submit">
                            <i class="fas fa-save"></i> Complete Purchase
                        </button>
                        <p class="premium-submit-hint">Stock will be added to inventory immediately after saving.</p>
                    </div>
                </div>
            </div>

            {{-- RIGHT — CATALOG --}}
            <div class="purchase-catalog premium-card">
                <div class="premium-catalog-head">
                    <span class="premium-head-icon"><i class="fas fa-th-large"></i></span>
                    <div>
                        <h3>Product Catalog</h3>
                        <p id="catalog-count">Loading products…</p>
                    </div>
                </div>
                <div class="premium-catalog-tools">
                    <div class="premium-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="catalog-search" class="form-control premium-input premium-search" placeholder="Search by name or code…">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <select id="cat-filter" class="form-control premium-input premium-select-sm" onchange="applyFilters()">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <select id="brand-filter" class="form-control premium-input premium-select-sm" onchange="applyFilters()">
                                <option value="">All Brands</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="product-grid" class="premium-grid">
                    <div class="premium-loading" style="grid-column:1/-1;">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading products…
                    </div>
                </div>

                <div id="quick-add">
                    <div class="premium-qa-head">
                        <span id="qa-name" class="premium-qa-name">Product Name</span>
                        <button type="button" class="premium-qa-close" id="qa-close">&times;</button>
                    </div>
                    <div id="qa-variation-box" style="display:none; margin-bottom:10px">
                        <label class="premium-label">Variation</label>
                        <select id="qa-variation" class="form-control premium-input"></select>
                    </div>
                    <div class="row">
                        <div class="col-5">
                            <label class="premium-label">Quantity</label>
                            <input type="number" id="qa-qty" class="form-control premium-input" value="1" min="0.01" step="any">
                        </div>
                        <div class="col-7">
                            <label class="premium-label">Unit Price (৳)</label>
                            <input type="number" id="qa-price" class="form-control premium-input" placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                    <button type="button" id="qa-add-btn" class="premium-qa-add">
                        <i class="fas fa-plus mr-1"></i> Add to Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('adminlte_css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --p-bg: #f1f4f9;
        --p-card: #ffffff;
        --p-border: #e8edf3;
        --p-text: #0f172a;
        --p-muted: #64748b;
        --p-soft: #94a3b8;
        --p-blue: #2563eb;
        --p-blue-dark: #1d4ed8;
        --p-green: #16a34a;
        --p-amber: #d97706;
        --p-rose: #e11d48;
        --p-radius: 16px;
        --p-radius-sm: 10px;
        --p-shadow: 0 1px 4px rgba(15,23,42,.05), 0 0 0 1px var(--p-border);
        --p-shadow-lg: 0 10px 30px rgba(15,23,42,.12);
    }
    body, .content-wrapper { background: var(--p-bg) !important; font-family: 'Inter', sans-serif !important; }

    /* Hero */
    .purchase-hero {
        position: relative; overflow: hidden;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #1d4ed8 100%);
        border-radius: 16px; padding: 22px 26px; margin-bottom: 18px;
        box-shadow: 0 8px 24px rgba(15,23,42,.18);
    }
    .purchase-hero::before, .purchase-hero::after { content: ''; position: absolute; border-radius: 50%; }
    .purchase-hero::before { width: 260px; height: 260px; right: -70px; top: -120px; background: radial-gradient(circle, rgba(255,255,255,.14) 0%, transparent 70%); }
    .purchase-hero::after { width: 180px; height: 180px; right: 120px; bottom: -110px; background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%); }
    .purchase-hero-info { position: relative; z-index: 1; }
    .purchase-hero-eyebrow { display: inline-flex; align-items: center; gap: 7px; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #93c5fd; }
    .purchase-hero-eyebrow i { font-size: .45rem; }
    .purchase-hero-title { margin: 6px 0 4px; font-weight: 800; font-size: 1.55rem; color: #fff; letter-spacing: -.3px; }
    .purchase-hero-sub { margin: 0; color: #cbd5e1; font-size: .85rem; }
    .purchase-hero-actions { position: relative; z-index: 1; display: flex; gap: 10px; flex-wrap: wrap; }
    .purchase-hero-btn { display: inline-flex; align-items: center; gap: 7px; font-size: .82rem; font-weight: 600; padding: 9px 16px; border-radius: 10px; text-decoration: none !important; transition: all .15s; border: 1px solid transparent; }
    .purchase-hero-btn-white { background: #fff; color: #0f172a !important; }
    .purchase-hero-btn-white:hover { background: #eff6ff; transform: translateY(-1px); }
    .purchase-hero-btn-ghost { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.25); color: #fff !important; }
    .purchase-hero-btn-ghost:hover { background: rgba(255,255,255,.2); color: #fff !important; }

    .purchase-wrap { padding-bottom: 40px; }
    .purchase-layout { display: flex; gap: 20px; align-items: flex-start; }
    .purchase-main { flex: 1 1 auto; min-width: 0; }
    .purchase-catalog {
        flex: 0 0 360px; position: sticky; top: 14px;
        max-height: calc(100vh - 28px); display: flex; flex-direction: column; overflow: hidden;
    }

    /* Cards */
    .premium-card {
        background: var(--p-card); border-radius: var(--p-radius);
        box-shadow: var(--p-shadow); border: 1px solid var(--p-border);
        overflow: hidden;
    }
    .premium-card-head {
        display: flex; align-items: center; gap: 14px;
        padding: 18px 22px; border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(180deg, #fff, #fbfdff);
    }
    .premium-card-head h3 { margin: 0; font-size: .95rem; font-weight: 800; color: var(--p-text); }
    .premium-card-head p { margin: 2px 0 0; font-size: .78rem; color: var(--p-muted); }
    .premium-head-icon {
        width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #dbeafe, #eff6ff); color: var(--p-blue); font-size: 1rem;
        border: 1px solid #bfdbfe;
    }
    .premium-head-icon-green { background: linear-gradient(135deg, #dcfce7, #f0fdf4); color: var(--p-green); border-color: #bbf7d0; }
    .premium-step {
        margin-left: auto; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        color: var(--p-blue); background: #eff6ff; border: 1px solid #bfdbfe;
        padding: 5px 11px; border-radius: 99px;
    }
    .premium-card-body { padding: 20px 22px; }

    .premium-label { font-size: .72rem; font-weight: 700; color: var(--p-muted); letter-spacing: .04em; text-transform: uppercase; margin-bottom: 6px; display: block; }
    .premium-input {
        border-radius: var(--p-radius-sm) !important; border: 1.5px solid var(--p-border) !important;
        font-size: .875rem !important; color: var(--p-text) !important; background: #fff !important;
        transition: border-color .18s, box-shadow .18s !important;
    }
    .premium-input:focus { border-color: var(--p-blue) !important; box-shadow: 0 0 0 4px rgba(37,99,235,.12) !important; }
    .premium-input-paid { font-weight: 700; color: var(--p-green) !important; }
    .premium-readonly { background: #f8fafc !important; font-weight: 700; }
    .premium-mono { font-family: ui-monospace, monospace; letter-spacing: .4px; font-weight: 600; }
    .premium-select-sm { font-size: .8rem !important; }
    .premium-select:focus, .select2-container--bootstrap4.select2-container--focus .select2-selection--single { border-color: var(--p-blue) !important; box-shadow: 0 0 0 4px rgba(37,99,235,.12) !important; }

    /* Table */
    .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .premium-table thead tr { background: #f8fafc; }
    .premium-table th {
        padding: 11px 14px; font-size: .68rem; font-weight: 800; color: var(--p-muted);
        letter-spacing: .06em; text-transform: uppercase; border-bottom: 2px solid var(--p-border);
        white-space: nowrap;
    }
    .premium-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: .875rem; }
    .premium-table tbody tr:last-child td { border-bottom: none; }
    .premium-table tbody tr:hover td { background: #fafbff; transition: background .15s; }
    .premium-inline { width: 74px; border: 1.5px solid var(--p-border); border-radius: 8px; padding: 6px; text-align: center; font-size: .875rem; background: #fff; color: var(--p-text); transition: border-color .15s, box-shadow .15s; }
    .premium-inline:focus { outline: none; border-color: var(--p-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .premium-inline-wide { width: 110px; }

    .premium-divider { border: none; border-top: 1.5px dashed #e2e8f0; margin: 20px 0; }

    .premium-totals-box {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border-radius: 14px; padding: 16px 18px; border: 1.5px solid #dbeafe;
    }
    .premium-totals-box table td { padding: 6px 6px; font-size: .875rem; }
    .premium-totals-box .grand-total td { font-size: 1.05rem; font-weight: 800; color: var(--p-blue); border-top: 1.5px solid #dbeafe; padding-top: 10px; }
    .premium-pay-hint { font-size: .75rem; color: var(--p-soft); margin-top: 10px; }

    .premium-fin-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .premium-pay-full {
        margin-top: 6px; background: var(--p-green); color: #fff; border: none;
        border-radius: 8px; padding: 5px 12px; font-size: .72rem; font-weight: 700; cursor: pointer;
        transition: all .15s; display: inline-flex; align-items: center; gap: 4px;
    }
    .premium-pay-full:hover { background: #15803d; transform: translateY(-1px); }

    .premium-clear-btn {
        margin-left: auto; background: transparent; border: 1px solid #fecaca; color: var(--p-rose);
        border-radius: 8px; padding: 6px 12px; font-size: .75rem; font-weight: 700; cursor: pointer; transition: all .15s;
    }
    .premium-clear-btn:hover { background: #fef2f2; border-color: var(--p-rose); }

    .premium-submit {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, var(--p-blue) 0%, var(--p-blue-dark) 100%);
        color: #fff; border: none; border-radius: var(--p-radius-sm);
        padding: 14px 24px; font-weight: 800; font-size: .9rem; width: 100%; margin-top: 20px;
        transition: transform .15s, box-shadow .2s; box-shadow: 0 6px 18px rgba(37,99,235,.35);
        letter-spacing: .02em;
    }
    .premium-submit::after {
        content: ''; position: absolute; top: 0; left: -80%; width: 50%; height: 100%;
        background: linear-gradient(100deg, transparent, rgba(255,255,255,.25), transparent);
        transform: skewX(-20deg); transition: left .5s;
    }
    .premium-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(37,99,235,.45); }
    .premium-submit:hover::after { left: 130%; }
    .premium-submit i { margin-right: 7px; }
    .premium-submit-hint { text-align: center; font-size: .75rem; color: var(--p-soft); margin-top: 8px; }

    /* Catalog */
    .premium-catalog-head { display: flex; align-items: center; gap: 12px; padding: 16px 18px 12px; border-bottom: 1px solid #f1f5f9; }
    .premium-catalog-head h3 { margin: 0; font-size: .92rem; font-weight: 800; color: var(--p-text); }
    .premium-catalog-head p { margin: 2px 0 0; font-size: .75rem; color: var(--p-muted); }
    .premium-catalog-tools { padding: 12px 16px 12px; display: flex; flex-direction: column; gap: 8px; }
    .premium-search-wrap { position: relative; }
    .premium-search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--p-soft); font-size: .8rem; }
    .premium-search { padding-left: 34px !important; font-size: .82rem !important; }
    .premium-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;
        overflow-y: auto; padding: 4px 16px 16px; flex: 1;
    }
    .premium-grid::-webkit-scrollbar { width: 5px; }
    .premium-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    .premium-loading { text-align: center; padding: 26px 10px; font-size: .8rem; color: var(--p-muted); }
    .premium-card-item {
        background: #fff; border: 1.5px solid var(--p-border); border-radius: 12px;
        padding: 12px; cursor: pointer; position: relative; overflow: hidden;
        transition: border-color .18s, transform .18s, box-shadow .18s;
    }
    .premium-card-item:hover { border-color: var(--p-blue); transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15,23,42,.1); }
    .premium-card-item.active { border-color: var(--p-blue); background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,.14); }
    .premium-card-name { font-size: .75rem; font-weight: 700; color: var(--p-text); line-height: 1.35; min-height: 32px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .premium-card-price { font-size: .8rem; font-weight: 800; color: var(--p-blue); margin-top: 6px; }
    .premium-card-stock { font-size: .68rem; margin-top: 5px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
    .premium-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .premium-dot.in { background: var(--p-green); }
    .premium-dot.low { background: var(--p-amber); }
    .premium-dot.out { background: var(--p-rose); }
    .premium-badge {
        position: absolute; top: 8px; right: 8px; background: #dbeafe; color: var(--p-blue);
        font-size: .6rem; font-weight: 800; border-radius: 6px; padding: 2px 6px; letter-spacing: .04em;
    }

    /* Quick add */
    #quick-add {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,.98); backdrop-filter: blur(8px);
        border-top: 1.5px solid var(--p-border); padding: 16px 18px;
        box-shadow: 0 -12px 32px rgba(15,23,42,.14);
        display: none; z-index: 20;
    }
    .premium-qa-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
    .premium-qa-name { font-size: .88rem; font-weight: 800; color: var(--p-text); line-height: 1.3; flex: 1; padding-right: 10px; }
    .premium-qa-close { background: #f1f5f9; border: none; color: var(--p-muted); font-size: 1.1rem; line-height: 1; padding: 5px 9px; cursor: pointer; border-radius: 8px; flex-shrink: 0; }
    .premium-qa-close:hover { background: #e2e8f0; color: var(--p-text); }
    .premium-qa-add {
        background: linear-gradient(135deg, var(--p-blue), var(--p-blue-dark)); color: #fff; border: none;
        border-radius: var(--p-radius-sm); width: 100%; padding: 11px; margin-top: 12px;
        font-weight: 800; font-size: .85rem; cursor: pointer; box-shadow: 0 4px 12px rgba(37,99,235,.3);
        transition: all .15s;
    }
    .premium-qa-add:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.4); }

    /* Select2 */
    .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; border-radius: var(--p-radius-sm) !important; border: 1.5px solid var(--p-border) !important; font-size: .875rem !important; }

    @media (max-width: 1100px) {
        .purchase-layout { flex-direction: column; }
        .purchase-catalog { position: relative; max-height: none; flex: unset; width: 100%; }
        .premium-grid { max-height: 360px; }
        .premium-fin-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .premium-fin-grid { grid-template-columns: 1fr; }
        .purchase-hero-title { font-size: 1.25rem; }
    }
</style>
@stop

@section('adminlte_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {
    'use strict';

    let allProducts = [];
    let cart = [];

    toastr.options = {
        positionClass: 'toast-top-right',
        timeOut: 2500,
        closeButton: true,
        progressBar: true,
        newestOnTop: true,
    };

    $(document).ready(function () {
        generateInvoiceNumber();

        $('#supplier_id').select2({
            theme: 'bootstrap4',
            placeholder: 'Search supplier…',
            allowClear: true,
            ajax: {
                url: '/admin/suppliers/search',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(s => ({ id: s.id, text: s.name || s.display_text }))
                }),
                cache: true
            }
        });

        fetch('/admin/products/pos-search?search=')
            .then(r => r.json())
            .then(data => {
                allProducts = data.data || data;
                renderGrid(allProducts);
                populateFilters();
                updateCatalogCount();
            })
            .catch(() => {
                $('#product-grid').html(
                    '<div class="premium-loading text-danger" style="grid-column:1/-1;">' +
                    '<i class="fas fa-exclamation-circle mr-1"></i> Failed to load products</div>'
                );
            });

        $('#catalog-search').on('input', debounce(applyFilters, 200));
        $('#product-grid').on('click', '.premium-card-item', function () {
            const product = allProducts.find(p => String(p.id) === String($(this).data('id')));
            if (!product) {
                console.error('Unable to find selected product.', $(this).data('id'));
                return;
            }
            window.openQuickAdd(product);
        });
        $('#qa-close').on('click', () => { $('#quick-add').slideUp(200); });

        $('#pay-full-btn').on('click', function () {
            const total = parseFloat($('#total-input').val()) || 0;
            $('#paid_amount').val(total.toFixed(2));
            updateTotals();
        });

        $('#paid_amount, #discount_amount, #tax_amount').on('input', updateTotals);

        $('#clear-cart-btn').on('click', function () {
            if (!cart.length) return;
            if (confirm('Clear all items from the cart?')) {
                cart = [];
                renderCart();
            }
        });

        $('#purchase-form').on('submit', function (e) {
            if (cart.length === 0) {
                e.preventDefault();
                toastr.error('Please add at least one product before submitting.', 'Cart is Empty');
                return false;
            }
            if (!$('#supplier_id').val()) {
                e.preventDefault();
                toastr.error('Please select a supplier.', 'Supplier Required');
                return false;
            }
            syncHiddenInputs();
        });
    });

    function generateInvoiceNumber() {
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const rand = Math.random().toString(36).substring(2, 7).toUpperCase();
        $('#invoice_number').val('PUR-' + yyyy + mm + dd + '-' + rand);
    }

    function renderGrid(products) {
        const grid = $('#product-grid').empty();
        if (!products.length) {
            grid.html('<div class="premium-loading" style="grid-column:1/-1;">No products found.</div>');
            return;
        }
        products.forEach(p => {
            const stock = p.stock !== undefined ? parseInt(p.stock) : null;
            const stockHtml = buildStockHtml(stock, p.variations);
            const varBadge = (p.variations && p.variations.length > 0) ? '<span class="premium-badge">VAR</span>' : '';
            grid.append(
                `<div class="premium-card-item" data-id="${p.id}">
                    ${varBadge}
                    <div class="premium-card-name">${escHtml(p.name)}</div>
                    <div class="premium-card-price">৳${parseFloat(p.purchase_price || p.price || 0).toFixed(2)}</div>
                    ${stockHtml}
                </div>`
            );
        });
        updateCatalogCount(products.length);
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
        if (qty > 10) return '<div class="premium-card-stock"><span class="premium-dot in"></span><span style="color:var(--p-green)">' + qty + ' in stock</span></div>';
        if (qty > 0) return '<div class="premium-card-stock"><span class="premium-dot low"></span><span style="color:var(--p-amber)">Low (' + qty + ')</span></div>';
        return '<div class="premium-card-stock"><span class="premium-dot out"></span><span style="color:var(--p-rose)">Out of stock</span></div>';
    }

    function populateFilters() {
        const cats = [...new Set(allProducts.map(p => p.category?.name || p.category).filter(Boolean))];
        const brands = [...new Set(allProducts.map(p => p.brand?.name || p.brand).filter(Boolean))];
        cats.forEach(c => $('#cat-filter').append('<option value="' + escHtml(c) + '">' + escHtml(c) + '</option>'));
        brands.forEach(b => $('#brand-filter').append('<option value="' + escHtml(b) + '">' + escHtml(b) + '</option>'));
    }

    function updateCatalogCount(n) {
        const count = (n !== undefined ? n : allProducts.length);
        $('#catalog-count').text(count + ' product' + (count === 1 ? '' : 's') + ' available');
    }

    window.applyFilters = function () {
        const term = $('#catalog-search').val().toLowerCase().trim();
        const cat = $('#cat-filter').val();
        const brand = $('#brand-filter').val();
        const filtered = allProducts.filter(p => {
            const matchSearch = !term || p.name.toLowerCase().includes(term) || (p.code && p.code.toLowerCase().includes(term));
            const matchCat = !cat || (p.category?.name === cat || p.category === cat);
            const matchBrand = !brand || (p.brand?.name === brand || p.brand === brand);
            return matchSearch && matchCat && matchBrand;
        });
        renderGrid(filtered);
    };

    window.openQuickAdd = function (p) {
        $('.premium-card-item').removeClass('active');
        $('.premium-card-item[data-id="' + p.id + '"]').addClass('active');

        $('#qa-name').text(p.name);
        $('#qa-price').val(parseFloat(p.purchase_price || p.price || 0).toFixed(2));
        $('#qa-qty').val(1);

        const varBox = $('#qa-variation-box').hide();
        const varSelect = $('#qa-variation').empty();

        if (p.variations && p.variations.length > 0) {
            p.variations.forEach(v => {
                varSelect.append(
                    '<option value="' + v.id + '" data-val="' + escHtml(v.variation_value) + '">' +
                    escHtml(v.variation_value) + ' (Stock: ' + (v.stock ?? '?') + ')</option>'
                );
            });
            varBox.slideDown(200);
        }

        $('#qa-add-btn').off('click').on('click', () => addToCart(p));
        const overlay = document.getElementById('quick-add');
        if (overlay.style.display === 'none' || overlay.style.display === '') {
            $('#quick-add').slideDown(220);
        }
    };

    function addToCart(p) {
        const hasVar = $('#qa-variation-box').is(':visible');
        const varId = hasVar ? $('#qa-variation').val() : null;
        const varName = hasVar ? $('#qa-variation option:selected').data('val') : '';
        const qty = parseFloat($('#qa-qty').val()) || 1;
        const price = parseFloat($('#qa-price').val()) || 0;

        if (qty <= 0) { toastr.warning('Please enter a valid quantity (greater than 0).', 'Invalid Qty'); return; }
        if (price < 0) { toastr.warning('Price cannot be negative.', 'Invalid Price'); return; }

        const cartKey = varId ? p.id + '_' + varId : String(p.id);
        const existingIdx = cart.findIndex(i => i.cartKey === cartKey);

        if (existingIdx > -1) {
            cart[existingIdx].qty += qty;
            cart[existingIdx].total = +(cart[existingIdx].qty * cart[existingIdx].price).toFixed(4);
        } else {
            cart.push({
                cartKey,
                product_id: p.id,
                variation_id: varId ? parseInt(varId) : null,
                name: p.name + (varName ? ' — ' + varName : ''),
                qty: qty,
                price: price,
                total: +(qty * price).toFixed(4)
            });
        }

        renderCart();
        $('#quick-add').slideUp(200);
        $('.premium-card-item').removeClass('active');
        toastr.success('<b>' + escHtml(p.name) + '</b> added to order.', 'Added', { timeOut: 1600 });
    }

    function renderCart() {
        const body = $('#purchase-items-body').empty();

        if (cart.length === 0) {
            $('#clear-cart-btn').hide();
            body.html(
                '<tr id="empty-row"><td colspan="5" class="text-center" style="padding:38px 16px;color:var(--p-soft)">' +
                '<i class="fas fa-box-open d-block mb-2" style="font-size:30px;opacity:.25"></i>' +
                '<span style="font-size:13px">Select products from the catalog &rarr;</span></td></tr>'
            );
            $('#items-summary').text('No items yet — pick products from the catalog.');
            updateTotals();
            return;
        }

        $('#clear-cart-btn').show();
        const totalQty = cart.reduce((s, i) => s + i.qty, 0);
        $('#items-summary').text(cart.length + ' item' + (cart.length === 1 ? '' : 's') + ' · ' + totalQty + ' units in this order');

        cart.forEach((item, idx) => {
            body.append(
                '<tr>' +
                '<td><div class="font-weight-bold" style="font-size:.875rem">' + escHtml(item.name) + '</div></td>' +
                '<td class="text-center"><input type="number" class="premium-inline" value="' + item.qty + '" min="0.01" step="any" onchange="window.updateQty(' + idx + ', this.value)"></td>' +
                '<td class="text-center"><input type="number" class="premium-inline premium-inline-wide" value="' + item.price + '" min="0" step="0.01" onchange="window.updatePrice(' + idx + ', this.value)"></td>' +
                '<td class="text-right font-weight-bold">৳' + item.total.toFixed(2) + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm" style="color:var(--p-rose);padding:3px 8px" onclick="window.removeItem(' + idx + ')" title="Remove"><i class="fas fa-times"></i></button></td>' +
                '</tr>'
            );
        });

        updateTotals();
    }

    window.updateQty = function (idx, val) {
        const q = parseFloat(val);
        if (isNaN(q) || q <= 0) return;
        cart[idx].qty = q;
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

    function updateTotals() {
        const subtotal = cart.reduce((sum, i) => sum + i.total, 0);
        const discount = Math.max(0, parseFloat($('#discount_amount').val()) || 0);
        const tax = Math.max(0, parseFloat($('#tax_amount').val()) || 0);
        const total = Math.max(0, subtotal - discount + tax);
        const paid = Math.max(0, parseFloat($('#paid_amount').val()) || 0);
        const due = Math.max(0, total - paid);
        const change = paid > total ? +(paid - total).toFixed(2) : 0;

        $('#subtotal-display').text(subtotal.toFixed(2));
        $('#discount-display').text(discount.toFixed(2));
        $('#tax-display').text(tax.toFixed(2));
        $('#total-display').text(total.toFixed(2));

        $('#due_display').val(due.toFixed(2)).css('color', due > 0 ? 'var(--p-rose)' : 'var(--p-green)');
        $('#change_display').val(change.toFixed(2)).css('color', change > 0 ? 'var(--p-green)' : 'var(--p-soft)');

        syncHiddenInputs(subtotal, total, due, change, discount, tax);
    }

    function syncHiddenInputs(subtotal, total, due, change, discount, tax) {
        if (subtotal === undefined) {
            subtotal = cart.reduce((sum, i) => sum + i.total, 0);
            discount = Math.max(0, parseFloat($('#discount_amount').val()) || 0);
            tax = Math.max(0, parseFloat($('#tax_amount').val()) || 0);
            total = Math.max(0, subtotal - discount + tax);
            const paid = Math.max(0, parseFloat($('#paid_amount').val()) || 0);
            due = Math.max(0, total - paid);
            change = paid > total ? +(paid - total).toFixed(2) : 0;
        }
        $('#subtotal-input').val(subtotal.toFixed(2));
        $('#total-input').val(total.toFixed(2));
        $('#due-input').val(due.toFixed(2));
        $('#change-input').val(change.toFixed(2));
        const payload = cart.map(i => ({
            product_id: i.product_id,
            variation_id: i.variation_id,
            unit_price: i.price,
            qty: i.qty,
            total: i.total,
        }));
        $('#items-json-input').val(JSON.stringify(payload));
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function debounce(fn, delay) {
        let t;
        return function () {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), delay);
        };
    }
})();
</script>
@stop
