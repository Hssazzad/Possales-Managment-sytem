@extends('adminlte::page')

@section('title', 'Point of Sale | PosSales')

@section('content_header')
@stop

@section('content')
{{-- Remove default AdminLTE padding --}}
<style>
/* ===== GOVERNMENT PROJECT THEME ===== */
body, .wrapper, .content-wrapper {
    background: #ffffff !important;
}
.content-wrapper {
    padding: 0 !important;
}
.content-header { display: none !important; }

/* ===== TOP QUICK ACTION BAR ===== */
.pos-topbar {
    background: #ffffff;
    border-bottom: 2px solid #333333;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}
.pos-topbar .qa-label {
    font-weight: 600;
    font-size: 1rem;
    color: #000000;
    margin-right: 20px;
    white-space: nowrap;
}
.pos-topbar .qa-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-qa {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
    border: 1px solid #333333;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    background: #ffffff;
    color: #000000;
}
.btn-qa:hover {
    background: #f5f5f5;
    text-decoration: none;
    border-color: #000000;
}
.btn-qa-green  { background: #ffffff; color: #000000; border-color: #333333; }
.btn-qa-purple { background: #ffffff; color: #000000; border-color: #333333; }
.btn-qa-blue   { background: #ffffff; color: #000000; border-color: #333333; }
.btn-qa-red    { background: #ffffff; color: #000000; border-color: #333333; }

/* ===== LAYOUT ===== */
.pos-layout {
    display: flex;
    height: calc(100vh - 57px);
    overflow: hidden;
}

/* ===== LEFT MAIN PANEL ===== */
.pos-main {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* ===== SALE INFO ROW ===== */
.sale-info-row {
    display: flex;
    gap: 12px;
}
.sale-info-row input[type="text"],
.sale-info-row input[type="date"] {
    flex: 1;
    padding: 10px 12px;
    border: 2px solid #333333;
    border-radius: 4px;
    font-size: 0.9rem;
    background: #ffffff;
    color: #000000;
    outline: none;
    transition: border-color 0.2s;
}
.sale-info-row input:focus { border-color: #000000; }

/* ===== CUSTOMER SEARCH ===== */
.customer-search-wrap {
    position: relative;
}
.customer-search-wrap input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #333333;
    border-radius: 4px;
    font-size: 0.92rem;
    outline: none;
    background: #ffffff;
    color: #000000;
}
.customer-select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #333333;
    border-radius: 4px;
    font-size: 0.9rem;
    background: #ffffff;
    color: #000000;
    outline: none;
    margin-top: 8px;
    appearance: auto;
}

/* ===== ADD PRODUCT ROW BUTTON ===== */
.btn-add-row {
    width: 100%;
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 12px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 4px;
    font-weight: 600;
}
.btn-add-row:hover { background: #f5f5f5; border-color: #000000; }

/* ===== CART TABLE ===== */
.cart-table-wrap {
    background: #ffffff;
    border-radius: 4px;
    overflow-x: auto;
    border: 2px solid #333333;
}
.cart-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
    font-size: 0.9rem;
}
.cart-table thead tr {
    background: #f5f5f5;
    border-bottom: 2px solid #333333;
}
.cart-table th {
    padding: 12px 12px;
    text-align: left;
    font-weight: 600;
    color: #000000;
    white-space: nowrap;
    border-right: 1px solid #333333;
}
.cart-table th:last-child {
    border-right: none;
}
.cart-table td {
    padding: 12px 12px;
    border-bottom: 1px solid #333333;
    vertical-align: middle;
    border-right: 1px solid #333333;
}
.cart-table td:last-child {
    border-right: none;
}
.cart-table tbody tr:last-child td { border-bottom: none; }
.cart-table td img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #333333;
}
.cart-table td input[type="number"] {
    width: 70px;
    padding: 6px 8px;
    border: 2px solid #333333;
    border-radius: 4px;
    font-size: 0.85rem;
    text-align: center;
    background: #ffffff;
    color: #000000;
}
.cart-table .empty-row td {
    text-align: center;
    color: #666666;
    padding: 30px;
    font-size: 0.9rem;
}
.btn-rm {
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 0.8rem;
    cursor: pointer;
    font-weight: 500;
}
.btn-rm:hover { background: #f5f5f5; border-color: #000000; }

/* ===== PAYMENT SECTION ===== */
.payment-section {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.payment-left, .payment-right {
    flex: 1 1 220px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.field-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #000000;
    margin-bottom: 6px;
}
.field-group input,
.field-group select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #333333;
    border-radius: 4px;
    font-size: 0.9rem;
    background: #ffffff;
    color: #000000;
    outline: none;
    transition: border-color 0.2s;
}
.field-group input:focus,
.field-group select:focus { border-color: #000000; }

/* ===== TOTALS ===== */
.totals-card {
    background: #ffffff;
    border-radius: 4px;
    border: 2px solid #333333;
    padding: 16px 18px;
}
.totals-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 0.9rem;
    color: #000000;
    border-bottom: 1px solid #333333;
}
.totals-row:last-child { border-bottom: none; }
.totals-row.total-main {
    font-size: 1.1rem;
    font-weight: 700;
    color: #000000;
    border-top: 2px solid #333333;
    margin-top: 8px;
    padding-top: 12px;
}
.totals-row span:last-child { font-weight: 600; }

/* ===== ACTION BUTTONS ===== */
.action-buttons {
    display: flex;
    gap: 12px;
}
.btn-cancel-pos {
    flex: 1;
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 14px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-cancel-pos:hover { background: #f5f5f5; border-color: #000000; }
.btn-save-pos {
    flex: 1;
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 14px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-save-pos:hover { background: #f5f5f5; border-color: #000000; }

/* ===== RIGHT PRODUCT PANEL ===== */
.pos-products {
    width: 350px;
    min-width: 300px;
    background: #ffffff;
    border-left: 2px solid #333333;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.pos-products-header {
    padding: 14px 16px;
    border-bottom: 2px solid #333333;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.pos-products-header .search-bar {
    display: flex;
    gap: 8px;
    width: 100%;
}
.pos-products-header .search-bar input {
    flex: 1;
    padding: 10px 12px;
    border: 2px solid #333333;
    border-radius: 4px;
    font-size: 0.85rem;
    outline: none;
    transition: border-color 0.2s;
    background: #ffffff;
    color: #000000;
}
.pos-products-header .search-bar input:focus { border-color: #000000; }
.btn-search-icon {
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 10px 14px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
}
.btn-search-icon:hover { background: #f5f5f5; border-color: #000000; }
.btn-category {
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 8px 14px;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    flex: 1;
    transition: all 0.2s;
}
.btn-category:hover { background: #f5f5f5; border-color: #000000; }
.btn-brand {
    background: #ffffff;
    color: #000000;
    border: 2px solid #333333;
    border-radius: 4px;
    padding: 8px 14px;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    flex: 1;
    transition: all 0.2s;
}
.btn-brand:hover { background: #f5f5f5; border-color: #000000; }

/* ===== PRODUCT GRID ===== */
.product-grid-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    align-content: start;
}
.product-card-new {
    background: #ffffff;
    border: 2px solid #333333;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color 0.2s;
    text-align: center;
}
.product-card-new:hover {
    border-color: #000000;
    background: #f5f5f5;
}
.product-card-new img {
    width: 100%;
    height: 80px;
    object-fit: contain;
    background: #f5f5f5;
    padding: 8px;
    border-bottom: 1px solid #333333;
}
.product-card-new .pc-body {
    padding: 8px 6px 10px;
}
.product-card-new .pc-name {
    font-size: 0.75rem;
    font-weight: 600;
    color: #000000;
    line-height: 1.2;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.product-card-new .pc-price {
    font-size: 0.8rem;
    font-weight: 700;
    color: #000000;
}

/* ===== MISC ===== */
.disc-row { display: flex; gap: 6px; }
.disc-row select { width: 120px; flex-shrink: 0; }
.vat-row { display: flex; gap: 6px; }
.vat-row select { width: 130px; flex-shrink: 0; }
.taka { font-family: inherit; }

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: #f1f1f1; }
::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

<!-- TOP BAR -->
<div class="pos-topbar">
    <span class="qa-label">Quick Action</span>
    <div class="qa-actions">
        <button onclick="window.location='{{ route('dashboard') }}'" class="btn-qa btn-qa-green">
            <i class="fas fa-list"></i> Product List
        </button>
        <button onclick="loadTodayStats()" class="btn-qa btn-qa-purple">
            <i class="fas fa-calendar-day"></i> Today Sales
        </button>
        <button onclick="openCashDrawer()" class="btn-qa btn-qa-blue">
            <i class="fas fa-calculator"></i> Calculator
        </button>
        <button onclick="window.location='{{ route('dashboard') }}'" class="btn-qa btn-qa-red">
            <i class="fas fa-power-off"></i> Dashboard
        </button>
    </div>
</div>

<!-- MAIN LAYOUT -->
<div class="pos-layout">

    <!-- LEFT PANEL -->
    <div class="pos-main">

        <!-- Sale ID + Date -->
        <div class="sale-info-row">
            <input type="text" value="S-00053" id="saleNumber" readonly style="background:#f9fafb; color:#6b7280; font-weight:600;">
            <input type="date" id="saleDate" value="{{ date('m/d/Y') }}">
        </div>

        <!-- Customer Search -->
        <div class="customer-search-wrap">
            <input type="text" id="customerSearch" placeholder="Search customers by name, phone, or type...">
        </div>

        <!-- Customer Select -->
        <select class="customer-select" id="customerSelect">
            <option value="">Select Customer</option>
            {{-- customer options if any --}}
        </select>

        <!-- Add Row Button -->
        <button class="btn-add-row" onclick="showHeldSales()">
            <i class="fas fa-plus"></i>
        </button>

        <!-- Cart Table -->
        <div class="cart-table-wrap">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Items</th>
                        <th>Code</th>
                        <th>Batch</th>
                        <th>Unit</th>
                        <th>Sale Price</th>
                        <th>Discount %</th>
                        <th>Qty</th>
                        <th>Sub Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cartItems">
                    <tr class="empty-row">
                        <td colspan="9">
                            <i class="fas fa-shopping-cart" style="font-size:1.5rem; opacity:0.3; display:block; margin-bottom:6px;"></i>
                            No items added yet
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Payment Section -->
        <div class="payment-section">
            <div class="payment-left">
                <div class="field-group">
                    <label>Receive Amount</label>
                    <input type="number" id="receiveAmount" value="0" min="0" step="0.01">
                </div>
                <div class="field-group">
                    <label>Change Amount</label>
                    <input type="number" id="changeAmount" value="0" readonly style="background:#f9fafb;">
                </div>
                <div class="field-group">
                    <label>Due Amount</label>
                    <input type="number" id="dueAmount" value="0" readonly style="background:#f9fafb;">
                </div>
                <div class="field-group">
                    <label>Payment Type</label>
                    <select id="paymentMethod">
                        <option value="cash">Cash</option>
                        <option value="bkash">Bkash</option>
                        <option value="card">Credit/Debit Card</option>
                        <option value="mobile">Mobile Payment</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
                <div class="field-group">
                    <label>Previous Paid Due</label>
                    <input type="number" id="prevPaidDue" placeholder="Enter amount..." min="0" step="0.01">
                </div>
            </div>

            <div class="payment-right">
                <!-- Totals -->
                <div class="totals-card">
                    <div class="totals-row">
                        <span>Sub Total</span>
                        <span class="taka">৳<span id="subtotal">0</span></span>
                    </div>
                    <div class="totals-row">
                        <span>Vat</span>
                        <div class="vat-row" style="gap:6px; align-items:center;">
                            <select id="vatType" style="padding:4px 6px; border:1px solid #d1d5db; border-radius:5px; font-size:0.8rem;">
                                <option value="">Select</option>
                                <option value="5">5%</option>
                                <option value="10">10%</option>
                                <option value="15">15%</option>
                            </select>
                            <span>0.00</span>
                        </div>
                    </div>
                    <div class="totals-row">
                        <span>Discount</span>
                        <div class="disc-row" style="gap:6px; align-items:center;">
                            <select id="discountType" style="padding:4px 6px; border:1px solid #d1d5db; border-radius:5px; font-size:0.8rem;">
                                <option value="flat">Flat (৳)</option>
                                <option value="percent">Percent (%)</option>
                            </select>
                            <input type="number" id="discountValue" value="0" min="0" step="0.01" style="width:60px; padding:4px 6px; border:1px solid #d1d5db; border-radius:5px; font-size:0.82rem; text-align:center;">
                        </div>
                    </div>
                    <div class="totals-row">
                        <span>Shipping Charge</span>
                        <input type="number" id="shippingCharge" value="0" min="0" step="0.01" style="width:70px; padding:4px 6px; border:1px solid #d1d5db; border-radius:5px; font-size:0.82rem; text-align:right;">
                    </div>
                    <div class="totals-row">
                        <span>Total Amount</span>
                        <span class="taka">৳<span id="totalAmount">0</span></span>
                    </div>
                    <div class="totals-row">
                        <span>Rounding(+/-)</span>
                        <span class="taka">৳<span id="rounding">0</span></span>
                    </div>
                    <div class="totals-row total-main">
                        <span>Payable Amount</span>
                        <span class="taka" style="color:#dc2626;">৳<span id="total">0</span></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn-cancel-pos" onclick="clearCart()">Cancel</button>
                    <button class="btn-save-pos" onclick="processPayment()">Save</button>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT PRODUCT PANEL -->
    <div class="pos-products">
        <div class="pos-products-header">
            <div class="search-bar">
                <input type="text" id="productSearchRight" placeholder="Search product...">
                <button class="btn-search-icon"><i class="fas fa-search"></i></button>
            </div>
            <button class="btn-category"><i class="fas fa-th-large"></i> Category</button>
            <button class="btn-brand"><i class="fas fa-tag"></i> Brand</button>
        </div>

        <div class="product-grid-scroll" id="productGrid">
            @foreach($products as $product)
            <div class="product-card-new" onclick="selectProduct({{ $product->id }})">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                <div class="pc-body">
                    <div class="pc-name">{{ $product->name }}</div>
                    <div class="pc-price">৳{{ number_format($product->price, 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Product Modal (unchanged logic) -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Cart</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" id="modalQuantity" class="form-control" value="1" min="1">
                </div>
                <div class="form-group">
                    <label>Price:</label>
                    <input type="number" id="modalPrice" class="form-control" step="0.01" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" onclick="addToCartFromModal()" class="btn btn-primary">Add to Cart</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
let cart = [];
let currentProduct = null;
let allProducts = @json($products);

// Initialize POS
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
    loadTodayStats();

    document.querySelectorAll('[data-category]').forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            loadProductsByCategory(category);
        });
    });

    // Right panel search - improved functionality
    document.getElementById('productSearchRight').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        if (term.length >= 2) {
            searchProducts(term);
        } else {
            // Show all products when search is empty
            displayProducts(allProducts, 'all');
        }
    });

    // Load all customers on page load
    loadAllCustomers();

    // Customer search - improved functionality
    document.getElementById('customerSearch').addEventListener('input', function() {
        const term = this.value;
        if (term.length >= 2) {
            searchCustomers(term);
        } else {
            loadAllCustomers();
        }
    });

    // Load all customers function
    function loadAllCustomers() {
        fetch('/admin/customers/search')
            .then(response => response.json())
            .then(customers => {
                displayCustomerResults(customers);
            })
            .catch(error => {
                console.error('Error loading customers:', error);
            });
    }

    // Receive amount -> change calc
    document.getElementById('receiveAmount').addEventListener('input', calcChange);
    document.getElementById('discountValue').addEventListener('input', updateTotals);
    document.getElementById('shippingCharge').addEventListener('input', updateTotals);
    document.getElementById('vatType').addEventListener('change', updateTotals);
    document.getElementById('discountType').addEventListener('change', updateTotals);
});

function calcChange() {
    const receive = parseFloat(document.getElementById('receiveAmount').value) || 0;
    const total = parseFloat(document.getElementById('total').textContent) || 0;
    const change = receive - total;
    document.getElementById('changeAmount').value = change >= 0 ? change.toFixed(2) : '0.00';
    document.getElementById('dueAmount').value = change < 0 ? Math.abs(change).toFixed(2) : '0.00';
}

function loadProductsByCategory(category) {
    const search = document.getElementById('productSearchRight').value || '';
    fetch(`/admin/products/pos-search?category=${category}&search=${search}`)
        .then(response => response.json())
        .then(products => { displayProducts(products, category); })
        .catch(error => { console.error('Error loading products:', error); });
}

function searchProducts(searchTerm) {
    fetch(`/admin/products/pos-search?search=${searchTerm}`)
        .then(response => response.json())
        .then(products => { displayProducts(products, 'search'); })
        .catch(error => { console.error('Error searching products:', error); });
}

function displayProducts(products, category) {
    const grid = document.getElementById('productGrid');
    if (!grid) return;
    grid.innerHTML = '';
    products.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card-new';
        card.onclick = () => selectProduct(product.id);
        card.innerHTML = `
            <img src="${product.image_url}" alt="${product.name}">
            <div class="pc-body">
                <div class="pc-name">${product.name}</div>
                <div class="pc-price">৳${parseFloat(product.sale_price || product.price).toFixed(2)}</div>
            </div>
        `;
        grid.appendChild(card);
    });
}

function selectProduct(productId) {
    currentProduct = allProducts.find(p => p.id === productId);
    if (currentProduct) {
        // Check if customer is selected and show flash message
        const customerSelect = document.getElementById('customerSelect');
        const selectedCustomer = customerSelect.options[customerSelect.selectedIndex];

        if (customerSelect.value && selectedCustomer.text) {
            const customerName = selectedCustomer.text.split(' - ')[0];
            showFlashMessage(`Customer: ${customerName}`);
        }

        // Add product directly to cart with quantity 1
        addProductToCart(currentProduct, 1);
    }
}

function addProductToCart(product, quantity = 1) {
    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            code: product.code || '-',
            batch: product.batch || '-',
            unit: product.unit || 'pcs',
            price: parseFloat(product.sale_price || product.price),
            quantity: quantity,
            discount: parseFloat(product.discount_percentage || 0),
            tax_rate: parseFloat(product.tax_rate || 0),
            image_url: product.image_url
        });
    }
    updateCartDisplay();
}

function addToCartFromModal() {
    if (!currentProduct) return;
    const quantity = parseInt(document.getElementById('modalQuantity').value);
    addProductToCart(currentProduct, quantity);
    $('#productModal').modal('hide');
}

function updateCartDisplay() {
    const cartBody = document.getElementById('cartItems');

    if (cart.length === 0) {
        cartBody.innerHTML = `<tr class="empty-row"><td colspan="9"><i class="fas fa-shopping-cart" style="font-size:1.5rem; opacity:0.3; display:block; margin-bottom:6px;"></i>No items added yet</td></tr>`;
    } else {
        let html = '';
        cart.forEach((item, index) => {
            const subtotal = (item.price * item.quantity * (1 - (item.discount || 0) / 100)).toFixed(2);
            html += `
                <tr>
                    <td>
                        <img src="${item.image_url}" alt="${item.name}" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #333333; margin-right:8px;">
                        <strong>${item.name}</strong>
                    </td>
                    <td>${item.code || '-'}</td>
                    <td>${item.batch || '-'}</td>
                    <td>${item.unit || 'pcs'}</td>
                    <td>(${item.price.toFixed(2)})</td>
                    <td>
                        <input type="number" value="${item.discount || 0}" min="0" max="100"
                            onchange="updateDiscount(${index}, this.value)"
                            style="width:70px; padding:6px 8px; border:2px solid #333333; border-radius:4px; text-align:center; background:#ffffff; color:#000000;">
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <button onclick="decrementQty(${index})" style="padding:4px 8px; border:2px solid #333333; background:#ffffff; color:#000000; border-radius:4px; cursor:pointer; font-weight:600;">-</button>
                            <input type="number" value="${item.quantity}" min="1"
                                onchange="updateQty(${index}, this.value)"
                                style="width:60px; padding:6px 8px; border:2px solid #333333; border-radius:4px; text-align:center; background:#ffffff; color:#000000;">
                            <button onclick="incrementQty(${index})" style="padding:4px 8px; border:2px solid #333333; background:#ffffff; color:#000000; border-radius:4px; cursor:pointer; font-weight:600;">+</button>
                        </div>
                    </td>
                    <td><strong>(${subtotal})</strong></td>
                    <td>
                        <button onclick="removeFromCart(${index})" class="btn-rm" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        cartBody.innerHTML = html;
    }
    updateTotals();
}

function incrementQty(index) {
    cart[index].quantity += 1;
    updateTotals();
    updateCartDisplay();
}

function decrementQty(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity -= 1;
        updateTotals();
        updateCartDisplay();
    }
}

function updateQty(index, val) {
    const newQty = parseInt(val) || 1;
    if (newQty >= 1) {
        cart[index].quantity = newQty;
        updateTotals();
        updateCartDisplay();
    }
}

function updateDiscount(index, val) {
    const discount = parseFloat(val) || 0;
    if (discount >= 0 && discount <= 100) {
        cart[index].discount = discount;
        updateTotals();
        updateCartDisplay();
    }
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => {
        const itemTotal = item.price * item.quantity;
        const itemDiscount = (item.discount || 0) / 100;
        return sum + (itemTotal * (1 - itemDiscount));
    }, 0);

    const originalSubtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const itemDiscounts = originalSubtotal - subtotal;

    const vatRate = parseFloat(document.getElementById('vatType').value) || 0;
    const vatAmount = subtotal * (vatRate / 100);

    const discType = document.getElementById('discountType').value;
    const discVal = parseFloat(document.getElementById('discountValue').value) || 0;
    const additionalDiscount = discType === 'percent' ? subtotal * (discVal / 100) : discVal;
    const totalDiscount = itemDiscounts + additionalDiscount;

    const shipping = parseFloat(document.getElementById('shippingCharge').value) || 0;
    const total = subtotal + vatAmount - additionalDiscount + shipping;
    const rounded = Math.round(total);
    const rounding = (rounded - total).toFixed(2);

    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('rounding').textContent = rounding;
    document.getElementById('total').textContent = rounded.toFixed(2);

    calcChange();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function clearCart() {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCartDisplay();
    }
}

function processPayment() {
    if (cart.length === 0) {
        alert('Please add items to cart first');
        return;
    }
    const paymentMethod = document.getElementById('paymentMethod').value;
    const total = document.getElementById('total').textContent;
    const customerSelect = document.getElementById('customerSelect');
    const selectedCustomer = customerSelect.options[customerSelect.selectedIndex];

    const saleData = {
        items: cart,
        payment_method: paymentMethod,
        total_amount: parseFloat(total),
        customer_id: customerSelect.value || null,
        customer_name: selectedCustomer.text ? selectedCustomer.text.split(' - ')[0] : '',
        customer_phone: selectedCustomer.dataset ? selectedCustomer.dataset.phone : '',
        notes: ''
    };
    fetch('/pos/process-sale', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(saleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Payment processed successfully! Invoice: ${data.invoice_number}`);
            cart = [];
            updateCartDisplay();
            loadTodayStats();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error processing payment:', error);
        alert('Error processing payment');
    });
}

function holdSale() {
    if (cart.length === 0) { alert('No items to hold'); return; }
    const holdData = {
        items: cart,
        customer_name: prompt('Customer name (optional):') || '',
        customer_phone: prompt('Customer phone (optional):') || ''
    };
    fetch('/pos/hold-sale', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(holdData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Sale held successfully! Hold ID: ${data.hold_id}`);
            cart = [];
            updateCartDisplay();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error holding sale:', error);
        alert('Error holding sale');
    });
}

function showHeldSales() {
    fetch('/pos/held-sales')
        .then(response => response.json())
        .then(heldSales => {
            if (heldSales.length === 0) { alert('No held sales found'); return; }
            let holdList = 'Held Sales:\n\n';
            heldSales.forEach((sale, index) => {
                holdList += `${index + 1}. ${sale.customer_name || 'No name'} - ${sale.total_amount} (${sale.created_at})\n`;
                holdList += `   Hold ID: ${sale.hold_token}\n\n`;
            });
            const holdId = prompt('Enter Hold ID to resume:\n\n' + holdList);
            if (holdId) { resumeSale(holdId); }
        })
        .catch(error => {
            console.error('Error loading held sales:', error);
            alert('Error loading held sales');
        });
}

function resumeSale(holdId) {
    fetch(`/pos/resume/${holdId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cart = data.items;
                updateCartDisplay();
                alert('Sale resumed successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error resuming sale:', error);
            alert('Error resuming sale');
        });
}

function startNewSale() {
    if (cart.length > 0) {
        if (!confirm('Clear current cart and start new sale?')) { return; }
    }
    cart = [];
    updateCartDisplay();
}

function openCashDrawer() {
    fetch('/pos/open-cash-drawer', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? 'Cash drawer opened!' : 'Error opening cash drawer');
    })
    .catch(error => {
        console.error('Error opening cash drawer:', error);
        alert('Error opening cash drawer');
    });
}

function searchCustomers(term) {
    fetch(`/admin/customers/search?search=${term}`)
        .then(response => response.json())
        .then(customers => {
            displayCustomerResults(customers);
        })
        .catch(error => {
            console.error('Error searching customers:', error);
        });
}

function displayCustomerResults(customers) {
    const select = document.getElementById('customerSelect');
    select.innerHTML = '<option value="">Select Customer</option>';

    customers.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = `${customer.name} - ${customer.phone || 'No phone'}`;
        option.dataset.phone = customer.phone;
        option.dataset.due = customer.due_amount || 0;
        select.appendChild(option);
    });
}

function showFlashMessage(message) {
    // Create flash message element
    const flashDiv = document.createElement('div');
    flashDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ffffff;
        border: 2px solid #333333;
        color: #000000;
        padding: 12px 20px;
        border-radius: 4px;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;
    flashDiv.textContent = message;
    document.body.appendChild(flashDiv);

    // Remove after 3 seconds
    setTimeout(() => {
        if (flashDiv.parentNode) {
            flashDiv.parentNode.removeChild(flashDiv);
        }
    }, 3000);
}

function clearCustomerResults() {
    const select = document.getElementById('customerSelect');
    select.innerHTML = '<option value="">Select Customer</option>';
}

function loadTodayStats() {
    fetch('/pos/stats')
        .then(response => response.json())
        .then(stats => { /* stats loaded */ })
        .catch(error => { console.error('Error loading stats:', error); });
}

// Left panel customer search also triggers product search
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('productSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value;
        const activeCategory = 'all';
        fetch(`/pos/search-products?category=${activeCategory}&search=${searchTerm}`)
            .then(response => response.json())
            .then(products => { displayProducts(products, activeCategory); })
            .catch(error => { console.error('Error searching products:', error); });
    });
});
</script>
@stop

@section('adminlte_css')
<style>
/* Hide default AdminLTE content padding */
.content { padding: 0 !important; }
</style>
@stop
