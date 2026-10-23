{{-- ═══════════════════════════════════════════════════════════════
     INVOICE CONTENT — Reusable partial
     Include: @include('admin.pos.partials.invoice-content')
     Data is injected via JS into #invoicePrintModal
════════════════════════════════════════════════════════════════ --}}

<style>
/* ── Invoice Print Styles ── */
.inv-print-wrap {
    font-family: 'Inter', 'Arial', sans-serif;
    font-size: 10px;
    color: #000;
    background: #fff;
    padding: 10px 14px;
    max-width: 100%;
    margin: 0 auto;
    line-height: 1.5;
    box-sizing: border-box;
}

.inv-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0; }
.inv-logo-side { flex: 1; }
.inv-logo-wrap { margin-bottom: 4px; }
.inv-shop-tagline { font-size: 8px; color: #555; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
.inv-shop-contact { font-size: 8px; color: #555; line-height: 1.5; }
.inv-info-side { flex: 0 0 auto; text-align: right; }

.inv-title-text {
    font-size: 20px; font-weight: 900; color: #000;
    margin: 0 0 2px 0; letter-spacing: 1.5px; line-height: 1;
}

.inv-meta-table { margin-bottom: 6px; }
.inv-meta-row { display: flex; justify-content: flex-end; gap: 8px; font-size: 9px; margin-bottom: 2px; }
.inv-meta-lbl { color: #555; }
.inv-meta-val { color: #000; font-weight: 600; }

.inv-barcode-wrap { text-align: right; max-width: 200px; margin-left: auto; }
.inv-barcode-wrap svg { display: block; width: 100%; max-width: 180px; max-height: 28px; }
.inv-barcode-num { font-size: 7px; letter-spacing: 0.5px; color: #555; margin-top: 1px; text-align: center; }

.inv-divider { border: none; border-top: 1px solid #000; margin: 8px 0; }

.inv-bill-row { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 0; }
.inv-bill-col, .inv-from-col { flex: 1; }
.inv-col-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #555; margin-bottom: 3px; }
.inv-cust-name { font-size: 11px; font-weight: 700; color: #000; margin-bottom: 2px; }
.inv-cust-sub { font-size: 9px; color: #555; margin-top: 1px; }
.inv-from-name { font-size: 11px; font-weight: 700; color: #000; text-align: right; margin-bottom: 2px; }
.inv-from-sub { font-size: 9px; color: #555; text-align: right; margin-top: 1px; }

.inv-items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.inv-items-table thead th {
    color: #000 !important; background: #fff !important;
    font-size: 8px !important; font-weight: 700 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
    padding: 5px 6px !important;
    border-top: 1.5px solid #000 !important;
    border-bottom: 1.5px solid #000 !important;
    -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
}
.inv-items-table tbody td {
    padding: 4px 6px; border-bottom: 1px solid #ddd;
    font-size: 9px; color: #000; vertical-align: middle;
}
.inv-items-table tbody tr:last-child td { border-bottom: 1.5px solid #000; }

.inv-totals-section { display: flex; justify-content: flex-end; }
.inv-totals-box { width: 200px; }
.inv-total-row {
    display: flex; justify-content: space-between; padding: 2px 6px;
    font-size: 9px; border-bottom: 1px solid #ddd;
}
.inv-total-row .lbl { color: #555; }
.inv-total-row .val { font-weight: 600; color: #000; }
.inv-total-row.discount .val { color: #c00; }
.inv-total-row.grand {
    font-size: 10px; font-weight: 800; padding: 5px 6px;
    background: #fff; border-bottom: none; border-top: 1.5px solid #000;
}

.inv-pay-info {
    display: flex; justify-content: space-between; align-items: center;
    padding: 4px 6px; font-size: 8px; color: #000;
    border: 1px solid #000; margin-top: 0;
}
.inv-pay-info b { color: #000; }
.inv-due-badge { color: #c00; font-weight: 700; }

.inv-amount-words {
    font-size: 8px; font-style: italic; color: #555;
    padding: 3px 6px; border: 1px solid #000; border-top: none;
}

.inv-thankyou {
    text-align: center; padding: 6px; font-size: 9px; font-weight: 600;
    color: #000; letter-spacing: 1px; border-top: 1px solid #000; margin-top: 8px;
}

@media print {
    .inv-print-wrap { padding: 0; max-width: 100%; font-size: 9px; }
    .inv-items-table thead th,
    .inv-total-row.grand,
    .inv-pay-info {
        font-size: 8px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<div class="inv-print-wrap">
    {{-- Header --}}
    <div class="inv-header-row">
        <div class="inv-logo-side">
            <div class="inv-logo-wrap">
                <img src="/images/bongobay-logo.jpg" alt="BongoBay"
                     style="max-width:100px;max-height:35px;object-fit:contain;">
            </div>
            <div style="font-size:8px;color:#000;line-height:1.4;margin-top:4px;font-weight:600;">
                Contact Person: Md. Kamrul Hassan, Proprietor<br>
                Phone: +88 01703-063330<br>
                Address: Mizmizi Purbo Para T.C. Road, Siddhirganj, Narayanganj.<br>
                Web: www.bongobaybd.com
            </div>
        </div>
        <div class="inv-info-side">
            <h1 class="inv-title-text">INVOICE</h1>
            <div class="inv-meta-table">
                <div class="inv-meta-row">
                    <span class="inv-meta-lbl">Invoice No:</span>
                    <span class="inv-meta-val" id="invInvoiceNo">-</span>
                </div>
                <div class="inv-meta-row">
                    <span class="inv-meta-lbl">Date:</span>
                    <span class="inv-meta-val" id="invDate">-</span>
                </div>
            </div>
            <div class="inv-barcode-wrap">
                <svg id="invBarcodeSvg"></svg>
                <div class="inv-barcode-num" id="invBarcodeText"></div>
            </div>
        </div>
    </div>

    <hr class="inv-divider">

    {{-- Bill To / From --}}
    <div class="inv-bill-row">
        <div class="inv-bill-col">
            <div class="inv-col-label">BILL TO</div>
            <div class="inv-cust-name" id="invCustomerName">Walk-in Customer</div>
            <div class="inv-cust-sub" id="invCustomerPhone"></div>
            <div class="inv-cust-sub" id="invCustomerAddress"></div>
            <div class="inv-cust-sub" id="invCustomerArea"></div>
            <div id="invShipName" style="display:none"></div>
            <div id="invShipPhone" style="display:none"></div>
            <div id="invShipAddress" style="display:none"></div>
        </div>
        <div class="inv-from-col">
            <div class="inv-col-label" style="text-align:right;">FROM</div>
            <div class="inv-from-name" id="invShopName"></div>
            <div class="inv-from-sub" id="invShopAddress"></div>
            <div class="inv-from-sub" id="invShopPhone"></div>
            <div class="inv-from-sub" id="invShopEmail"></div>
            <div id="invFooterShopName" style="display:none"></div>
        </div>
    </div>

    <hr class="inv-divider">

    {{-- Items Table --}}
    <table class="inv-items-table">
        <thead>
            <tr>
                <th style="width:5%;text-align:center;">#</th>
                <th style="width:35%;text-align:left;">Product</th>
                <th style="width:13%;text-align:center;">Color</th>
                <th style="width:10%;text-align:center;">Size</th>
                <th style="width:8%;text-align:center;">Qty</th>
                <th style="width:14%;text-align:right;">Price</th>
                <th style="width:15%;text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody id="invItems"></tbody>
    </table>

    {{-- Totals --}}
    <div class="inv-totals-section">
        <div class="inv-totals-box">
            <div class="inv-total-row">
                <span class="lbl">Subtotal</span>
                <span class="val" id="invTaxable">BDT0.00</span>
            </div>
            <div class="inv-total-row" id="invCgstRow">
                <span class="lbl">CGST (<span id="invCgstRate">0</span>%)</span>
                <span class="val" id="invCgst">BDT0.00</span>
            </div>
            <div class="inv-total-row" id="invSgstRow">
                <span class="lbl">SGST (<span id="invSgstRate">0</span>%)</span>
                <span class="val" id="invSgst">BDT0.00</span>
            </div>
            <div class="inv-total-row discount" id="invDiscountRow">
                <span class="lbl">Discount</span>
                <span class="val" id="invDiscount">-BDT0.00</span>
            </div>
            <div class="inv-total-row grand">
                <span class="lbl">TOTAL</span>
                <span class="val" id="invTotal">BDT0.00</span>
            </div>
        </div>
    </div>

    {{-- Payment Info --}}
    <div class="inv-pay-info">
        <div>
            <b>Payment Method:</b> <span id="invPayMethod">-</span>
            &nbsp;|&nbsp; <b>Received:</b> <span id="invPaid">BDT0.00</span>
            <span id="invDueRow">&nbsp;|&nbsp; <span class="inv-due-badge"><b>Due:</b> <span id="invDue">BDT0.00</span></span></span>
        </div>
        <div>
            <span id="invBankName" style="display:none"></span>
            <span id="invBankAc" style="display:none"></span>
            <span id="invBankIfsc" style="display:none"></span>
            <span id="invBankUpi" style="display:none"></span>
        </div>
    </div>

    <div class="inv-amount-words" id="invAmountWords"></div>

    <div id="invSerialNo" style="display:none"></div>
    <div id="invProductCodeArea" style="display:none"></div>

    <div style="display:flex;justify-content:space-between;align-items:flex-end;padding:6px 0 2px;border-top:1px solid #000;margin-top:6px;">
        <div style="text-align:center;">
            <div style="font-size:9px;font-weight:700;color:#000;margin-bottom:20px;">________________________</div>
            <div style="font-size:8px;font-weight:600;color:#555;">Authorized Signature</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:9px;font-weight:700;color:#000;margin-bottom:20px;">________________________</div>
            <div style="font-size:8px;font-weight:600;color:#555;">Customer Signature</div>
        </div>
    </div>

    <div class="inv-thankyou">Thank you for shopping with BongoBay</div>
</div>
