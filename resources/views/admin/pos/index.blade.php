@extends('adminlte::page')
@section('title', 'Point of Sale | BongoBay')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v=12">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (session('scan_success'))
        <div class="scan-flash scan-flash-success"><i class="fas fa-check-circle"></i> {{ session('scan_success') }}</div>
    @endif
    @if ($errors->any())
        <div class="scan-flash scan-flash-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('barcode') ?: $errors->first() }}</div>
    @endif

    <div class="pos-layout">

        {{-- LEFT: Cart & Payment (visually right via row-reverse) --}}
        <div class="pos-left">
            <div class="left-card">

                {{-- Top bar: customer + sale info --}}
                <div class="pos-topbar">
                    <div class="pos-topbar-head">
                        <span class="pos-topbar-title"><i class="fas fa-bolt"></i> Checkout</span>
                        <div class="pos-topbar-meta">
                            <span class="pos-meta-pill"><i class="far fa-calendar-alt"></i> <input type="date" id="saleDate" value="{{ date('Y-m-d') }}"></span>
                            <span class="pos-meta-pill"><i class="far fa-clock"></i> <input type="time" id="saleTime" value="{{ date('H:i') }}"></span>
                        </div>
                    </div>

                    <div class="pos-customer-row">
                        <div class="pos-customer-search">
                            <i class="fas fa-search"></i>
                            <input type="text" id="customerSearch" placeholder="Search customer by name or phone" autocomplete="off" required>
                            <input type="hidden" id="customerId" name="customer_id" value="">
                            <div id="customerSearchResults" class="cust-results" style="display:none;"></div>
                        </div>
                        <div id="walkingCustomerBadge" class="walking-customer-badge" title="Select customer">
                            <i class="far fa-user"></i> No Customer
                        </div>
                        <button type="button" id="btnAddCustomer" class="btn-add-cust" title="Add new customer">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <p class="pos-customer-hint">Customer is required before paying. Press <kbd>F10</kbd> to add a new one.</p>
                </div>

                {{-- Cart table --}}
                <div class="cart-wrap">
                    <table class="cart-table">
                        <thead>
                        <tr>
                            <th style="min-width:150px;">Product</th>
                            <th>Price</th>
                            <th>Disc%</th>
                            <th>Qty</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th style="text-align:center; width:36px;"><i class="far fa-trash-alt"></i></th>
                        </tr>
                        </thead>
                        <tbody id="cartBody">
                        <tr>
                            <td colspan="6">
                                <div class="cart-empty">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>Your cart is empty</p>
                                    <small>Click a product or scan a barcode to start</small>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Payment --}}
                <div class="pos-payment">
                    <div class="pay-amounts-row">
                        <div class="pay-col-box pay-receive">
                            <label><i class="fas fa-hand-holding-usd"></i> Receive</label>
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

                    <div class="pay-method-tiles" role="group" aria-label="Payment method">
                        <button type="button" class="pay-tile active" data-method="cash"><i class="fas fa-money-bill-wave"></i><span>Cash</span></button>
                        <button type="button" class="pay-tile" data-method="bkash"><i class="fas fa-mobile-alt"></i><span>bKash</span></button>
                        <button type="button" class="pay-tile" data-method="nagad"><i class="fas fa-wallet"></i><span>Nagad</span></button>
                        <button type="button" class="pay-tile" data-method="card"><i class="fas fa-credit-card"></i><span>Card</span></button>
                        <select id="paymentMethod" class="method-sel" style="display:none;">
                            <option value="cash">Cash</option>
                            <option value="bkash">Bkash</option>
                            <option value="nagad">Nagad</option>
                            <option value="card">Card</option>
                        </select>
                    </div>

                    <div class="pay-bottom-grid">
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
                                <span class="tl">Payable</span>
                                <span class="tv">BDT <span id="dispTotal">0.00</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="pos-btn btn-clear" id="btnClear"><i class="fas fa-times"></i> CLEAR</button>
                        <button type="button" class="pos-btn btn-pay" id="btnPay"><i class="far fa-check-circle"></i> PAY</button>
                    </div>
                    <p class="pos-shortcuts">F2 Search &middot; F4 Clear &middot; F8 Pay &middot; F10 New Customer</p>
                </div>

            </div>
        </div>

        {{-- RIGHT: Product list (visually left) --}}
        <div class="pos-right">
            <div class="prod-header">
                <div class="prod-header-top">
                    <h3><i class="fas fa-th-large"></i> Products</h3>
                    <span class="prod-count" id="prodCountBadge">Loading…</span>
                </div>
                <div class="search-row">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-inp" id="prodSearch" placeholder="Search name, code or scan barcode…" autocomplete="off">
                        <i class="fas fa-barcode prod-search-barcode"></i>
                    </div>
                    <button class="btn-icon" title="Add Custom Product"><i class="fas fa-plus"></i></button>
                    <button class="btn-icon" title="Refresh List" onclick="loadProducts()"><i class="fas fa-redo-alt"></i></button>
                    <button type="button" class="btn-scan-phone" id="btnPhoneScan" title="Phone barcode scan" onclick="window.PhoneBarcodeScanner && PhoneBarcodeScanner.open()">
                        <i class="fas fa-camera"></i> <span>Phone Scan</span>
                    </button>

                    <div id="scannerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; flex-direction:column;">
                        <div id="qr-reader" style="width:90%; max-width:400px;"></div>
                        <button type="button" id="btnCloseScanner" class="btn btn-light mt-3">✕ Turn Of Camera </button>
                    </div>

                    <div id="productSearchDropdown" style="display:none;"></div>
                </div>
                <div class="cat-chips" id="catChips">
                    <button type="button" class="cat-chip active" data-cat="all">All</button>
                    @foreach($categories ?? [] as $cat)
                        <button type="button" class="cat-chip" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>

            <div id="cartDetailsPanel" style="display:none;">
                <div class="cdp-head">
                    <span><i class="fas fa-shopping-cart"></i> Cart Items <span id="cdpCount"></span></span>
                    <span id="cdpTotal"></span>
                </div>
                <div id="cdpList"></div>
            </div>

            <div class="prod-list" id="prodGrid">
                <div class="prod-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading products…
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
    <div class="modal" id="customerModal" tabindex="-1" role="dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1050; background:rgba(0,0,0,0.45);">
        <div class="modal-dialog modal-sm" role="document" style="margin:10vh auto;">
            <div class="modal-content customer-modal-card">
                <div class="modal-header customer-modal-head">
                    <h4 class="customer-modal-title"><i class="fas fa-user-plus"></i> Add Customer</h4>
                    <button type="button" id="customerModalCloseBtn" class="customer-modal-x">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:12px;">
                        <label class="customer-label">Name <span style="color:#e11d48;">*</span></label>
                        <input type="text" id="modalCustName" placeholder="Enter customer name">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label class="customer-label">Phone <span style="color:#e11d48;">*</span></label>
                        <input type="text" id="modalCustPhone" placeholder="Enter phone number">
                    </div>
                    <div>
                        <label class="customer-label">Address</label>
                        <textarea id="modalCustAddress" rows="2" placeholder="Enter address"></textarea>
                    </div>
                </div>
                <div class="customer-modal-foot">
                    <button type="button" id="customerModalCancelBtn" class="customer-cancel">Cancel</button>
                    <button type="button" id="btnSaveCustomer" class="customer-save"><i class="fas fa-save"></i> Save & Select</button>
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
                <button onclick="closeInvoiceModal()" class="inv-btn inv-btn-cancel"><i class="fas fa-times"></i> Cancel</button>
                <button onclick="printInvoice()" class="inv-btn inv-btn-print"><i class="fas fa-print"></i> Print</button>
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
        document.documentElement.classList.remove('dark-mode', 'dark', 'dark-theme');
        document.body.classList.remove('dark-mode', 'dark', 'dark-theme');
        document.querySelector('.content-wrapper')?.classList.remove('dark-mode', 'dark');
        document.getElementById('invoicePrintModal').addEventListener('click', function(e) { if (e.target === this) closeInvoiceModal(); });

        // Payment tiles synced to hidden select (pos.js reads #paymentMethod)
        document.querySelectorAll('.pay-tile').forEach(function(tile) {
            tile.addEventListener('click', function() {
                document.querySelectorAll('.pay-tile').forEach(function(t) { t.classList.remove('active'); });
                tile.classList.add('active');
                var sel = document.getElementById('paymentMethod');
                if (sel) sel.value = tile.getAttribute('data-method');
            });
        });

        // Category chips — filter product grid locally (no backend change)
        var activeCat = 'all';
        document.querySelectorAll('.cat-chip').forEach(function(chip) {
            chip.addEventListener('click', function() {
                document.querySelectorAll('.cat-chip').forEach(function(c) { c.classList.remove('active'); });
                chip.classList.add('active');
                activeCat = chip.getAttribute('data-cat');
                window._posCatFilter = activeCat;
                if (typeof renderProductGrid === 'function' && typeof POS !== 'undefined' && POS.products) {
                    var q = (document.getElementById('prodSearch').value || '').trim().toLowerCase();
                    var list = POS.products.slice();
                    if (activeCat !== 'all') {
                        list = list.filter(function(p) { return p.category && String(p.category.id) === String(activeCat); });
                    }
                    if (q) {
                        list = list.filter(function(p) {
                            return (p.name && p.name.toLowerCase().indexOf(q) !== -1) ||
                                (p.code && String(p.code).toLowerCase().indexOf(q) !== -1) ||
                                (p.barcode && String(p.barcode).toLowerCase().indexOf(q) !== -1);
                        });
                    }
                    renderProductGrid(list);
                    var badge = document.getElementById('prodCountBadge');
                    if (badge) badge.textContent = list.length + ' item' + (list.length === 1 ? '' : 's');
                }
            });
        });
        // Keep search filter category-aware: wrap applyFilters
        var _origApplyFilters = window.applyFilters;
        window.applyFilters = function() {
            if (typeof _origApplyFilters === 'function' && window._posCatFilter && window._posCatFilter !== 'all') {
                _origApplyFilters();
                var dd = document.getElementById('productSearchDropdown');
                if (dd && dd.style.display !== 'none') return;
            } else {
                if (typeof _origApplyFilters === 'function') _origApplyFilters();
            }
        };
    </script>
@stop
