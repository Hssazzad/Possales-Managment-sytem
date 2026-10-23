/**
 * barcode-scanner.js
 *
 * This script enables focus-free barcode scanning for the POS billing page.
 * It listens for fast keyboard input (typical of hardware scanners),
 * buffers the characters, and fetches product details via AJAX.
 */

$(document).ready(function() {
    let barcodeBuffer = '';
    let lastKeyTime = Date.now();
    const SCAN_TIMEOUT = 90; // Milliseconds between keystrokes to consider it a scan
    const MIN_BARCODE_LENGTH = 3;

    /**
     * Listen for all keydown events on the document (Focus-free scanning)
     */
    $(document).on('keydown', function(e) {
        const currentTime = Date.now();

        // Ignore pure modifier keys (common noise during scanning / shortcuts)
        if (e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt' || e.key === 'Meta') {
            lastKeyTime = currentTime;
            return;
        }

        // 1. Skip if user is typing in common input fields (except the main POS search)
        // This allows manual entry in other fields without interference
        const activeEl = document.activeElement;
        const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeEl.tagName);
        const isPosSearch = activeEl.id === 'prodSearch';

        if (isInput && !isPosSearch) {
            // If focused on another input, only process if it's very fast (scanner)
            if (currentTime - lastKeyTime > SCAN_TIMEOUT) {
                return;
            }
        }

        // 2. Buffer logic: If typing is fast, it's likely a scanner
        if (currentTime - lastKeyTime > SCAN_TIMEOUT) {
            barcodeBuffer = ''; // Reset buffer for new scan
        }

        // 3. Handle the input
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (barcodeBuffer.length >= MIN_BARCODE_LENGTH) {
                e.preventDefault();
                processScannedBarcode(barcodeBuffer);
                barcodeBuffer = ''; // Clear after processing
            }
        } else if (e.key.length === 1) {
            // Append single characters to buffer
            barcodeBuffer += e.key;
        }

        lastKeyTime = currentTime;
    });

    /**
     * AJAX fetch and process the scanned barcode
     */
    function processScannedBarcode(barcode) {
        console.log('Barcode Scanned:', barcode);

        // Visual feedback on the scanner icon
        const scannerIcon = $('.fa-barcode');
        scannerIcon.css('color', '#059669').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100, function() {
            $(this).css('color', '#6b7280');
        });

        // Optional: Show a temporary visual feedback
        if (typeof showToast === 'function') {
            showToast('Scanning: ' + barcode, false);
        }

        $.ajax({
            url: '/pos/get-product/' + encodeURIComponent(barcode),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.product) {
                    const product = response.product;

                    // Ensure product exists in the local POS state if needed
                    if (typeof POS !== 'undefined' && POS.products) {
                        const exists = POS.products.find(p => p.id === product.id);
                        if (!exists) {
                            POS.products.push(product);
                        }
                    }

                    // Handle Customer Selection Check (Reuse existing POS logic)
                    if (typeof isCustomerSelected === 'function' && !isCustomerSelected()) {
                        if (typeof showToast === 'function') {
                            showToast('Please select a customer first!', true);
                        }
                        if (typeof highlightCustomerField === 'function') {
                            highlightCustomerField();
                        }
                        return;
                    }

                    // Add to billing table
                    // If product has variations, open the modal, otherwise add directly
                    if (product.variations && product.variations.length > 0) {
                        // POS variation modal function is `openVariationModal(product)`
                        if (typeof openVariationModal === 'function') {
                            openVariationModal(product);
                        } else if (typeof showToast === 'function') {
                            showToast('Variation modal not available', true);
                        }
                    } else {
                        if (typeof addToCart === 'function') {
                            addToCart(product, null);
                            if (typeof showToast === 'function') {
                                showToast('Added: ' + product.name, false);
                            }
                        }
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Product not found: ' + barcode, true);
                    }
                }
            },
            error: function(xhr) {
                console.error('Barcode Search Error:', xhr);
                if (typeof showToast === 'function') {
                    showToast('Error searching barcode', true);
                }
            },
            complete: function() {
                // Clear and refocus the main search input if needed
                const searchInp = document.getElementById('prodSearch');
                if (searchInp) {
                    searchInp.value = '';
                    searchInp.focus();
                }
            }
        });
    }
});
