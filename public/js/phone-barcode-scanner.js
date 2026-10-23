'use strict';
window.PhoneBarcodeScanner = (function () {
    var html5QrCode = null;

    function open() {
        var modal = document.getElementById('scannerModal');
        modal.style.display = 'flex';

        html5QrCode = new Html5Qrcode("qr-reader");

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            onScanSuccess,
            function onScanFailure(errorMessage) {

            }
        ).catch(function (err) {
            console.error('Camera start failed:', err);
            showToast('Camera চালু করা যায়নি: ' + err, true);
            modal.style.display = 'none';
        });
    }

    function onScanSuccess(decodedText) {
        // barbar trigger হওয়া ঠেকাতে আগে stop করো
        html5QrCode.stop().then(function () {
            document.getElementById('scannerModal').style.display = 'none';

            var inp = document.getElementById('prodSearch');
            inp.value = decodedText;

            // pos.js er existing function — barcode resolve + cart add
            handleBarcodeEnter();
        }).catch(function (err) {
            console.error('Stop failed:', err);
        });
    }

    function close() {
        if (html5QrCode) {
            html5QrCode.stop().then(function () {
                document.getElementById('scannerModal').style.display = 'none';
            }).catch(function () {
                document.getElementById('scannerModal').style.display = 'none';
            });
        } else {
            document.getElementById('scannerModal').style.display = 'none';
        }
    }

    return { open: open, close: close };
})();
