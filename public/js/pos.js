'use strict';

/* --------------------------------------
   STATE
-------------------------------------- */
var POS = {
    cart:              [],
    products:          [],
    customers:         [],
    selected:          null,
    csrfToken:         '',
    customerId:        '',
    customerName:      '',
    customerPhone:     '',
    customerAddress:   '',
    pendingSaleAfterCustomer: false,
};

var customerDataMap = {};

/* --------------------------------------
   PRECISION HELPERS
-------------------------------------- */
function round2(num) {
    return Math.round((num + Number.EPSILON) * 100) / 100;
}
function parseFloat2(str) {
    return round2(parseFloat(str) || 0);
}

/* --------------------------------------
   STOCK VALIDATION
-------------------------------------- */
function getProductStock(productId, variationId) {
    var product = POS.products.find(function (p) { return p.id == productId; });
    if (!product) return 0;
    if (variationId) {
        var variation = product.variations.find(function (v) { return v.id == variationId; });
        return variation ? (parseInt(variation.stock) || 0) : 0;
    }
    return parseInt(product.stock_quantity) || 0;
}
function getCartItemQty(productId, variationId) {
    var cartKey  = variationId ? (productId + '_' + variationId) : String(productId);
    var existing = POS.cart.find(function (item) { return item.cartKey === cartKey; });
    return existing ? existing.qty : 0;
}
function checkStock(productId, variationId, requestedQty) {
    var stock       = getProductStock(productId, variationId);
    var inCart      = getCartItemQty(productId, variationId);
    var totalNeeded = inCart + requestedQty;
    if (totalNeeded > stock) {
        var available = stock - inCart;
        if (available <= 0) {
            showToast('Insufficient stock! Already have ' + inCart + ' in cart, stock is ' + stock, true);
        } else {
            showToast('Only ' + available + ' more item(s) available in stock', true);
        }
        return false;
    }
    return true;
}
/* --------------------------------------
   SCAN RESOLUTION (deterministic)
   Product Code and Barcode are the ONLY product identifiers.
   Variation codes, unit codes and SKU must never resolve a product,
   otherwise one value (e.g. BB12) can match two different products.
   Priority matches the server resolver in PosController:
     1. exact product barcode
     2. exact product code
-------------------------------------- */
function findProductByScanCode(term) {
    var value = String(term || '').toLowerCase();
    if (!value) return null;

    // 1. exact product barcode
    var byBarcode = POS.products.find(function(p) {
        return p.barcode && String(p.barcode).toLowerCase() === value;
    });
    if (byBarcode) return byBarcode;

    // 2. exact product code
    return POS.products.find(function(p) {
        return p.code && String(p.code).toLowerCase() === value;
    }) || null;
}

/* Add a product resolved by an exact scan match. */
function addScannedProduct(product) {
    if (!product) return;

    var hasVars = Array.isArray(product.variations) && product.variations.length > 0 &&
        !(product.variations.length === 1 && product.variations[0].variation_value === 'Standard');

    if (hasVars) {
        var firstInStock = product.variations.find(function(v) { return (parseInt(v.stock) || 0) > 0; });
        var varToAdd = firstInStock || product.variations[0];
        if (!checkStock(product.id, varToAdd.id, 1)) return;
        addToCart(product, varToAdd);
    } else {
        if (!checkStock(product.id, null, 1)) return;
        addToCart(product, null);
    }
}

/* --------------------------------------
   KEYBOARD SHORTCUTS
-------------------------------------- */
function handleKeyboardShortcuts(e) {
    if (e.key === 'F2')  { e.preventDefault(); document.getElementById('prodSearch').focus(); return; }
    if (e.key === 'F4')  { e.preventDefault(); clearCart(); return; }
    if (e.key === 'F8')  { e.preventDefault(); processSaleAjax(); return; }
    if (e.key === 'F10') { e.preventDefault(); openCustomerModal(); return; }
    if (e.key === 'Escape') {
        var modal = document.getElementById('varModal');
        if (modal && modal.classList.contains('open')) { e.preventDefault(); closeModal(); return; }
        closePsd();
        return;
    }
    if (e.key === 'Enter' && e.target.id === 'prodSearch') { e.preventDefault(); handleBarcodeEnter(); return; }
}

function handleBarcodeEnter() {
    var inp = document.getElementById('prodSearch');
    if (!inp) return;
    var raw = String(inp.value || '').trim();
    if (!raw) return;

    // STEP 1: resolve locally — exact product barcode, then exact product code.
    var local = findProductByScanCode(raw);
    if (local) {
        inp.value = '';
        closePsd();
        addScannedProduct(local);
        return;
    }

    // STEP 2: ask the server, which uses the same deterministic priority
    // (products.barcode then products.code) so client and server always agree.
    fetch('/pos/get-product/' + encodeURIComponent(raw), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(r) {
            return r.json().then(function(data) { return { ok: r.ok, data: data }; });
        })
        .then(function(res) {
            if (!res.ok || !res.data || !res.data.success || !res.data.product) {
                showToast('Product not found: ' + raw, true);
                return;
            }

            var serverProduct = res.data.product;
            var existing = POS.products.find(function(x) { return x.id == serverProduct.id; });
            if (!existing) {
                POS.products.push(serverProduct);
                existing = serverProduct;
            }

            inp.value = '';
            closePsd();
            addScannedProduct(existing);
        })
        .catch(function() {
            showToast('Search failed', true);
        });
}

/* --------------------------------------
   INIT
-------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    POS.csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    loadProducts();
    initCustomerSearch();

    var searchInp = document.getElementById('prodSearch');
    if (searchInp) {
        searchInp.focus();
        document.addEventListener('click', function (e) {
            if (!['INPUT','SELECT','TEXTAREA','BUTTON','A'].includes(e.target.tagName)) searchInp.focus();
        });
    }

    var receiveAmount = document.getElementById('receiveAmount');
    if (receiveAmount) receiveAmount.addEventListener('input', calcChange);
    var vatType = document.getElementById('vatType');
    if (vatType) vatType.addEventListener('change', updateTotals);
    var discountType = document.getElementById('discountType');
    if (discountType) discountType.addEventListener('change', updateTotals);
    var discountValue = document.getElementById('discountValue');
    if (discountValue) discountValue.addEventListener('input', updateTotals);

    var btnClear = document.getElementById('btnClear');
    if (btnClear) btnClear.addEventListener('click', clearCart);
    var btnPay = document.getElementById('btnPay');
    if (btnPay) btnPay.addEventListener('click', processSaleAjax);
    var prodSearch = document.getElementById('prodSearch');
    if (prodSearch) prodSearch.addEventListener('input', applyFilters);

    // ★ NOTUN: Phone camera scanner modal-er close button ★
    var btnCloseScanner = document.getElementById('btnCloseScanner');
    if (btnCloseScanner) btnCloseScanner.addEventListener('click', function() {
        window.PhoneBarcodeScanner && window.PhoneBarcodeScanner.close();
    });

    document.addEventListener('click', function (e) {
        if (psdDropdown && psdInput) {
            if (!psdDropdown.contains(e.target) && e.target !== psdInput && !e.target.closest('.search-row')) {
                closePsd();
            }
        }
    });

    document.addEventListener('keydown', handleKeyboardShortcuts);

    var varModalClose  = document.getElementById('varModalClose');
    if (varModalClose)  varModalClose.addEventListener('click', closeModal);
    var varModalCancel = document.getElementById('varModalCancel');
    if (varModalCancel) varModalCancel.addEventListener('click', closeModal);
    var varModalAdd    = document.getElementById('varModalAdd');
    if (varModalAdd)    varModalAdd.addEventListener('click', confirmVariation);
    var varModal = document.getElementById('varModal');
    if (varModal) varModal.addEventListener('click', function (e) { if (e.target === this) closeModal(); });

    renderCart();
});

/* --------------------------------------
   CUSTOMER SEARCH
-------------------------------------- */
function initCustomerSearch() {
    POS.customers = [];
    fetch('/admin/customers/search?search=')
        .then(function (res) { return res.json(); })
        .then(function (data) { POS.customers = Array.isArray(data) ? data : []; })
        .catch(function () { console.log('Failed to load customers'); });

    var searchInp = document.getElementById('customerSearch');
    var resultsDiv = document.getElementById('customerSearchResults');

    if (searchInp) {
        searchInp.addEventListener('input', function () {
            var term = this.value.trim().toLowerCase();
            if (!term) {
                resultsDiv.style.display = 'none';
                selectCustomer('', '', '', '');
                return;
            }
            var filtered = POS.customers.filter(function (c) {
                return (c.name && c.name.toLowerCase().indexOf(term) !== -1) ||
                    (c.phone && c.phone.toLowerCase().indexOf(term) !== -1);
            });
            renderCustomerResults(filtered);
        });
        searchInp.addEventListener('focus', function () {
            if (this.value.trim()) { resultsDiv.style.display = 'block'; }
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.customer-search-group')) {
            resultsDiv.style.display = 'none';
        }
    });

    var btnAdd = document.getElementById('btnAddCustomer');
    if (btnAdd) btnAdd.addEventListener('click', openCustomerModal);

    var walkingBadge = document.getElementById('walkingCustomerBadge');
    if (walkingBadge) walkingBadge.addEventListener('click', openCustomerModal);

    var btnSave = document.getElementById('btnSaveCustomer');
    if (btnSave) btnSave.addEventListener('click', saveCustomerAjax);
    var btnClose = document.getElementById('customerModalCloseBtn');
    if (btnClose) btnClose.addEventListener('click', closeCustomerModal);
    var btnCancel = document.getElementById('customerModalCancelBtn');
    if (btnCancel) btnCancel.addEventListener('click', closeCustomerModal);
    var modal = document.getElementById('customerModal');
    if (modal) modal.addEventListener('click', function (e) { if (e.target === this) closeCustomerModal(); });
}

function renderCustomerResults(customers) {
    var resultsDiv = document.getElementById('customerSearchResults');
    if (!customers.length) {
        resultsDiv.innerHTML = '<div class="no-results">No customers found. Click + to add new.</div>';
        resultsDiv.style.display = 'block';
        return;
    }
    var html = '';
    customers.forEach(function (c) {
        html += '<div class="result-item" data-id="' + c.id + '" data-name="' + escHtml(c.name || '') + '" data-phone="' + escHtml(c.phone || '') + '" data-address="' + escHtml(c.address || '') + '">' +
            '<div class="cust-name">' + escHtml(c.name) + '</div>' +
            '<div class="cust-phone">' + escHtml(c.phone || 'No phone') + '</div>' +
            '</div>';
    });
    resultsDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
    resultsDiv.querySelectorAll('.result-item').forEach(function (el) {
        el.addEventListener('click', function () {
            selectCustomer(
                this.getAttribute('data-id'),
                this.getAttribute('data-name'),
                this.getAttribute('data-phone'),
                this.getAttribute('data-address')
            );
        });
    });
}

function selectCustomer(id, name, phone, address) {
    POS.customerId = id || '';
    POS.customerName = name || '';
    POS.customerPhone = phone || '';
    POS.customerAddress = address || '';
    var custIdField = document.getElementById('customerId');
    if (custIdField) custIdField.value = id || '';
    var searchInp = document.getElementById('customerSearch');
    if (searchInp) searchInp.value = name || '';
    var resultsDiv = document.getElementById('customerSearchResults');
    if (resultsDiv) resultsDiv.style.display = 'none';

    var badge = document.getElementById('walkingCustomerBadge');
    if (id && name) {
        badge.innerHTML = '<i class="fas fa-user"></i> ' + escHtml(name);
        badge.style.background = '#e3f2fd';
        badge.style.borderColor = '#2196f3';
        badge.style.color = '#1565c0';
    } else {
        badge.innerHTML = '<i class="far fa-user"></i> No Customer <span style="color:#dc2626;">*</span>';
        badge.style.background = '#fffbeb';
        badge.style.borderColor = '#f59e0b';
        badge.style.color = '#b45309';
    }
}

function openCustomerModal() {
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#customerModal').modal('show');
    } else {
        var modal = document.getElementById('customerModal');
        if (modal) {
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }
    setTimeout(function () { var nm = document.getElementById('modalCustName'); if (nm) nm.focus(); }, 150);
}

function closeCustomerModal() {
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#customerModal').modal('hide');
    } else {
        var modal = document.getElementById('customerModal');
        if (modal) { modal.style.display = 'none'; document.body.classList.remove('modal-open'); }
    }
    var nm = document.getElementById('modalCustName'); if (nm) nm.value = '';
    var ph = document.getElementById('modalCustPhone'); if (ph) ph.value = '';
    var ad = document.getElementById('modalCustAddress'); if (ad) ad.value = '';
}

function saveCustomerAjax() {
    var name = document.getElementById('modalCustName').value.trim();
    var phone = document.getElementById('modalCustPhone').value.trim();
    var address = document.getElementById('modalCustAddress').value.trim();

    if (!name) { showToast('Customer name is required', true); document.getElementById('modalCustName').focus(); return; }
    if (!phone) { showToast('Customer phone is required', true); document.getElementById('modalCustPhone').focus(); return; }

    var btn = document.getElementById('btnSaveCustomer');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    fetch('/admin/customers', {        //routes file Route/customer for saving customer
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': POS.csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ name: name, phone: phone, address: address })
    })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
            if (result.ok && result.data.success) {
                var cust = result.data.customer || {};
                var newCust = {
                    id: cust.id || 0,
                    name: cust.name || name,
                    phone: cust.phone || phone,
                    address: cust.address || address
                };
                POS.customers.unshift(newCust);
                selectCustomer(String(newCust.id), newCust.name, newCust.phone, newCust.address);
                showToast('Customer saved and selected', false);
                closeCustomerModal();
            } else {
                var msg = (result.data && result.data.message) ? result.data.message : 'Failed to save customer';
                if (result.data && result.data.errors) {
                    msg = Object.values(result.data.errors).flat().join(' ');
                }
                showToast(msg, true);
            }
        })
        .catch(function (err) { console.error(err); showToast('Network error saving customer', true); })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save & Select';
        });
}

function isCustomerSelected() { return !!document.getElementById('customerId').value; }

function highlightCustomerField() {
    var inp = document.getElementById('customerSearch');
    if (!inp) return;
    inp.style.borderColor = '#dc2626';
    inp.style.boxShadow = '0 0 0 3px rgba(220,38,38,.15)';
    inp.focus();
    setTimeout(function () { inp.style.borderColor = ''; inp.style.boxShadow = ''; }, 1200);
}

/* --------------------------------------
   PRODUCTS
-------------------------------------- */
function loadProducts() {
    fetch('/admin/products/pos-search')
        .then(function (res) { return res.json(); })
        .then(function (data) { POS.products = Array.isArray(data) ? data : []; renderProductGrid(POS.products); })
        .catch(function () { showToast('Failed to load products', true); });
}

// ── Product Search Dropdown ──
var psdDropdown = document.getElementById('productSearchDropdown');
var psdInput    = document.getElementById('prodSearch');
var psdTimer    = null;

function closePsd() {
    if (psdDropdown) psdDropdown.style.display = 'none';
}

function renderPsdItems(products) {
    if (!psdDropdown) return;
    if (!products || products.length === 0) {
        psdDropdown.innerHTML = '<div class="psd-empty">কোনো পণ্য পাওয়া যায়নি</div>';
        psdDropdown.style.display = 'block';
        return;
    }
    var html = '';
    products.forEach(function(p) {
        var imgSrc = p.image ? p.image : '';
        var imgHtml = imgSrc
            ? '<img class="psd-img" src="'+imgSrc+'" onerror="this.src=\'\';this.style.background=\'#eee\'">'
            : '<div class="psd-img" style="display:flex;align-items:center;justify-content:center;color:#ccc;font-size:20px">?</div>';
        var stockBadge = (p.stock > 0)
            ? '<span class="psd-stock-ok">Stock: '+p.stock+'</span>'
            : '<span class="psd-stock-out">Stock Out</span>';

        // Build variation list
        var varHtml = '';
        var fullProduct = POS.products.find(function(pr) { return pr.id == p.id; });
        if (fullProduct && Array.isArray(fullProduct.variations) && fullProduct.variations.length > 0) {
            fullProduct.variations.forEach(function(v) {
                var vStock = parseInt(v.stock) || 0;
                var vPrice = parseFloat(v.price || 0).toFixed(2);
                var vStockBadge = vStock > 0
                    ? '<span class="psd-stock-ok">'+vStock+'</span>'
                    : '<span class="psd-stock-out">Out</span>';
                varHtml += '<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:1px solid #f1f5f9;font-size:11px;">' +
                    '<span style="color:#000000;font-weight:700;">'+escHtml(v.variation_value)+'</span>' +
                    '<span style="display:flex;align-items:center;gap:6px;">' +
                    vStockBadge +
                    '<span style="color:#000000;font-weight:800;">BDT'+vPrice+'</span>' +
                    '</span>' +
                    '</div>';
            });
        }

        html += '<div class="psd-item" data-id="'+p.id+'" data-barcode="'+(p.barcode||'')+'">' +
            imgHtml +
            '<div class="psd-info" style="flex:1;">' +
            '<div class="psd-name">'+escHtml(p.name)+'</div>' +
            '<div class="psd-meta">'+
            (p.code ? 'Code: '+p.code+' &nbsp;' : '')+
            (p.barcode ? '| Barcode: '+p.barcode : '') +
            '&nbsp;' + stockBadge +
            '</div>' +
            (varHtml ? '<div style="margin-top:4px;">'+varHtml+'</div>' : '') +
            '</div>' +
            '<div class="psd-price">BDT'+parseFloat(p.price||0).toFixed(2)+'</div>' +
            '</div>';
    });
    psdDropdown.innerHTML = html;
    psdDropdown.style.display = 'block';

    // Click to add product
    psdDropdown.querySelectorAll('.psd-item').forEach(function(el) {
        el.addEventListener('click', function() {
            var productId = el.getAttribute('data-id');
            var product = POS.products.find(function(p) { return p.id == productId; });
            if (product) {
                onProductClick(product.id);
            }
            if (psdInput) psdInput.value = '';
            closePsd();
        });
    });
}

function applyFilters() {
    var q = psdInput ? psdInput.value.trim() : '';
    clearTimeout(psdTimer);
    if (q.length < 1) { closePsd(); return; }

    var term = q.toLowerCase();
    var filtered = POS.products.filter(function(p) {
        if ((p.name    && p.name.toLowerCase().indexOf(term) !== -1) ||
            (p.code    && p.code.toLowerCase().indexOf(term) !== -1) ||
            (p.barcode && p.barcode.toLowerCase().indexOf(term) !== -1) ||
            (p.sku     && p.sku.toLowerCase().indexOf(term) !== -1)) {
            return true;
        }
        // Variations are identified by their value only — they never own a
        // product-style code or barcode.
        if (Array.isArray(p.variations)) {
            return p.variations.some(function(v) {
                return v.variation_value && String(v.variation_value).toLowerCase().indexOf(term) !== -1;
            });
        }
        return false;
    });

    renderPsdItems(filtered.map(function(p) {
        return {
            id:      p.id,
            name:    p.name,
            code:    p.code,
            barcode: p.barcode,
            price:   p.sale_price || p.price || 0,
            stock:   p.stock_quantity || 0,
            image:   p.image_url || ''
        };
    }));
}

function renderProductGrid(products) {
    var grid = document.getElementById('prodGrid');
    var badge = document.getElementById('prodCountBadge');
    if (badge) badge.textContent = products.length + ' item' + (products.length === 1 ? '' : 's');
    if (!products.length) {
        grid.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;font-weight:600;"><i class="fas fa-box-open fa-2x" style="display:block;margin-bottom:10px;opacity:0.4;"></i>No products found</div>';
        return;
    }
    var html = '';
    products.forEach(function (p) {
        var price      = parseFloat2(p.sale_price || p.price || 0).toFixed(2);
        var hasVars    = Array.isArray(p.variations) && p.variations.length > 0;
        var totalStock = hasVars
            ? p.variations.reduce(function (s, v) { return s + (parseInt(v.stock) || 0); }, 0)
            : (parseInt(p.stock_quantity) || 0);
        var stockOut   = totalStock <= 0;

        var categoryName = (p.category && p.category.name) ? p.category.name : '';
        var brandName    = (p.brand && p.brand.name) ? p.brand.name : '';

        var stockBadge = stockOut
            ? '<span style="font-size:11px;font-weight:800;background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;border:1px solid #fca5a5;">Stock Out</span>'
            : '<span style="font-size:11px;font-weight:800;background:#dcfce7;color:#14532d;padding:2px 8px;border-radius:20px;border:1px solid #86efac;">Stock: ' + totalStock + '</span>';

        var imgUrl = p.image_url || '';
        var isPlaceholder = !imgUrl || imgUrl.indexOf('no-image') > -1 || imgUrl.indexOf('no-product') > -1;
        var initial = escHtml((p.name || 'P').charAt(0).toUpperCase());
        var grads = [
            'linear-gradient(135deg,#2563eb,#1d4ed8)',
            'linear-gradient(135deg,#0d9488,#0f766e)',
            'linear-gradient(135deg,#7c3aed,#6d28d9)',
            'linear-gradient(135deg,#d97706,#b45309)',
            'linear-gradient(135deg,#e11d48,#be123c)'
        ];
        var grad = grads[(p.id || 0) % grads.length];
        var imgHtml = isPlaceholder
            ? '<div style="width:52px;height:52px;border-radius:12px;background:' + grad + ';color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;flex-shrink:0;box-shadow:0 2px 8px rgba(15,23,42,.15);">' + initial + '</div>'
            : '<img class="product-thumb" src="' + escHtml(imgUrl) + '" data-initial="' + initial + '" data-gradient="' + grad + '" style="width:52px;height:52px;object-fit:cover;border-radius:12px;border:1px solid #e8edf3;flex-shrink:0;box-shadow:0 1px 3px rgba(15,23,42,.06);">';

        html += '<div class="prod-list-item' + (stockOut ? ' out-of-stock' : '') + '" onclick="onProductClick(' + p.id + ')">' +
            '<div onclick="event.stopPropagation();openProdDetails(' + p.id + ')" style="flex-shrink:0;cursor:pointer;" title="View Details">' + imgHtml + '</div>' +
            '<div style="flex:1;min-width:0;">' +
            '<div style="display:flex;align-items:center;gap:7px;margin-bottom:4px;flex-wrap:wrap;">' +
            '<span class="prod-name" style="font-size:13.5px;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;" title="' + escHtml(p.name) + '">' + escHtml(p.name) + '</span>' +
            (categoryName ? '<span style="font-size:10px;font-weight:800;color:#64748b;background:#f1f5f9;border:1px solid #e8edf3;padding:1px 7px;border-radius:99px;text-transform:uppercase;">' + escHtml(categoryName) + '</span>' : '') +
            (brandName ? '<span style="font-size:10px;font-weight:800;color:#7c3aed;background:#f5f3ff;border:1px solid #ede9fe;padding:1px 7px;border-radius:99px;text-transform:uppercase;">' + escHtml(brandName) + '</span>' : '') +
            '</div>' +
            '<div style="display:flex;align-items:center;gap:9px;">' +
            '<span class="prod-price" style="font-size:15px;font-weight:900;color:#2563eb;">BDT' + price + '</span>' +
            stockBadge +
            '</div>' +
            '</div>' +
            '<div style="display:flex;gap:6px;flex-shrink:0;align-items:center;">' +
            '<button type="button" onclick="event.stopPropagation();openProdDetails(' + p.id + ')" style="background:#f1f5f9;color:#475569;border:1.5px solid #e8edf3;border-radius:10px;padding:8px 10px;font-size:13px;font-weight:800;cursor:pointer;" title="Details"><i class=\'fas fa-info\'></i></button>' +
            '<button type="button" style="background:' + (stockOut ? '#e2e8f0' : 'linear-gradient(135deg,#2563eb,#1d4ed8)') + ';color:' + (stockOut ? '#64748b' : '#ffffff') + ';border:none;border-radius:10px;padding:8px 15px;font-size:12.5px;font-weight:800;cursor:' + (stockOut ? 'not-allowed' : 'pointer') + ';box-shadow:' + (stockOut ? 'none' : '0 4px 10px rgba(37,99,235,.3)') + ';" ' + (stockOut ? 'disabled' : '') + ' onclick="event.stopPropagation();onProductClick(' + p.id + ')">+ Add</button>' +
            '</div>' +
            '</div>';
    });
    grid.innerHTML = html;
    grid.querySelectorAll('.product-thumb').forEach(function (image) {
        image.addEventListener('error', function () {
            var fallback = document.createElement('div');
            fallback.style.cssText = 'width:52px;height:52px;border-radius:12px;background:' + image.dataset.gradient + ';color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;flex-shrink:0;box-shadow:0 2px 8px rgba(15,23,42,.15);';
            fallback.textContent = image.dataset.initial || 'P';
            image.replaceWith(fallback);
        }, { once: true });
    });
}

/* --------------------------------------
   PRODUCT DETAILS POPUP
-------------------------------------- */
function openProdDetails(productId) {
    var p = POS.products.find(function(pr) { return pr.id == productId; });
    if (!p) return;

    var hasVars   = Array.isArray(p.variations) && p.variations.length > 0;
    var totalStock = hasVars
        ? p.variations.reduce(function(s, v) { return s + (parseInt(v.stock) || 0); }, 0)
        : (parseInt(p.stock_quantity) || 0);

    var price = parseFloat2(p.sale_price || p.price || 0).toFixed(2);
    var categoryName = (p.category && p.category.name) ? p.category.name : '';
    var brandName    = (p.brand    && p.brand.name)    ? p.brand.name    : '';

    // Variation rows
    var varRows = '';
    if (hasVars) {
        varRows = '<table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:12px;">' +
            '<thead><tr style="background:#f1f5f9;">' +
            '<th style="padding:7px 10px;text-align:left;border:1.5px solid #cbd5e1;color:#000000;font-weight:800;">Variation</th>' +
            '<th style="padding:7px 10px;text-align:center;border:1.5px solid #cbd5e1;color:#000000;font-weight:800;">Stock</th>' +
            '<th style="padding:7px 10px;text-align:right;border:1.5px solid #cbd5e1;color:#000000;font-weight:800;">Action</th>' +
            '</tr></thead><tbody>';
        p.variations.forEach(function(v) {
            var vStock = parseInt(v.stock) || 0;
            var stockColor = vStock <= 0 ? '#dc2626' : (vStock <= 5 ? '#b45309' : '#15803d');
            varRows += '<tr style="border-bottom:1px solid #e2e8f0;">' +
                '<td style="padding:7px 10px;border:1px solid #cbd5e1;font-weight:700;color:#000000;">' + escHtml(v.variation_value) + '</td>' +
                '<td style="padding:7px 10px;border:1px solid #cbd5e1;text-align:center;font-weight:800;color:' + stockColor + ';">' + vStock + '</td>' +
                '<td style="padding:7px 10px;border:1px solid #cbd5e1;text-align:right;">' +
                (vStock > 0 ? '<button onclick="closeProdDetails();addToCartById(' + p.id + ',' + v.id + ')" style="background:#000000;color:#fff;border:none;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:800;cursor:pointer;">+ Add</button>' : '<span style="color:#991b1b;font-weight:800;font-size:11px;">Out</span>') +
                '</td>' +
                '</tr>';
        });
        varRows += '</tbody></table>';
    }

    var html =
        '<div style="display:flex;gap:14px;margin-bottom:14px;">' +
        '<img src="' + (p.image_url || '/images/no-product.png') + '" onerror="this.src=\'/images/no-product.png\'" style="width:90px;height:90px;object-fit:cover;border-radius:10px;border:1.5px solid #cbd5e1;flex-shrink:0;">' +
        '<div style="flex:1;min-width:0;">' +
        '<div style="font-size:16px;font-weight:800;color:#000000;margin-bottom:6px;line-height:1.3;">' + escHtml(p.name) + '</div>' +
        '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">' +
        (categoryName ? '<span style="background:#f1f5f9;color:#000000;border:1px solid #9ca3af;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase;">' + escHtml(categoryName) + '</span>' : '') +
        (brandName    ? '<span style="background:#f1f5f9;color:#000000;border:1px solid #9ca3af;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase;">' + escHtml(brandName) + '</span>' : '') +
        '</div>' +
        '<div style="display:flex;align-items:center;gap:12px;">' +
        '<span style="font-size:19px;font-weight:900;color:#000000;">BDT' + price + '</span>' +
        '<span style="font-size:12px;font-weight:800;background:' + (totalStock > 0 ? '#dcfce7' : '#fee2e2') + ';color:' + (totalStock > 0 ? '#14532d' : '#991b1b') + ';border:1px solid ' + (totalStock > 0 ? '#86efac' : '#fca5a5') + ';padding:3px 10px;border-radius:20px;">Stock: ' + totalStock + '</span>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div style="display:grid;grid-template-columns:1fr;gap:6px;margin-bottom:10px;">' +
        '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:7px 10px;">' +
        '<div style="font-size:11px;color:#000000;font-weight:800;margin-bottom:2px;">CODE</div>' +
        '<div style="font-size:13px;font-weight:800;color:#000000;font-family:monospace;">' + escHtml(p.code || '-') + '</div>' +
        '</div>' +
        '</div>' +
        (varRows ? '<div style="font-size:12px;font-weight:800;color:#000000;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Variations</div>' + varRows : '') +
        (!hasVars ? '<div style="margin-top:12px;text-align:right;"><button onclick="closeProdDetails();onProductClick(' + p.id + ')" style="background:#000000;color:#fff;border:none;border-radius:8px;padding:9px 24px;font-size:13px;font-weight:800;cursor:pointer;"><i class=\'fas fa-cart-plus\' style=\'margin-right:5px;\'></i>+ Add to Cart</button></div>' : '');

    // Rebuild modal every time to avoid stale DOM references
    var modalEl = document.getElementById('prodDetailsModal');
    if (!modalEl) {
        modalEl = document.createElement('div');
        modalEl.id = 'prodDetailsModal';
        modalEl.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:9999;display:flex;align-items:center;justify-content:center;';
        modalEl.addEventListener('click', function(e) { if (e.target === modalEl) closeProdDetails(); });
        document.body.appendChild(modalEl);
    }
    modalEl.innerHTML =
        '<div style="background:#fff;border-radius:14px;width:90%;max-width:480px;max-height:88vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.25);">' +
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1.5px solid #e2e8f0;">' +
        '<div style="font-size:15px;font-weight:900;color:#000000;">' + escHtml(p.name) + '</div>' +
        '<button onclick="closeProdDetails()" style="background:none;border:none;font-size:22px;color:#000000;font-weight:800;cursor:pointer;line-height:1;">&times;</button>' +
        '</div>' +
        '<div style="padding:16px 18px;">' + html + '</div>' +
        '</div>';
    modalEl.style.display = 'flex';
}

function closeProdDetails() {
    var el = document.getElementById('prodDetailsModal');
    if (el) el.style.display = 'none';
}

function addToCartById(productId, variationId) {
    var product   = POS.products.find(function (p) { return p.id == productId; });
    if (!product) return;
    var variation = variationId ? product.variations.find(function (v) { return v.id == variationId; }) : null;
    if (!checkStock(productId, variationId, 1)) return;
    addToCart(product, variation);
    document.getElementById('prodSearch').focus();
}

/* --------------------------------------
   PRODUCT CLICK — no customer check
-------------------------------------- */
function onProductClick(productId) {
    var product = POS.products.find(function (p) { return p.id == productId; });
    if (!product) return;
    var hasVars = Array.isArray(product.variations) && product.variations.length > 0 &&
        !(product.variations.length === 1 && product.variations[0].variation_value === 'Standard');
    if (hasVars) {
        openVariationModal(product);
    } else {
        if (!checkStock(product.id, null, 1)) return;
        addToCart(product, null);
    }
}

/* --------------------------------------
   VARIATION MODAL
-------------------------------------- */
function openModal(product) { openVariationModal(product); }
function openVariationModal(product) {
    POS.selected = product;
    document.getElementById('varModalTitle').textContent = product.name;
    var opts = '', firstEnabledValue = '';
    product.variations.forEach(function (v) {
        var stock = parseInt(v.stock) || 0;
        if (!firstEnabledValue && stock > 0) firstEnabledValue = String(v.id);
        opts += '<option value="' + v.id + '" data-stock="' + stock + '">' +
            escHtml(v.variation_value) + (stock > 0 ? ' (Stock: ' + stock + ')' : ' (Out of stock)') + '</option>';
    });
    document.getElementById('varModalBody').innerHTML =
        '<div class="form-group" style="margin:0;"><select id="varPickSelect" class="form-control">' + opts + '</select></div>';
    if (firstEnabledValue) document.getElementById('varPickSelect').value = firstEnabledValue;
    document.getElementById('varModal').classList.add('open');
}
function closeModal() { document.getElementById('varModal').classList.remove('open'); POS.selected = null; }
function confirmVariation() {
    var select      = document.getElementById('varPickSelect');
    var pickedValue = select ? select.value : '';
    if (!pickedValue) { showToast('Please select a variation', true); return; }
    var pickedOpt   = select.options[select.selectedIndex];
    var pickedStock = pickedOpt ? (parseInt(pickedOpt.getAttribute('data-stock')) || 0) : 0;
    if (pickedStock <= 0) { showToast('Selected variation is out of stock', true); return; }
    var varId     = parseInt(pickedValue);
    var variation = POS.selected.variations.find(function (v) { return v.id == varId; });
    if (!variation) return;
    if (!checkStock(POS.selected.id, variation.id, 1)) return;
    addToCart(POS.selected, variation);
    closeModal();
}

/* --------------------------------------
   ADD TO CART
-------------------------------------- */
function addToCart(product, variation) {
    var basePrice  = parseFloat2(product.sale_price || product.price || 0);
    var finalPrice = (variation && parseFloat2(variation.price || 0) > 0)
        ? round2(parseFloat2(variation.price))
        : round2(basePrice);
    var cartKey    = variation ? (product.id + '_' + variation.id) : String(product.id);
    var existing   = POS.cart.find(function (item) { return item.cartKey === cartKey; });
    if (existing) {
        if (!checkStock(product.id, variation ? variation.id : null, 1)) return;
        existing.qty += 1; renderCart(); updateTotals();
        showLastScanned(product, variation); return;
    }
    var itemCode = product.code || product.barcode || '';
    POS.cart.push({
        cartKey: cartKey, product_id: product.id, variation_id: variation ? variation.id : null,
        name: product.name, var_name: variation ? variation.variation_value : 'Standard',
        code: itemCode, qty: 1, discount: 0,
        price: finalPrice,
        image: product.image_url || '/images/no-product.png',
        stock: variation ? (parseInt(variation.stock) || 0) : (parseInt(product.stock_quantity) || 0)
    });
    renderCart(); updateTotals();
    showLastScanned(product, variation);
}

/* --------------------------------------
   CART DETAILS PANEL (all items)
-------------------------------------- */
function showLastScanned() {
    renderCartDetailsPanel();
}
function renderCartDetailsPanel() {
    var panel   = document.getElementById('cartDetailsPanel');
    var listEl  = document.getElementById('cdpList');
    var countEl = document.getElementById('cdpCount');
    var totalEl = document.getElementById('cdpTotal');
    if (!panel || !listEl) return;

    if (!POS.cart.length) {
        panel.style.display = 'none';
        return;
    }

    var html = '';
    var grandTotal = 0;

    POS.cart.forEach(function(item) {
        var lineTotal = round2(item.price * item.qty * (1 - item.discount / 100));
        grandTotal   += lineTotal;

        // Full product for category/brand
        var fp = POS.products.find(function(p) { return p.id == item.product_id; });
        var categoryName = (fp && fp.category) ? fp.category.name : '';
        var brandName    = (fp && fp.brand)    ? fp.brand.name    : '';

        // Color / Size from var_name
        var colorText = '', sizeText = '';
        if (item.var_name && item.var_name !== 'Standard') {
            var vp = item.var_name.split(' - ');
            colorText = vp.length >= 2 ? vp[0].trim() : item.var_name.trim();
            sizeText  = vp.length >= 2 ? vp[1].trim() : '';
        }

        // Color badge bg/tc (light theme — same as cart table)
        var colorBg = '#e5e7eb', colorTc = '#374151';
        if (colorText) {
            var cl = colorText.toLowerCase();
            if      (cl.indexOf('red')    > -1) { colorBg='#fee2e2'; colorTc='#991b1b'; }
            else if (cl.indexOf('green')  > -1) { colorBg='#dcfce7'; colorTc='#166534'; }
            else if (cl.indexOf('blue')   > -1) { colorBg='#dbeafe'; colorTc='#1e40af'; }
            else if (cl.indexOf('black')  > -1) { colorBg='#1f2937'; colorTc='#ffffff'; }
            else if (cl.indexOf('white')  > -1) { colorBg='#f9fafb'; colorTc='#374151'; }
            else if (cl.indexOf('yellow') > -1) { colorBg='#fef9c3'; colorTc='#854d0e'; }
            else if (cl.indexOf('orange') > -1) { colorBg='#ffedd5'; colorTc='#9a3412'; }
            else if (cl.indexOf('pink')   > -1) { colorBg='#fce7f3'; colorTc='#9d174d'; }
            else if (cl.indexOf('purple') > -1) { colorBg='#f3e8ff'; colorTc='#6b21a8'; }
            else if (cl.indexOf('navy')   > -1) { colorBg='#dbeafe'; colorTc='#1e3a8a'; }
            else if (cl.indexOf('brown')  > -1) { colorBg='#fef3c7'; colorTc='#78350f'; }
            else if (cl.indexOf('gray')   > -1 || cl.indexOf('grey') > -1) { colorBg='#f3f4f6'; colorTc='#6b7280'; }
        }

        // Stock left
        var stockLeft  = item.stock - item.qty;
        var stockColor = stockLeft <= 5 ? '#991b1b' : '#14532d';
        var stockBg    = stockLeft <= 5 ? '#fee2e2' : '#dcfce7';

        // Badges (light theme — same look as cart table)
        var badges = '';
        if (colorText) badges += '<span style="display:inline-flex;align-items:center;gap:3px;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700;background:'+colorBg+';color:'+colorTc+';"><span style="width:6px;height:6px;border-radius:50%;background:'+colorTc+';flex-shrink:0;"></span>'+escHtml(colorText)+'</span>';
        if (sizeText)  badges += '<span style="padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Size: '+escHtml(sizeText)+'</span>';
        if (categoryName) badges += '<span style="padding:1px 7px;border-radius:20px;font-size:10px;font-weight:600;background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;">'+escHtml(categoryName)+'</span>';
        if (brandName)    badges += '<span style="padding:1px 7px;border-radius:20px;font-size:10px;font-weight:600;background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff;">'+escHtml(brandName)+'</span>';

        html +=
            '<div style="display:flex;align-items:center;gap:10px;padding:7px 14px;border-bottom:1px solid #f1f5f9;">' +
            '<img src="'+escHtml(item.image)+'" onerror="this.src=\'/images/no-product.png\'" style="width:40px;height:40px;object-fit:cover;border-radius:7px;border:1px solid #e2e8f0;flex-shrink:0;">' +
            '<div style="flex:1;min-width:0;">' +
            '<div style="font-size:13px;font-weight:800;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;">'+escHtml(item.name)+'</div>' +
            (badges ? '<div style="display:flex;flex-wrap:wrap;gap:3px;margin-bottom:3px;">'+badges+'</div>' : '') +
            '<div style="display:flex;align-items:center;gap:8px;">' +
            '<span style="font-size:11px;color:#64748b;">BDT'+item.price.toFixed(2)+' × '+item.qty+'</span>' +
            '<span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;background:'+stockBg+';color:'+stockColor+';">Stock: '+stockLeft+'</span>' +
            '</div>' +
            '</div>' +
            '<div style="text-align:right;flex-shrink:0;">' +
            '<div style="font-size:14px;font-weight:800;color:#111827;">BDT'+lineTotal.toFixed(2)+'</div>' +
            '<div style="font-size:9px;color:#64748b;margin-top:1px;">QTY: '+item.qty+'</div>' +
            '</div>' +
            '</div>';
    });

    listEl.innerHTML = html;
    if (countEl) countEl.textContent = POS.cart.length;
    if (totalEl) totalEl.textContent = 'BDT' + grandTotal.toFixed(2);
    panel.style.display = 'block';
}

/* --------------------------------------
   CART RENDER
-------------------------------------- */
function renderCart() {
    var tbody = document.getElementById('cartBody');
    if (!POS.cart.length) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="cart-empty">' +
            '<i class="fas fa-shopping-basket"></i>Click on a product or scan barcode to add</div></td></tr>';
        var p = document.getElementById('cartDetailsPanel'); if (p) p.style.display = 'none';
        updateTotals(); return;
    }
    var rows = '';
    POS.cart.forEach(function (item, i) {
        var lineTotal = round2(item.price * item.qty * (1 - item.discount / 100)).toFixed(2);

        // Full product data for category & brand
        var fullProduct = POS.products.find(function(p) { return p.id == item.product_id; });
        var categoryName = (fullProduct && fullProduct.category) ? fullProduct.category.name : '';
        var brandName    = (fullProduct && fullProduct.brand)    ? fullProduct.brand.name    : '';

        // Parse color & size from variation_value e.g. "Red - S"
        var colorText = '', sizeText = '';
        if (item.var_name && item.var_name !== 'Standard') {
            var vp = item.var_name.split(' - ');
            if (vp.length >= 2) { colorText = vp[0].trim(); sizeText = vp[1].trim(); }
            else { colorText = item.var_name.trim(); }
        }

        // Color badge colors
        var colorBg = '#e5e7eb', colorTc = '#374151';
        if (colorText) {
            var cl = colorText.toLowerCase();
            if      (cl.indexOf('red')    > -1) { colorBg='#fee2e2'; colorTc='#991b1b'; }
            else if (cl.indexOf('green')  > -1) { colorBg='#dcfce7'; colorTc='#166534'; }
            else if (cl.indexOf('blue')   > -1) { colorBg='#dbeafe'; colorTc='#1e40af'; }
            else if (cl.indexOf('black')  > -1) { colorBg='#1f2937'; colorTc='#ffffff'; }
            else if (cl.indexOf('white')  > -1) { colorBg='#f9fafb'; colorTc='#374151'; }
            else if (cl.indexOf('yellow') > -1) { colorBg='#fef9c3'; colorTc='#854d0e'; }
            else if (cl.indexOf('orange') > -1) { colorBg='#ffedd5'; colorTc='#9a3412'; }
            else if (cl.indexOf('pink')   > -1) { colorBg='#fce7f3'; colorTc='#9d174d'; }
            else if (cl.indexOf('purple') > -1) { colorBg='#f3e8ff'; colorTc='#6b21a8'; }
            else if (cl.indexOf('navy')   > -1) { colorBg='#dbeafe'; colorTc='#1e3a8a'; }
            else if (cl.indexOf('brown')  > -1) { colorBg='#fef3c7'; colorTc='#78350f'; }
            else if (cl.indexOf('gray')   > -1 || cl.indexOf('grey') > -1) { colorBg='#f3f4f6'; colorTc='#6b7280'; }
        }

        // Stock remaining
        var stockLeft  = item.stock - item.qty;
        var stockColor = stockLeft <= 5 ? '#dc2626' : '#6b7280';

        // Badges: color, size, category, brand
        var badges = '';
        if (colorText) badges +=
            '<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;background:' + colorBg + ';color:' + colorTc + ';margin-right:3px;">' +
            '<span style="width:6px;height:6px;border-radius:50%;background:' + colorTc + ';flex-shrink:0;"></span>' + escHtml(colorText) + '</span>';
        if (sizeText)     badges += '<span style="padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;margin-right:3px;">Size: ' + escHtml(sizeText) + '</span>';
        if (categoryName) badges += '<span style="padding:2px 7px;border-radius:20px;font-size:10px;font-weight:600;background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;margin-right:3px;">' + escHtml(categoryName) + '</span>';
        if (brandName)    badges += '<span style="padding:2px 7px;border-radius:20px;font-size:10px;font-weight:600;background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff;">' + escHtml(brandName) + '</span>';

        rows += '<tr>' +
            '<td>' +
            '<div style="display:flex;align-items:flex-start;gap:8px;">' +
            '<img src="' + item.image + '" style="width:44px;height:44px;object-fit:cover;border-radius:7px;border:1px solid #e5e7eb;flex-shrink:0;" onerror="this.src=\'/images/no-product.png\'">' +
            '<div style="min-width:0;">' +
            '<div style="font-weight:700;font-size:13.5px;line-height:1.45;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:155px;" title="' + escHtml(item.name) + '">' + escHtml(item.name) + '</div>' +
            (badges ? '<div style="margin-top:3px;display:flex;flex-wrap:wrap;gap:2px;">' + badges + '</div>' : '') +
            '<div style="margin-top:2px;font-size:11px;color:' + stockColor + ';font-weight:600;"><i class="fas fa-boxes" style="font-size:9px;margin-right:2px;"></i>Stock: ' + stockLeft + '</div>' +
            '</div>' +
            '</div>' +
            '</td>' +
            '<td style="font-weight:700;font-size:13px;color:#1a1a2e;">BDT' + item.price.toFixed(2) + '</td>' +
            '<td><input class="disc-num" type="number" value="' + item.discount + '" min="0" max="100" onchange="setDiscount(' + i + ',this.value)"></td>' +
            '<td><div class="qty-wrap">' +
            '<button class="qty-btn" onclick="decQty(' + i + ')">-</button>' +
            '<input class="qty-num" type="number" value="' + item.qty + '" min="1" onchange="setQty(' + i + ',this.value)">' +
            '<button class="qty-btn" onclick="incQty(' + i + ')">+</button>' +
            '</div></td>' +
            '<td style="font-weight:800;font-size:14px;color:#059669;">BDT' + lineTotal + '</td>' +
            '<td style="text-align:center;"><button class="remove-btn" onclick="removeItem(' + i + ')"><i class="fas fa-times"></i></button></td>' +
            '</tr>';
    });
    tbody.innerHTML = rows; updateTotals(); renderCartDetailsPanel();
}

/* --------------------------------------
   CART ACTIONS
-------------------------------------- */
function incQty(i) {
    if (!POS.cart[i]) return;
    if (!checkStock(POS.cart[i].product_id, POS.cart[i].variation_id, 1)) return;
    POS.cart[i].qty += 1; renderCart();
}
function decQty(i) { if (POS.cart[i] && POS.cart[i].qty > 1) { POS.cart[i].qty -= 1; renderCart(); } }
function setQty(i, val) {
    if (!POS.cart[i]) return;
    var newQty = Math.max(1, parseInt(val) || 1);
    var delta  = newQty - POS.cart[i].qty;
    if (delta > 0 && !checkStock(POS.cart[i].product_id, POS.cart[i].variation_id, delta)) return;
    POS.cart[i].qty = newQty; renderCart();
}
function setDiscount(i, val) { if (POS.cart[i]) { POS.cart[i].discount = Math.min(100, Math.max(0, parseFloat(val) || 0)); renderCart(); } }
function removeItem(i) { POS.cart.splice(i, 1); renderCart(); }
function clearCart() {
    if (!POS.cart.length || confirm('Clear the cart?')) {
        POS.cart = []; renderCart();
        var p = document.getElementById('cartDetailsPanel'); if (p) p.style.display = 'none';
    }
}

/* --------------------------------------
   TOTALS
-------------------------------------- */
function updateTotals() {
    var subtotal = round2(POS.cart.reduce(function (sum, item) { return sum + round2(item.price * item.qty * (1 - item.discount / 100)); }, 0));
    var vatRate  = parseFloat2(document.getElementById('vatType').value);
    var vat      = round2(subtotal * (vatRate / 100));
    var discType = document.getElementById('discountType').value;
    var discVal  = parseFloat2(document.getElementById('discountValue').value);
    var discount = discType === 'percent' ? round2(subtotal * (discVal / 100)) : discVal;
    var total    = Math.max(0, round2(subtotal + vat - discount));
    document.getElementById('dispSubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('dispVat').textContent      = vat.toFixed(2);
    document.getElementById('dispTotal').textContent    = total.toFixed(2);
    calcChange();
    var searchInp = document.getElementById('prodSearch');
    if (searchInp) searchInp.focus();
}
function calcChange() {
    var receive = parseFloat2(document.getElementById('receiveAmount').value);
    var total   = parseFloat2(document.getElementById('dispTotal').textContent);
    var diff    = round2(receive - total);
    document.getElementById('changeAmount').value = diff >= 0 ? diff.toFixed(2) : '0.00';
    document.getElementById('dueAmount').value    = diff <  0 ? Math.abs(diff).toFixed(2) : '0.00';
}

/* --------------------------------------
   BUILD PAYLOAD
-------------------------------------- */
function buildSalePayload() {
    return {
        items: POS.cart.map(function (item) {
            return { id: item.product_id, quantity: item.qty, discount: item.discount || 0, variation_id: item.variation_id || null, code: item.code || '' };
        }),
        customer_id:      POS.customerId ? parseInt(POS.customerId) : null,
        customer_name:    POS.customerName    || null,
        customer_phone:   POS.customerPhone   || null,
        customer_address: POS.customerAddress || null,
        payment_method: document.getElementById('paymentMethod').value,
        paid_amount:    parseFloat2(document.getElementById('receiveAmount').value),
        vat_rate:       parseFloat2(document.getElementById('vatType').value),
        discount_type:  document.getElementById('discountType').value,
        discount_value: parseFloat2(document.getElementById('discountValue').value),
        notes:          null
    };
}

/* --------------------------------------
   PAY NOW
-------------------------------------- */
function processSaleAjax() {
    if (!POS.cart.length) { alert('Cart is empty! Please add products first.'); return; }

    if (!isCustomerSelected()) {
        showToast('Please select a customer. Customer selection is mandatory.', true);
        highlightCustomerField();
        return;
    }

    var paid = parseFloat2(document.getElementById('receiveAmount').value);
    if (paid <= 0) { alert('Receive amount is required before selling.'); document.getElementById('receiveAmount').focus(); return; }

    // Ensure selected customer state is synced from hidden field
    var hiddenId = document.getElementById('customerId').value;
    if (hiddenId && !POS.customerId) {
        var found = POS.customers.find(function (c) { return String(c.id) === String(hiddenId); });
        if (found) {
            selectCustomer(String(found.id), found.name, found.phone, found.address);
        }
    }

    doProcessSale();
}

/* --------------------------------------
   ACTUAL SALE
-------------------------------------- */
function doProcessSale() {
    var payload = buildSalePayload();
    var btnPay  = document.getElementById('btnPay');
    btnPay.disabled  = true;
    btnPay.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    fetch('/pos/process-sale', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': POS.csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(payload)
    })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
            if (result.ok && result.data.success) {
                if (result.data.invoice_data) {
                    showInvoiceModal(result.data.invoice_data);
                }
                showToast('Sale Completed Successfully!', false);
                POS.cart = []; POS.customerId = ''; POS.customerName = ''; POS.customerPhone = ''; POS.customerAddress = ''; POS.pendingSaleAfterCustomer = false;
                var lsp = document.getElementById('cartDetailsPanel'); if (lsp) lsp.style.display = 'none';
                selectCustomer('', '', '', '');
                document.getElementById('receiveAmount').value  = '0';
                document.getElementById('discountValue').value  = '0';
                document.getElementById('vatType').value        = '0';
                renderCart(); updateTotals();
                document.getElementById('prodSearch').focus();
            } else {
                var msg = result.data.message || 'Payment failed. Please try again.';
                if (result.data.errors) console.error('Validation errors:', result.data.errors);
                alert(msg);
            }
        })
        .catch(function (err) { console.error('Network error:', err); alert('Network error. Please check your connection and try again.'); })
        .finally(function () { btnPay.disabled = false; btnPay.innerHTML = '<i class="fas fa-check-circle"></i> Pay Now'; });
}

/* --------------------------------------
   HELPERS
-------------------------------------- */
function showToast(msg, isError) {
    var el = document.createElement('div');
    el.className = 'pos-toast' + (isError ? ' error' : '');
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function () { el.remove(); }, 2800);
}
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function escJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/\r/g,'').replace(/\n/g,' ');
}

/* --------------------------------------
   INVOICE MODAL FUNCTIONS
-------------------------------------- */
function escInv(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function numberToWords(num) {
    if (num === 0) return 'Zero';
    var a = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    var b = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    var c = ['','Thousand','Lakh','Crore'];
    function inWords(n) {
        if (n < 20) return a[n];
        if (n < 100) return b[Math.floor(n/10)] + (n%10 ? ' ' + a[n%10] : '');
        if (n < 1000) return a[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' and ' + inWords(n%100) : '');
    }
    var parts = [], i = 0;
    while (num > 0) {
        var chunk = num % 1000;
        if (i === 1 && chunk > 99) { chunk = num % 100; num = Math.floor(num / 100); } else { num = Math.floor(num / 1000); }
        if (chunk > 0) parts.unshift(inWords(chunk) + (c[i] ? ' ' + c[i] : ''));
        i++;
    }
    return parts.join(' ');
}

function showInvoiceModal(invoiceData) {
    window._lastInvoiceData = invoiceData;
    document.getElementById('invShopName').textContent = invoiceData.shop_name || '';
    document.getElementById('invFooterShopName').textContent = invoiceData.shop_name || '';
    document.getElementById('invShopAddress').textContent = invoiceData.shop_address || '';
    document.getElementById('invShopPhone').textContent = invoiceData.shop_phone ? 'Phone: ' + invoiceData.shop_phone : '';
    document.getElementById('invShopEmail').textContent = invoiceData.shop_email ? 'Email: ' + invoiceData.shop_email : '';
    document.getElementById('invInvoiceNo').textContent = invoiceData.invoice_no;
    document.getElementById('invDate').textContent = invoiceData.date;
    document.getElementById('invCustomerName').textContent = invoiceData.customer_name || 'Walk-in Customer';
    document.getElementById('invCustomerPhone').textContent = invoiceData.customer_phone ? 'Phone: ' + invoiceData.customer_phone : '';
    document.getElementById('invCustomerAddress').textContent = invoiceData.customer_address ? 'Address: ' + invoiceData.customer_address : '';
    document.getElementById('invCustomerArea').textContent = invoiceData.customer_area ? 'Area: ' + invoiceData.customer_area : '';
    document.getElementById('invShipName').textContent = invoiceData.customer_name || 'Walk-in Customer';
    document.getElementById('invShipPhone').textContent = invoiceData.customer_phone ? 'Phone: ' + invoiceData.customer_phone : '';
    document.getElementById('invShipAddress').textContent = invoiceData.customer_address ? invoiceData.customer_address : '';
    document.getElementById('invPayMethod').textContent = invoiceData.payment_method || 'Cash';
    document.getElementById('invBankName').textContent = invoiceData.bank_name || 'Not Provided';
    document.getElementById('invBankAc').textContent = invoiceData.bank_ac || '-';
    document.getElementById('invBankIfsc').textContent = invoiceData.bank_ifsc || '-';
    document.getElementById('invBankUpi').textContent = invoiceData.bank_upi || '-';

    var barcodeVal = (invoiceData.items && invoiceData.items.length > 0) ? (invoiceData.items[0].barcode || invoiceData.items[0].code || invoiceData.invoice_no || '') : (invoiceData.invoice_no || '');
    var svgEl = document.getElementById('invBarcodeSvg');
    svgEl.innerHTML = '';
    document.getElementById('invBarcodeText').textContent = '';
    function renderBarcode() {
        if (barcodeVal && typeof JsBarcode !== 'undefined') {
            try { JsBarcode(svgEl, barcodeVal, { format: 'CODE128', width: 1, height: 30, displayValue: false, margin: 0 }); document.getElementById('invBarcodeText').textContent = barcodeVal; } catch(e) { svgEl.innerHTML = ''; }
        }
    }
    if (typeof JsBarcode !== 'undefined') { renderBarcode(); }
    else { var tries = 0; var tryBarcode = setInterval(function() { tries++; if (typeof JsBarcode !== 'undefined') { clearInterval(tryBarcode); renderBarcode(); } if (tries > 30) clearInterval(tryBarcode); }, 100); }

    var serialEl = document.getElementById('invSerialNo');
    if (serialEl) serialEl.textContent = invoiceData.invoice_no || '-';
    var prodCodeArea = document.getElementById('invProductCodeArea');
    if (prodCodeArea) {
        var codeLines = '';
        if (invoiceData.items && invoiceData.items.length > 0) {
            invoiceData.items.forEach(function(item) {
                if (item.code) codeLines += 'Code: ' + item.code + '<br>';
                if (item.barcode && item.barcode !== item.code) codeLines += 'Barcode: ' + item.barcode + '<br>';
            });
        }
        prodCodeArea.innerHTML = codeLines;
    }

    var itemsHtml = '', totalQty = 0;
    invoiceData.items.forEach(function(item, idx) {
        totalQty += parseInt(item.quantity) || 0;
        var color = item.color || '-', size = item.size || '-';
        if ((!color || color === '-') && item.variation_name) { var parts = String(item.variation_name).split(' - '); if (parts.length >= 2) { color = parts[0].trim(); size = parts[1].trim(); } else if (parts.length === 1) { color = parts[0].trim(); } }
        if ((!color || color === '-') && item.variation) { var parts = String(item.variation).split(' - '); if (parts.length >= 2) { color = parts[0].trim(); size = parts[1].trim(); } else if (parts.length === 1) { color = parts[0].trim(); } }
        var c = color || '', bg = '#eeeeee', tc = '#333333', border = '1px solid #bbb', cl = c.toLowerCase();
        if(cl.indexOf('red') > -1) { bg='#ff5252'; tc='#ffffff'; border='1px solid #d32f2f'; } else if(cl.indexOf('blue') > -1) { bg='#3b82f6'; tc='#ffffff'; border='1px solid #2563eb'; } else if(cl.indexOf('green') > -1) { bg='#10b981'; tc='#ffffff'; border='1px solid #059669'; } else if(cl.indexOf('black') > -1) { bg='#111827'; tc='#ffffff'; border='1px solid #000000'; } else if(cl.indexOf('white') > -1) { bg='#ffffff'; tc='#111827'; border='1px solid #d1d5db'; } else if(cl.indexOf('yellow') > -1) { bg='#f59e0b'; tc='#ffffff'; border='1px solid #d97706'; } else if(cl.indexOf('orange') > -1) { bg='#f97316'; tc='#ffffff'; border='1px solid #ea580c'; } else if(cl.indexOf('pink') > -1) { bg='#ec4899'; tc='#ffffff'; border='1px solid #db2777'; } else if(cl.indexOf('purple') > -1) { bg='#a855f7'; tc='#ffffff'; border='1px solid #9333ea'; } else if(cl.indexOf('gray') > -1 || cl.indexOf('grey') > -1) { bg='#6b7280'; tc='#ffffff'; border='1px solid #4b5563'; } else if(cl.indexOf('brown') > -1) { bg='#78350f'; tc='#ffffff'; border='1px solid #451a03'; } else if(cl.indexOf('navy') > -1) { bg='#1e3a8a'; tc='#ffffff'; border='1px solid #1e40af'; }
        var colorCell = c && c !== '-' ? '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;background:'+bg+';color:'+tc+';border:'+border+';text-transform:capitalize">'+escHtml(c)+'</span>' : '<span style="color:#9ca3af">-</span>';
        itemsHtml += '<tr><td style="text-align:center;">' + (idx + 1) + '</td><td style="text-align:left;"><b>' + escHtml(item.name) + '</b>' + (item.code ? '<div style="font-size:9px;color:#6b7280;margin-top:2px">Code: ' + escHtml(item.code) + '</div>' : '') + '</td><td style="text-align:center">' + colorCell + '</td><td style="text-align:center;">' + escHtml(size) + '</td><td style="text-align:center;">' + item.quantity + '</td><td style="text-align:right;">BDT' + parseFloat(item.unit_price).toFixed(2) + '</td><td style="text-align:right;">BDT' + parseFloat(item.total).toFixed(2) + '</td></tr>';
    });
    document.getElementById('invItems').innerHTML = itemsHtml;

    var subtotal = parseFloat(invoiceData.subtotal) || 0, vatAmt = parseFloat(invoiceData.vat_amount) || 0, vatRate = parseFloat(invoiceData.vat_rate) || 0, discount = parseFloat(invoiceData.discount) || 0, total = parseFloat(invoiceData.total) || 0, paid = parseFloat(invoiceData.paid) || 0, due = parseFloat(invoiceData.due) || 0;
    document.getElementById('invTaxable').textContent = 'BDT' + subtotal.toFixed(2);
    if (vatAmt > 0) {
        var cgstRate = (vatRate / 2).toFixed(1), sgstRate = (vatRate / 2).toFixed(1), cgstAmt = (vatAmt / 2).toFixed(2), sgstAmt = (vatAmt / 2).toFixed(2);
        document.getElementById('invCgstRow').style.display = '';
        document.getElementById('invSgstRow').style.display = '';
        document.getElementById('invCgstRate').textContent = cgstRate;
        document.getElementById('invSgstRate').textContent = sgstRate;
        document.getElementById('invCgst').textContent = 'BDT' + cgstAmt;
        document.getElementById('invSgst').textContent = 'BDT' + sgstAmt;
    } else {
        document.getElementById('invCgstRow').style.display = 'none';
        document.getElementById('invSgstRow').style.display = 'none';
    }
    if (discount > 0) { document.getElementById('invDiscountRow').style.display = ''; document.getElementById('invDiscount').textContent = '-BDT' + discount.toFixed(2); } else { document.getElementById('invDiscountRow').style.display = 'none'; }
    document.getElementById('invTotal').textContent = 'BDT' + total.toFixed(2);
    document.getElementById('invPaid').textContent = 'BDT' + paid.toFixed(2);
    document.getElementById('invAmountWords').textContent = 'Amount in words: ' + numberToWords(Math.round(total)) + ' BDT Only';
    if (due > 0) { document.getElementById('invDueRow').style.display = ''; document.getElementById('invDue').textContent = 'BDT' + due.toFixed(2); } else { document.getElementById('invDueRow').style.display = 'none'; }
    document.getElementById('invoicePrintModal').classList.add('active');
}

function closeInvoiceModal() {
    document.getElementById('invoicePrintModal').classList.remove('active');
}

function printInvoice() {
    var content = document.getElementById('thermalInvoiceContent');
    if (!content) return;
    var win = window.open('', '_blank');
    win.document.write('<!DOCTYPE html><html><head><title>Invoice</title>');
    win.document.write('<style>');
    win.document.write('@page { size: A4; margin: 10mm 15mm; }');
    win.document.write('body { margin: 0; padding: 0; background: #fff; font-family: "Inter","Arial",sans-serif; display: flex; justify-content: center; }');
    win.document.write('.inv-print-wrap { width: 100%; max-width: 190mm; padding: 5mm 8mm; margin: 0 auto; box-sizing: border-box; font-size: 10px; line-height: 1.5; color: #000; }');
    win.document.write('.inv-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0; }');
    win.document.write('.inv-logo-side { flex: 1; } .inv-info-side { flex: 0 0 auto; text-align: right; }');
    win.document.write('.inv-title-text { font-size: 22px; font-weight: 900; color: #000; margin: 0 0 4px 0; letter-spacing: 2px; line-height: 1; }');
    win.document.write('.inv-meta-table { margin-bottom: 6px; }');
    win.document.write('.inv-meta-row { display: flex; justify-content: flex-end; gap: 8px; font-size: 9px; margin-bottom: 2px; }');
    win.document.write('.inv-meta-lbl { color: #555; } .inv-meta-val { color: #000; font-weight: 600; }');
    win.document.write('.inv-barcode-wrap { text-align: right; max-width: 180px; margin-left: auto; }');
    win.document.write('.inv-barcode-wrap svg { display: block; width: 100%; max-width: 160px; max-height: 26px; }');
    win.document.write('.inv-barcode-num { font-size: 7px; letter-spacing: 0.5px; color: #555; margin-top: 1px; text-align: center; }');
    win.document.write('.inv-divider { border: none; border-top: 1px solid #000; margin: 6px 0; }');
    win.document.write('.inv-bill-row { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 0; }');
    win.document.write('.inv-bill-col, .inv-from-col { flex: 1; }');
    win.document.write('.inv-col-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #555; margin-bottom: 3px; }');
    win.document.write('.inv-cust-name { font-size: 11px; font-weight: 700; color: #000; margin-bottom: 2px; }');
    win.document.write('.inv-cust-sub { font-size: 9px; color: #555; margin-top: 1px; }');
    win.document.write('.inv-from-name { font-size: 11px; font-weight: 700; color: #000; text-align: right; margin-bottom: 2px; }');
    win.document.write('.inv-from-sub { font-size: 9px; color: #555; text-align: right; margin-top: 1px; }');
    win.document.write('.inv-items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }');
    win.document.write('.inv-items-table thead th { color: #000 !important; background: #fff !important; font-size: 8px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; padding: 5px 6px !important; border-top: 1.5px solid #000 !important; border-bottom: 1.5px solid #000 !important; }');
    win.document.write('.inv-items-table tbody td { padding: 4px 6px; border-bottom: 1px solid #ddd; font-size: 9px; color: #000; vertical-align: middle; }');
    win.document.write('.inv-items-table tbody tr:last-child td { border-bottom: 1.5px solid #000; }');
    win.document.write('.inv-totals-section { display: flex; justify-content: flex-end; }');
    win.document.write('.inv-totals-box { width: 200px; }');
    win.document.write('.inv-total-row { display: flex; justify-content: space-between; padding: 2px 6px; font-size: 9px; border-bottom: 1px solid #ddd; }');
    win.document.write('.inv-total-row .lbl { color: #555; } .inv-total-row .val { font-weight: 600; color: #000; }');
    win.document.write('.inv-total-row.discount .val { color: #c00; }');
    win.document.write('.inv-total-row.grand { font-size: 10px; font-weight: 800; padding: 5px 6px; background: #fff; border-bottom: none; border-top: 1.5px solid #000; }');
    win.document.write('.inv-pay-info { display: flex; justify-content: space-between; align-items: center; padding: 4px 6px; font-size: 8px; color: #000; border: 1px solid #000; margin-top: 0; }');
    win.document.write('.inv-pay-info b { color: #000; } .inv-due-badge { color: #c00; font-weight: 700; }');
    win.document.write('.inv-amount-words { font-size: 8px; font-style: italic; color: #555; padding: 3px 6px; border: 1px solid #000; border-top: none; }');
    win.document.write('.inv-thankyou { text-align: center; padding: 6px; font-size: 9px; font-weight: 600; color: #000; letter-spacing: 1px; border-top: 1px solid #000; margin-top: 8px; }');
    win.document.write('@media print { body { background: #fff; } .inv-print-wrap { padding: 0; max-width: 100%; } }');
    win.document.write('</style></head><body>');
    win.document.write(content.innerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); win.close(); }, 400);
}
