/**
 * Simple Barcode Scanner for POS
 * Focus-free scanning with buffer, AJAX fetch, add to table.
 */
$(function () {
    var $input = $('#prodSearch');
    var $status = $('#scannerStatus');
    var barcodeBuffer = '';
    var lastKeyTime = Date.now();
    var SCAN_TIMEOUT = 50;
    var MIN_LENGTH = 3;

    // 1. Keep focus on barcode input
    function keepFocus() {
        $input.focus();
    }
    keepFocus();
    $(document).on('click', function (e) {
        if (!$(e.target).is('input, textarea, select, button, a')) {
            keepFocus();
        }
    });

    // 2. Focus-free barcode capture (global keydown)
    $(document).on('keydown', function (e) {
        var currentTime = Date.now();
        var activeEl = document.activeElement;
        var isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeEl.tagName);
        var isPosSearch = activeEl.id === 'prodSearch';

        // Ignore typing in other input fields (unless it's very fast = scanner)
        if (isInput && !isPosSearch) {
            if (currentTime - lastKeyTime > SCAN_TIMEOUT) {
                return;
            }
        }

        // Reset buffer if typing is slow (human typing)
        if (currentTime - lastKeyTime > SCAN_TIMEOUT) {
            barcodeBuffer = '';
        }

        if (e.key === 'Enter') {
            // If input has text, use that; otherwise use buffer
            var inputVal = $.trim($input.val());
            var barcode = inputVal.length >= MIN_LENGTH ? inputVal : barcodeBuffer;

            if (barcode.length >= MIN_LENGTH) {
                e.preventDefault();
                processBarcode(barcode);
            }
            barcodeBuffer = '';
            $input.val('');
        } else if (e.key.length === 1) {
            barcodeBuffer += e.key;
        }

        lastKeyTime = currentTime;
    });

    // 3. AJAX fetch and add product
    function processBarcode(barcode) {
        if (typeof showToast === 'function') {
            showToast('Scanning: ' + barcode, false);
        }

        $.ajax({
            url: '/pos/add-product-by-barcode/' + encodeURIComponent(barcode),
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success && res.product) {
                    addOrUpdateProduct(res.product);
                    flashStatus('success');
                } else {
                    flashStatus('error');
                    if (typeof showToast === 'function') showToast('Product not found', true);
                }
            },
            error: function () {
                flashStatus('error');
                if (typeof showToast === 'function') showToast('Server error', true);
            }
        });
    }

    // 4. Update UI: add to table or increase quantity
    function addOrUpdateProduct(product) {
        if (typeof POS !== 'undefined') {
            var exists = POS.products.find(function (p) { return p.id === product.id; });
            if (!exists) POS.products.push(product);
        }

        if (typeof addToCart === 'function') {
            // Handle variations: if product has variations, open modal
            if (product.variations && product.variations.length > 0) {
                if (typeof openVariationModal === 'function') {
                    POS.selected = product;
                    openVariationModal(product);
                    if (typeof showToast === 'function') showToast('Select variation for: ' + product.name, false);
                }
            } else {
                addToCart(product, null);
                if (typeof showToast === 'function') showToast('Added: ' + product.name, false);
            }
        }

        $input.val('');
        keepFocus();
    }

    // Visual feedback on status badge
    function flashStatus(type) {
        var bg = type === 'success' ? '#059669' : '#dc2626';
        var text = type === 'success' ? '✓ ADDED' : '✗ ERROR';
        $status.css('background', bg).text(text);
        setTimeout(function () {
            $status.css('background', '#059669').text('SCANNER ACTIVE');
        }, 800);
    }
});
