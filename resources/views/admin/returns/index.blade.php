@extends('adminlte::page')

@section('title', 'Returns Management | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}?v={{ time() }}">
    <style>
    /* Modal Overlay */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: flex-start;
        padding-top: 40px;
    }
    .modal-overlay.active { display: flex; }

    /* Modal Box */
    .return-modal {
        background: #ffffff;
        border-radius: 8px;
        width: 820px;
        max-width: 95vw;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalIn .2s ease;
    }
    @keyframes modalIn {
        from { transform: translateY(-20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* Modal Header */
    .modal-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .modal-header-custom h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #111;
    }
    .modal-close-btn {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #e24b4a;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        line-height: 1;
        display: flex; align-items: center; justify-content: center;
    }

    /* Modal Body */
    .modal-body-custom { padding: 24px; }

    .modal-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 16px;
    }
    .modal-form-group { display: flex; flex-direction: column; gap: 5px; }
    .modal-form-group.full { grid-column: 1 / -1; }
    .modal-form-group.half { grid-column: span 1; }

    .modal-form-group label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    .modal-form-group label .req { color: #e24b4a; }

    .modal-form-group select,
    .modal-form-group input,
    .modal-form-group textarea {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        color: #111;
        background: #fff;
        outline: none;
        transition: border-color .15s;
        width: 100%;
    }
    .modal-form-group select:focus,
    .modal-form-group input:focus,
    .modal-form-group textarea:focus {
        border-color: #1a3a6b;
        box-shadow: 0 0 0 3px rgba(26,58,107,.08);
    }

    /* Product search row */
    .product-search-wrap {
        position: relative;
    }
    .product-search-wrap input { padding-right: 36px; }
    .scan-icon {
        position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%);
        color: #9ca3af; font-size: 15px;
    }

    /* Mini product table */
    .mini-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 13px; }
    .mini-table th {
        background: #f9fafb;
        padding: 9px 12px;
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    .mini-table td {
        padding: 9px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }
    .mini-table tbody tr:last-child td { border-bottom: none; }

    /* Totals box */
    .totals-wrap { display: flex; justify-content: flex-end; margin-bottom: 16px; }
    .totals-box {
        width: 260px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 12px;
        font-size: 13px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }
    .totals-row:last-child {
        border-bottom: none;
        font-weight: 600;
        color: #111;
    }

    /* Bottom grid */
    .modal-bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }

    /* Customer add row */
    .customer-row { display: flex; gap: 6px; }
    .customer-row select { flex: 1; }
    .add-customer-btn {
        width: 36px; height: 36px;
        background: #1a3a6b;
        border: none;
        border-radius: 6px;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* Modal Footer */
    .modal-footer-custom {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .btn-modal-save {
        padding: 9px 22px;
        background: #1a3a6b;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }
    .btn-modal-save:hover { background: #14306a; }
    .btn-modal-cancel {
        padding: 9px 18px;
        background: #fff;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    .btn-modal-cancel:hover { background: #f3f4f6; }

    /* ── Same-page print report (screen-e hidden, print-e visible) ── */
    .print-report { display: none; }
    @media print {
        .main-sidebar, .main-header, .main-footer,
        .list-container, .modal-overlay { display: none !important; }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            min-height: auto !important;
        }
        .content { padding: 0 !important; }
        .pt-4 { padding: 0 !important; }
        .print-report {
            display: block !important;
            color: #1f2937;
            font-size: 12px;
        }
        .print-report .sheet { border-top: 6px solid #1D1D1D; }
        .print-report .shop-header {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 24px 14px;
            border-bottom: 2px solid #1D1D1D;
        }
        .print-report .shop-header img { width: 64px; height: 64px; object-fit: contain; }
        .print-report .shop-header h1 { font-size: 24px; letter-spacing: 1px; color: #111; margin: 0 0 2px; }
        .print-report .shop-header p { font-size: 11px; color: #6b7280; margin: 1px 0; }
        .print-report .title-band {
            background: #1D1D1D; color: #fff; text-align: center;
            padding: 9px 12px; font-size: 14px; font-weight: 700; letter-spacing: 3px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report .title-band span { color: #F59115; }
        .print-report .report-meta {
            display: flex; justify-content: space-between;
            padding: 10px 24px; border-bottom: 1px dashed #d1d5db; color: #4b5563;
        }
        .print-report table { width: 100%; border-collapse: collapse; }
        .print-report th {
            background: #f3f4f6; font-size: 10px; text-transform: uppercase;
            letter-spacing: .6px; color: #4b5563; padding: 8px 10px;
            text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report th.num, .print-report td.num { text-align: right; white-space: nowrap; }
        .print-report th.center, .print-report td.center { text-align: center; }
        .print-report td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; }
        .print-report tbody tr:nth-child(even) { background: #f5f5f5; }
        .print-report .p-status {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; white-space: nowrap;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report .p-status.completed, .print-report .p-status.approved { background: #d1fae5; color: #065f46; }
        .print-report .p-status.pending { background: #fef3c7; color: #92400e; }
        .print-report .p-status.rejected { background: #fee2e2; color: #b91c1c; }
        .print-report .p-summary {
            display: flex; justify-content: flex-end;
            padding: 12px 24px; border-top: 2px solid #1D1D1D; background: #f9fafb;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report .p-summary table { width: auto; min-width: 280px; }
        .print-report .p-summary td { padding: 4px 0 4px 16px; border: none; }
        .print-report .p-summary td:last-child { text-align: right; font-weight: 700; }
        .print-report .p-summary tr.grand td { font-size: 14px; border-top: 1px solid #d1d5db; padding-top: 8px; }
        .print-report .p-summary tr.grand td:last-child { color: #b91c1c; }
        .print-report .p-sign { display: flex; justify-content: space-between; gap: 30px; padding: 36px 24px 10px; }
        .print-report .p-sign div { flex: 1; text-align: center; font-size: 11px; color: #6b7280; }
        .print-report .p-sign .line { border-top: 1px solid #111; height: 22px; }
        .print-report .p-footer { text-align: center; padding: 12px 24px 16px; font-size: 11px; color: #6b7280; }
    }
    </style>
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="returnSearch" placeholder="Search returns by invoice or customer...">
            </div>
            <div class="action-buttons">
                <button onclick="exportReturns()" class="btn-export">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button onclick="window.print()" class="btn-export">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                {{-- ✅ route এর বদলে modal open করবে --}}
                <button onclick="openReturnModal()" class="btn-add-new">
                    <i class="fas fa-plus mr-1"></i> New Return
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Date</th>
                        <th>Sale Invoice</th>
                        <th>Customer</th>
                        <th>Product Details</th>
                        <th class="text-right">Refund Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $return->id }}</td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $return->return_date->format('d M, Y') }}</div>
                        </td>
                        <td>
                            @if($return->sale && $return->sale->invoice_number)
                                <span class="badge badge-light border text-primary" style="font-size:13px;">{{ $return->sale->invoice_number }}</span>
                            @else
                                <span class="text-muted small">Sale ID: {{ $return->sale_id ?: 'N/A' }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $return->customer->name ?? 'Walk-in Customer' }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $return->product->name ?? 'Unknown Product' }}</div>
                            <div class="text-muted small">Qty: {{ $return->quantity }}</div>
                        </td>
                        <td class="text-right font-weight-bold text-danger">
                            ৳{{ number_format((float) $return->refund_amount, 2) }}
                        </td>
                        <td class="text-center">
                            @if($return->status == 'completed')
                                <span class="status-badge status-active">Completed</span>
                            @elseif($return->status == 'pending')
                                <span class="status-badge status-inactive">Pending</span>
                            @elseif($return->status == 'approved')
                                <span class="badge badge-info px-2 py-1">Approved</span>
                            @else
                                <span class="badge badge-danger px-2 py-1">{{ ucfirst($return->status) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                    <a class="dropdown-item" href="{{ route('returns.show', $return->id) }}">
                                        <i class="fas fa-eye text-primary mr-2"></i> View Details
                                    </a>
                                    <a class="dropdown-item" href="{{ route('returns.edit', $return->id) }}">
                                        <i class="fas fa-edit text-warning mr-2"></i> Edit Return
                                    </a>
                                    <a class="dropdown-item" href="{{ route('returns.receipt', $return->id) }}" target="_blank">
                                        <i class="fas fa-print text-success mr-2"></i> Print Receipt
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteReturn({{ $return->id }})">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-undo fa-3x mb-3 text-light"></i>
                                <h5>No Returns Found</h5>
                                <p>Start by creating your first return record.</p>
                                <button onclick="openReturnModal()" class="btn-add-new mt-2">
                                    <i class="fas fa-plus mr-1"></i> Create Return
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 flex-wrap" style="border-top:1px solid #e5e7eb; gap:15px;">
            <div class="text-muted text-sm">
                Showing <strong>{{ $returns ? $returns->total() : 0 }}</strong> total returns
            </div>
            <div>
                <span class="badge badge-light border text-warning mr-2" style="font-size:13px; padding:6px 10px;" title="Pending approval — stock not yet restored">
                    Pending: <strong>{{ $summary['pending'] ?? 0 }}</strong>
                </span>
                <span class="badge badge-light border text-success mr-2" style="font-size:13px; padding:6px 10px;" title="Approved/Completed — stock restored">
                    Completed: <strong>{{ $summary['completed'] ?? 0 }}</strong>
                </span>
                <span class="badge badge-light border text-danger" style="font-size:13px; padding:6px 10px;" title="Only approved/completed refunds count">
                    Total Refunded: <strong>৳{{ number_format($summary['total_refunded'] ?? 0, 2) }}</strong>
                </span>
            </div>
            @if($returns && $returns->hasPages())
            <div>{{ $returns->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Same-page print report (screen-e hidden, print-e oi style-e print hobe) ── --}}
<div class="print-report">
    <div class="sheet">
        <div class="shop-header">
            <img src="{{ asset('images/bongobay-logo.jpg') }}" alt="Shop Logo">
            <div>
                <h1>{{ $shopName ?? env('SHOP_NAME', config('app.name', 'BongoBay')) }}</h1>
                @if($shopAddress ?? env('SHOP_ADDRESS'))<p>{{ $shopAddress ?? env('SHOP_ADDRESS') }}</p>@endif
                @if($shopPhone ?? env('SHOP_PHONE'))<p>Phone: {{ $shopPhone ?? env('SHOP_PHONE') }}</p>@endif
                @if(env('SHOP_EMAIL'))<p>{{ env('SHOP_EMAIL') }}</p>@endif
            </div>
        </div>

        <div class="title-band">SALES RETURN <span>REPORT</span></div>

        <div class="report-meta">
            <span>Total Records: <strong>{{ $summary['total'] ?? 0 }}</strong></span>
            <span>Printed: <strong>{{ now()->format('d M Y, h:i A') }}</strong></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Sale Invoice</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th class="center">Qty</th>
                    <th class="num">Refund</th>
                    <th>Method</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($printReturns ?? [] as $r)
                <tr>
                    <td>#{{ $r->id }}</td>
                    <td style="white-space:nowrap;">{{ optional($r->return_date)->format('d M Y') ?? $r->created_at->format('d M Y') }}</td>
                    <td>{{ $r->sale?->invoice_number ?? ('Sale #'.$r->sale_id) }}</td>
                    <td>{{ $r->customer?->name ?? 'Walk-in' }}</td>
                    <td>{{ $r->product?->name ?? 'Unknown' }}</td>
                    <td class="center">{{ $r->quantity }}</td>
                    <td class="num">৳{{ number_format((float) $r->refund_amount, 2) }}</td>
                    <td>{{ ucfirst($r->refund_method) }}</td>
                    <td class="center"><span class="p-status {{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center; padding:24px; color:#6b7280;">No returns found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-summary">
            <table>
                <tr><td>Total Returns</td><td>{{ $summary['total'] ?? 0 }}</td></tr>
                <tr><td>Total Qty</td><td>{{ ($printReturns ?? collect())->sum('quantity') }}</td></tr>
                <tr><td>Pending</td><td>{{ $summary['pending'] ?? 0 }}</td></tr>
                <tr><td>Completed</td><td>{{ $summary['completed'] ?? 0 }}</td></tr>
                <tr class="grand"><td>Total Refunded</td><td>৳{{ number_format($summary['total_refunded'] ?? 0, 2) }}</td></tr>
            </table>
        </div>

        <div class="p-sign">
            <div><div class="line"></div>Prepared By</div>
            <div><div class="line"></div>Authorized Signature</div>
        </div>

        <div class="p-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</div>

{{-- ✅ Dreams POS Style Modal --}}
<div class="modal-overlay" id="returnModalOverlay" onclick="closeOnBackdrop(event)">
    <div class="return-modal">

        <div class="modal-header-custom">
            <h4><i class="fas fa-undo mr-2"></i> Add Sales Return</h4>
            <button class="modal-close-btn" onclick="closeReturnModal()">×</button>
        </div>

        <div class="modal-body-custom">
            <form method="POST" action="{{ route('returns.store') }}" id="returnForm">
                @csrf

                <!-- Row 1: Customer, Date, Reference -->
                <div class="modal-form-grid">
                    <div class="modal-form-group">
                        <label>Customer Name <span class="req">*</span></label>
                        <div class="customer-row">
                            <select name="customer_id" id="modal_customer_id">
                                <option value="">Choose Customer</option>
                            </select>
                            <button type="button" class="add-customer-btn" title="Add Customer">+</button>
                        </div>
                    </div>
                    <div class="modal-form-group">
                        <label>Sale Invoice <span class="req">*</span></label>
                        <select name="sale_id" id="modal_sale_id" required>
                            <option value="">Select Invoice</option>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label>Return Date</label>
                        <input type="date" name="return_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Row 2: Product -->
                <div class="modal-form-grid">
                    <div class="modal-form-group full">
                        <label>Product <span class="req">*</span></label>
                        <div class="product-search-wrap">
                            <select name="product_id" id="modal_product_id" required>
                                <option value="">Please type product code and select</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Mini Table -->
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Unit Price</th>
                            <th>QTY</th>
                            <th>Discount</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="returnTableBody">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3" style="font-size:13px;">
                                No products added yet
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="totals-wrap">
                    <div class="totals-box">
                        <div class="totals-row"><span>Discount</span><span>৳ 0.00</span></div>
                        <div class="totals-row"><span>Refund Amount</span><span id="totalRefund">৳ 0.00</span></div>
                    </div>
                </div>

                <!-- Bottom Fields -->
                <div class="modal-bottom-grid">
                    <div class="modal-form-group">
                        <label>Quantity <span class="req">*</span></label>
                        <input type="number" name="quantity" min="1" value="1" required>
                    </div>
                    <div class="modal-form-group">
                        <label>Refund Amount <span class="req">*</span></label>
                        <input type="number" name="refund_amount" step="0.01" min="0" required>
                    </div>
                    <div class="modal-form-group">
                        <label>Refund Method <span class="req">*</span></label>
                        <select name="refund_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="credit">Store Credit</option>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label>Status <span class="req">*</span></label>
                        <select name="status">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <!-- Reason -->
                <div class="modal-form-group" style="margin-bottom:16px;">
                    <label>Return Reason <span class="req">*</span></label>
                    <textarea name="reason" rows="2" placeholder="Enter the reason for return..." required></textarea>
                </div>

                <!-- Notes -->
                <div class="modal-form-group" style="margin-bottom:20px;">
                    <label>Additional Notes</label>
                    <textarea name="notes" rows="2" placeholder="Enter any additional notes..."></textarea>
                </div>

                <!-- Footer -->
                <div class="modal-footer-custom">
                    <button type="button" class="btn-modal-cancel" onclick="closeReturnModal()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn-modal-save">
                        <i class="fas fa-save mr-1"></i> Save Return
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// ── Modal Open / Close ──────────────────────────────
function openReturnModal() {
    document.getElementById('returnModalOverlay').classList.add('active');
    loadModalData();
}
function closeReturnModal() {
    document.getElementById('returnModalOverlay').classList.remove('active');
}
function closeOnBackdrop(e) {
    if (e.target.id === 'returnModalOverlay') closeReturnModal();
}

// ── Load Dropdowns ──────────────────────────────────
function loadModalData() {
    // Sales
    fetch('/admin/sales/search')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('modal_sale_id');
            sel.innerHTML = '<option value="">Select Invoice</option>';
            data.forEach(s => {
                const o = document.createElement('option');
                o.value = s.id;
                o.textContent = s.display_text || s.invoice_number || 'Sale #' + s.id;
                sel.appendChild(o);
            });
        })
        .catch(() => {});

    // Customers
    fetch('/admin/customers/search')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('modal_customer_id');
            sel.innerHTML = '<option value="">Choose Customer</option>';
            data.forEach(c => {
                const o = document.createElement('option');
                o.value = c.id;
                o.textContent = c.display_text || c.name;
                sel.appendChild(o);
            });
        })
        .catch(() => {});

    // Products
    fetch('/admin/products/search')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('modal_product_id');
            sel.innerHTML = '<option value="">Select Product</option>';
            data.forEach(p => {
                const o = document.createElement('option');
                o.value = p.id;
                o.textContent = p.name;
                sel.appendChild(o);
            });
        })
        .catch(() => {});
}

// ── Search ──────────────────────────────────────────
document.getElementById('returnSearch')?.addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(s) ? '' : 'none';
    });
});

// ── Delete ──────────────────────────────────────────
function deleteReturn(id) {
    if (!confirm('Are you sure you want to delete this return?')) return;
    fetch(`/admin/returns/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.message); })
    .catch(() => alert('Error deleting return.'));
}

// ── Export ──────────────────────────────────────────
function exportReturns() {
    window.location.href = '/admin/returns/export';
}

// ── ESC key to close ────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeReturnModal();
});
</script>
@stop