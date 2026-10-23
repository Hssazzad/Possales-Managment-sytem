@extends('adminlte::page')
@section('title', 'Point of Sale')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v=9">
    <style>
        /* Only override POS content area, NOT the sidebar */
        body { background: #f5f5f5 !important; color: #1a1a1a !important; }
        .content-wrapper { background: #f5f5f5 !important; color: #1a1a1a !important; }
        .pos-layout, .pos-left, .pos-right,
        .left-card, .prod-header, .prod-list, .prod-grid,
        .pos-topbar, .pos-payment, .cart-wrap,
        #prodGrid, #prodGrid > div {
            background: #fff !important;
            color: #1a1a1a !important;
        }
        input, select, textarea, .form-control {
            background: #fafafa !important;
            color: #333 !important;
            border-color: #e0e0e0 !important;
        }
    </style>
@stop

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Phone-scan flash messages (server-side validation / scan result) --}}
    @if (session('scan_success'))
        <div class="scan-flash scan-flash-success"><i class="fas fa-check-circle"></i> {{ session('scan_success') }}</div>
    @endif
    @if ($errors->any())
        <div class="scan-flash scan-flash-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('barcode') ?: $errors->first() }}</div>
    @endif

    <div class="pos-layout">

        {{-- LEFT: Cart & Payment --}}
        <div class="pos-left">
            <div class="left-card">

                {{-- Top bar: date, time, customer --}}
                <div class="pos-topbar">
                    <div class="pos-topbar-row">
                        <div style="position:relative; flex:1;">
                            <input type="date" class="tb-input" id="saleDate" value="{{ date('Y-m-d') }}" style="width:100%; padding-left:32px;">
                            <i class="far fa-calendar-alt" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#bbb; font-size:12px;"></i>
                        </div>
                        <div style="position:relative; flex:1;">
                            <input type="time" class="tb-input" id="saleTime" value="{{ date('H:i') }}" style="width:100%; padding-left:32px;">
                            <i class="far fa-clock" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#bbb; font-size:12px;"></i>
                        </div>
                    </div>
                    <div class="pos-topbar-row" style="align-items:center;">
                        <div style="position:relative; flex:1;">
                            <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#bbb; font-size:12px; z-index:2;"></i>
                            <input type="text" id="customerSearch" class="tb-input" placeholder="Select Customer * (Required)" autocomplete="off" style="padding-left:35px; width:100%; border-color:#f59e0b;" required>
                            <input type="hidden" id="customerId" name="customer_id" value="">
                            <div id="customerSearchResults" class="cust-results" style="display:none;"></div>
                        </div>
                        <div id="walkingCustomerBadge" class="walking-customer-badge" title="Click to add new customer" style="border-color:#f59e0b; color:#b45309;">
                            <i class="far fa-user"></i> No Customer <span style="color:#dc2626;">*</span>
                        </div>
                        <button type="button" id="btnAddCustomer" class="btn-add-cust" title="Add new customer">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                {{-- Cart table --}}
                <div class="cart-wrap">
                    <table class="cart-table">
                        <thead>
                        <tr>
                            <th style="min-width:140px;">Product</th>
                            <th>Price</th>
                            <th>Disc%</th>
                            <th>Qty</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th style="text-align:center; width:30px;"><i class="far fa-trash-alt"></i></th>
                        </tr>
                        </thead>
                        <tbody id="cartBody">
                        <tr>
                            <td colspan="6">
                                <div class="cart-empty">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>Your cart is empty</p>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Payment --}}
                <div class="pos-payment">
                    <div class="pay-amounts-row">
                        <div class="pay-col-box">
                            <label>Receive</label>
                            <input type="number" id="receiveAmount" value="0" min="0" step="0.01">
                        </div>
                        <div class="pay-col-box">
                            <label>Change</label>
                            <input type="number" id="changeAmount" value="0.00" readonly>
                        </div>
                        <div class="pay-col-box">
                            <label>Due</label>
                            <input type="number" id="dueAmount" value="0.00" readonly>
                        </div>
                    </div>
                    <div class="pay-bottom-grid">
                        <div class="method-col">
                            <label>Method</label>
                            <select id="paymentMethod" class="method-sel">
                                <option value="cash">Cash</option>
                                <option value="bkash">Bkash</option>
                                <option value="nagad">Nagad</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="totals-col">
                            <div class="tot-row">
                                <span class="tl">Subtotal</span>
                                <span class="tv">BDT <span id="dispSubtotal">0.00</span></span>
                            </div>
                            <div class="tot-row">
                            <span class="tl">VAT
                                <select id="vatType" class="sm-sel">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="10">10%</option>
                                    <option value="15">15%</option>
                                </select>
                            </span>
                                <span class="tv">BDT <span id="dispVat">0.00</span></span>
                            </div>
                            <div class="tot-row">
                            <span class="tl">Discount
                                <select id="discountType" class="sm-sel">
                                    <option value="flat">BDT</option>
                                    <option value="percent">%</option>
                                </select>
                            </span>
                                <input type="number" id="discountValue" class="sm-inp" value="0" min="0">
                            </div>
                            <div class="tot-row tot-main">
                                <span class="tl">PAYABLE</span>
                                <span class="tv">BDT <span id="dispTotal">0.00</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button class="pos-btn btn-clear" id="btnClear"><i class="fas fa-times"></i> CLEAR</button>
                        <button type="button" class="pos-btn btn-pay" id="btnPay"><i class="far fa-check-circle"></i> PAY</button>
                    </div>
                </div>

            </div>
        </div>

        {{-- RIGHT: Product list --}}
        <div class="pos-right">
            <div class="prod-header">
                <h3>Select Products to Sell</h3>
                <div class="search-row" style="position:relative;">
                    <div class="search-input-group">
                        <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#bbb; font-size:13px;"></i>
                        <input type="text" class="search-inp" id="prodSearch" placeholder="Search..." autocomplete="off">
                        <i class="fas fa-barcode" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#999; font-size:14px;"></i>
                    </div>
                    <button class="btn-icon" title="Add Custom Product"><i class="fas fa-plus"></i></button>
                    <button class="btn-icon" title="Refresh List" onclick="loadProducts()"><i class="fas fa-redo-alt"></i></button>
                    <button type="button" class="btn-scan-phone" id="btnPhoneScan" title="Phn Barcode scan" onclick="window.PhoneBarcodeScanner && PhoneBarcodeScanner.open()">
                        <i class="fas fa-camera"></i> <span>Phone Scan</span>
                    </button>

                    {{-- ★ NOTUN: Phone camera scanner modal ★ --}}
                    <div id="scannerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; flex-direction:column;">
                        <div id="qr-reader" style="width:90%; max-width:400px;"></div>
                        <button type="button" id="btnCloseScanner" class="btn btn-light mt-3">✕ Turn Of Camera </button>
                    </div>

                    <div id="productSearchDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #e0e0e0; border-top:none; border-radius:0 0 10px 10px; max-height:400px; overflow-y:auto; z-index:9999; box-shadow:0 8px 24px rgba(0,0,0,0.08);"></div>
                </div>
            </div>

            {{-- Cart details panel --}}
            <div id="cartDetailsPanel" style="display:none; margin:0 10px 10px 10px; background:#fafafa; border-radius:12px; overflow:hidden; border:1px solid #eee;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 14px; background:#f5f5f5; border-bottom:1px solid #eee;">
                <span style="font-size:9px; font-weight:700; letter-spacing:1.5px; color:#999; text-transform:uppercase;">
                    <i class="fas fa-shopping-cart" style="margin-right:4px;"></i>Cart Items
                    <span id="cdpCount" style="background:#e0e0e0; color:#666; padding:1px 7px; border-radius:10px; margin-left:4px; font-size:10px;"></span>
                </span>
                    <span id="cdpTotal" style="font-size:12px; font-weight:800; color:#333;"></span>
                </div>
                <div id="cdpList" style="max-height:220px; overflow-y:auto; padding:6px 0;"></div>
            </div>

            <div class="prod-list" id="prodGrid" style="background:#fff !important; color:#000 !important;">
                <div style="text-align:center; padding:40px; color:#000 !important; font-weight:700; width:100%;">
                    <i class="fas fa-spinner fa-spin fa-2x" style="display:block; margin-bottom:10px;"></i>
                    Loading products...
                </div>
            </div>
        </div>

    </div>

    {{-- Product Details Modal --}}
    <div class="modal-overlay" id="prodDetailsModal">
        <div class="modal-box" style="width:500px;">
            <div class="modal-head">
                <span>Product Details</span>
                <button class="modal-close" onclick="closeProdDetails()">&times;</button>
            </div>
            <div class="modal-body" id="prodDetailsBody"></div>
            <div class="modal-foot">
                <button class="modal-cancel-btn" onclick="closeProdDetails()">Close</button>
            </div>
        </div>
    </div>

    {{-- Variation Modal --}}
    <div class="modal-overlay" id="varModal">
        <div class="modal-box">
            <div class="modal-head">
                <span id="varModalTitle">Select Variation</span>
                <button class="modal-close" id="varModalClose">&times;</button>
            </div>
            <div class="modal-body" id="varModalBody"></div>
            <div class="modal-foot">
                <button class="modal-cancel-btn" id="varModalCancel">Cancel</button>
                <button class="modal-add-btn" id="varModalAdd"><i class="fas fa-cart-plus"></i> Add to Cart</button>
            </div>
        </div>
    </div>

    {{-- Customer Modal --}}
    <div class="modal" id="customerModal" tabindex="-1" role="dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1050; background:rgba(0,0,0,0.4);">
        <div class="modal-dialog modal-sm" role="document" style="margin:10vh auto;">
            <div class="modal-content" style="border-radius:14px; border:1px solid #e8e8e8; box-shadow:0 20px 60px rgba(0,0,0,0.15); background:#fff;">
                <div class="modal-header" style="background:#fff; border-bottom:1px solid #f0f0f0; border-radius:14px 14px 0 0; padding:14px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="font-size:15px; font-weight:700; color:#333; margin:0;"><i class="fas fa-user-plus"></i> Add Customer</h4>
                    <button type="button" id="customerModalCloseBtn" style="background:none; border:none; font-size:20px; color:#999; cursor:pointer;">&times;</button>
                </div>
                <div class="modal-body" style="padding:16px;">
                    <div style="margin-bottom:12px;">
                        <label style="font-size:13px; color:#666; display:block; margin-bottom:4px;">Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="modalCustName" placeholder="Enter customer name" style="width:100%; padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; background:#fafafa; color:#333; outline:none;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:13px; color:#666; display:block; margin-bottom:4px;">Phone <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="modalCustPhone" placeholder="Enter phone number" style="width:100%; padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; background:#fafafa; color:#333; outline:none;">
                    </div>
                    <div>
                        <label style="font-size:13px; color:#666; display:block; margin-bottom:4px;">Address</label>
                        <textarea id="modalCustAddress" rows="2" placeholder="Enter address" style="width:100%; padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; background:#fafafa; color:#333; outline:none; resize:vertical;"></textarea>
                    </div>
                </div>
                <div style="padding:14px 16px; border-top:1px solid #f0f0f0; background:#fafafa; border-radius:0 0 14px 14px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" id="customerModalCancelBtn" style="background:#fff; border:1px solid #e0e0e0; color:#666; font-weight:600; border-radius:8px; padding:8px 16px; cursor:pointer; font-size:13px;">Cancel</button>
                    <button type="button" id="btnSaveCustomer" style="background:#1a1a1a; border:none; color:#fff; font-weight:700; border-radius:8px; padding:8px 16px; cursor:pointer; font-size:13px;"><i class="fas fa-save"></i> Save & Select</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Modal --}}
    <div class="inv-overlay" id="invoicePrintModal">
        <div class="inv-box">
            <div class="inv-head">
                <span><i class="fas fa-receipt"></i> Sale Invoice</span>
                <button class="inv-close" onclick="closeInvoiceModal()">&times;</button>
            </div>
            <div style="background:#fff;">
                <div id="thermalInvoiceContent" class="inv-print-wrap">
                    @include('admin.pos.partials.invoice-content')
                </div>
            </div>
            <div class="inv-foot">
                <button onclick="closeInvoiceModal()" style="background:#fff; border:1px solid #e0e0e0; color:#666; font-weight:600; border-radius:8px; padding:8px 20px; cursor:pointer; font-size:13px;">
                    <i class="fas fa-times" style="margin-right:5px;"></i>Cancel
                </button>
                <button onclick="printInvoice()" style="background:#1a1a1a; border:none; color:#fff; font-weight:700; border-radius:8px; padding:8px 20px; cursor:pointer; font-size:13px;">
                    <i class="fas fa-print" style="margin-right:5px;"></i>Print
                </button>
            </div>
        </div>
    </div>

    @php
        $phoneScannerConfig = [
            'submitRoute' => route('pos.scan-barcode'),
            'autoSubmit'  => session('scanned_barcode') !== null,
            'autoBarcode' => (string) session('scanned_barcode', ''),
        ];
    @endphp

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
    <script src="{{ asset('js/pos.js') }}?v={{ time() }}"></script>
    <script>
        window.PHONE_SCANNER_CONFIG = @json($phoneScannerConfig);
    </script>
    <script src="{{ asset('js/phone-barcode-scanner.js') }}?v={{ time() }}"></script>
    <script>
        // Remove any AdminLTE dark mode classes
        document.documentElement.classList.remove('dark-mode', 'dark', 'dark-theme');
        document.body.classList.remove('dark-mode', 'dark', 'dark-theme');
        document.querySelector('.content-wrapper')?.classList.remove('dark-mode', 'dark');
        document.getElementById('invoicePrintModal').addEventListener('click', function(e) { if (e.target === this) closeInvoiceModal(); });
    </script>
@stop
