@extends('adminlte::page')

@section('title', 'Invoice - ' . $sale->invoice_number)

@section('adminlte_css')
<style>
.btn-toolbar { text-align: center; margin-bottom: 15px; max-width: 850px; margin-left: auto; margin-right: auto; }
.btn-toolbar a, .btn-toolbar button {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600;
    cursor: pointer; text-decoration: none; border: none;
}
.btn-back { background: #e5e7eb; color: #374151; }
.btn-back:hover { background: #d1d5db; }
.btn-print { background: #2563eb; color: #fff; }
.btn-print:hover { background: #1d4ed8; }
@media print { .btn-toolbar { display: none !important; } }

.inv-print-wrap { max-width: 850px; margin: 0 auto; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
@media print {
    body { background: #fff !important; }
    .inv-print-wrap { box-shadow: none; padding: 10px; border-radius: 0; }
}
</style>
@stop

@section('content')
<div class="btn-toolbar">
    <a href="{{ route('sales.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Sales</a>
    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
</div>

@include('admin.pos.partials.invoice-content')

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
@php
    $cName = $sale->customer_name ?: ($sale->customer?->name ?? 'Walk-in Customer');
    $cPhone = $sale->customer_phone ?: ($sale->customer?->phone ?? '');
    $cAddr = $sale->customer?->address ?? '';
    $shopName = config('adminlte.logo', 'BongoBay');
    $subtotal = 0;
    $totalQty = 0;
    $itemsData = [];
    foreach ($sale->items as $idx => $item) {
        $varName = null;
        $color = '-';
        $size = '-';
        if ($item->product_variation_id) {
            $var = \App\Models\ProductVariation::find($item->product_variation_id);
            $varName = $var?->variation_value;
            if ($varName) {
                $parts = explode(' - ', $varName);
                $color = $parts[0] ?? '-';
                $size = $parts[1] ?? '-';
            }
        }
        $qty = (int) $item->quantity;
        $price = (float) $item->unit_price;
        $lineTotal = $qty * $price;
        $totalQty += $qty;
        $subtotal += $lineTotal;
        $itemsData[] = ['name' => $item->product_name, 'code' => $item->product?->code ?? '', 'barcode' => $item->product?->barcode ?? '', 'color' => $color, 'size' => $size, 'qty' => $qty, 'price' => $price, 'lineTotal' => $lineTotal, 'variation' => $varName];
    }
    $vatAmt = (float) ($sale->vat_amount ?? 0);
    $vatRate = (float) ($sale->vat_rate ?? 0);
    $discount = (float) ($sale->discount_amount ?? 0);
    $total = (float) $sale->total_amount;
    $paid = (float) $sale->paid_amount;
    $due = (float) $sale->due_amount;
@endphp
(function() {
    var d = {
        shop_name: '{{ $shopName }}',
        shop_address: 'Mizmizi Purbo Para T.C. Road, Siddhirganj, Narayanganj.',
        shop_phone: '+88 01703-063330',
        shop_email: 'info@bongobaybd.com',
        invoice_no: '{{ $sale->invoice_number }}',
        date: '{{ $sale->created_at->format("M d, Y h:i A") }}',
        customer_name: '{{ $cName }}',
        customer_phone: '{{ $cPhone }}',
        customer_address: '{{ $cAddr }}',
        customer_area: '',
        payment_method: '{{ $sale->payment_method }}',
        bank_name: '', bank_ac: '', bank_ifsc: '', bank_upi: '',
        subtotal: {{ $subtotal }},
        vat_amount: {{ $vatAmt }},
        vat_rate: {{ $vatRate }},
        discount: {{ $discount }},
        total: {{ $total }},
        paid: {{ $paid }},
        due: {{ $due }},
        items: [
            @foreach($itemsData as $item)
            {
                name: '{{ str_replace("'", "\\'", $item['name']) }}',
                code: '{{ $item['code'] }}',
                barcode: '{{ $item['barcode'] }}',
                color: '{{ $item['color'] }}',
                size: '{{ $item['size'] }}',
                quantity: {{ $item['qty'] }},
                unit_price: {{ $item['price'] }},
                total: {{ $item['lineTotal'] }},
                variation: '{{ $item['variation'] ?? '' }}',
                variation_name: '{{ $item['variation'] ?? '' }}'
            }@if(!$loop->last),@endif
            @endforeach
        ]
    };

    document.getElementById('invShopName').textContent = d.shop_name;
    document.getElementById('invFooterShopName').textContent = d.shop_name;
    document.getElementById('invShopAddress').textContent = d.shop_address;
    document.getElementById('invShopPhone').textContent = d.shop_phone ? 'Phone: ' + d.shop_phone : '';
    document.getElementById('invShopEmail').textContent = d.shop_email ? 'Email: ' + d.shop_email : '';
    document.getElementById('invInvoiceNo').textContent = d.invoice_no;
    document.getElementById('invDate').textContent = d.date;
    document.getElementById('invCustomerName').textContent = d.customer_name;
    document.getElementById('invCustomerPhone').textContent = d.customer_phone ? 'Phone: ' + d.customer_phone : '';
    document.getElementById('invCustomerAddress').textContent = d.customer_address ? 'Address: ' + d.customer_address : '';
    document.getElementById('invCustomerArea').textContent = d.customer_area ? 'Area: ' + d.customer_area : '';
    document.getElementById('invShipName').textContent = d.customer_name;
    document.getElementById('invShipPhone').textContent = d.customer_phone ? 'Phone: ' + d.customer_phone : '';
    document.getElementById('invShipAddress').textContent = d.customer_address || '';
    document.getElementById('invPayMethod').textContent = d.payment_method;
    document.getElementById('invBankName').textContent = d.bank_name || 'Not Provided';
    document.getElementById('invBankAc').textContent = d.bank_ac || '-';
    document.getElementById('invBankIfsc').textContent = d.bank_ifsc || '-';
    document.getElementById('invBankUpi').textContent = d.bank_upi || '-';

    var itemsHtml = '', totalQty = 0;
    d.items.forEach(function(item, idx) {
        totalQty += parseInt(item.quantity) || 0;
        var color = item.color || '-', size = item.size || '-';
        if ((!color || color === '-') && item.variation_name) { var parts = String(item.variation_name).split(' - '); if (parts.length >= 2) { color = parts[0].trim(); size = parts[1].trim(); } else if (parts.length === 1) { color = parts[0].trim(); } }
        if ((!color || color === '-') && item.variation) { var parts = String(item.variation).split(' - '); if (parts.length >= 2) { color = parts[0].trim(); size = parts[1].trim(); } else if (parts.length === 1) { color = parts[0].trim(); } }
        itemsHtml += '<tr><td>' + (idx + 1) + '</td><td style="text-align:left;"><b>' + item.name + '</b>' + (item.code ? '<div style="font-size:9px;color:#6b7280;">Code: ' + item.code + '</div>' : '') + '</td><td>' + (color && color !== '-' ? '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;background:#eee;color:#333;border:1px solid #bbb;">' + color + '</span>' : '-') + '</td><td>' + size + '</td><td>' + item.quantity + '</td><td style="text-align:right;">BDT' + parseFloat(item.unit_price).toFixed(2) + '</td><td style="text-align:right;">BDT' + parseFloat(item.total).toFixed(2) + '</td></tr>';
    });
    document.getElementById('invItems').innerHTML = itemsHtml;

    var subtotal = d.subtotal, vatAmt = d.vat_amount, vatRate = d.vat_rate, discount = d.discount, total = d.total, paid = d.paid, due = d.due;
    document.getElementById('invTaxable').textContent = 'BDT' + subtotal.toFixed(2);
    if (vatAmt > 0) {
        document.getElementById('invCgstRow').style.display = '';
        document.getElementById('invSgstRow').style.display = '';
        document.getElementById('invCgstRate').textContent = (vatRate / 2).toFixed(1);
        document.getElementById('invSgstRate').textContent = (vatRate / 2).toFixed(1);
        document.getElementById('invCgst').textContent = 'BDT' + (vatAmt / 2).toFixed(2);
        document.getElementById('invSgst').textContent = 'BDT' + (vatAmt / 2).toFixed(2);
    } else {
        document.getElementById('invCgstRow').style.display = 'none';
        document.getElementById('invSgstRow').style.display = 'none';
    }
    if (discount > 0) { document.getElementById('invDiscountRow').style.display = ''; document.getElementById('invDiscount').textContent = '-BDT' + discount.toFixed(2); } else { document.getElementById('invDiscountRow').style.display = 'none'; }
    document.getElementById('invTotal').textContent = 'BDT' + total.toFixed(2);
    document.getElementById('invPaid').textContent = 'BDT' + paid.toFixed(2);

    function numToWords(num) {
        if (num === 0) return 'Zero';
        var a = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
        var b = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        var c = ['','Thousand','Lakh','Crore'];
        var parts = []; var n = num;
        while (n > 0) {
            var chunk = n % 1000; n = Math.floor(n / 1000); var ci = 0;
            var tmp = n; while (tmp > 0 && ci < c.length - 1) { tmp = Math.floor(tmp / 1000); ci++; }
            if (chunk > 0) {
                var words = '';
                if (chunk >= 100) { words += a[Math.floor(chunk / 100)] + ' Hundred'; chunk %= 100; if (chunk > 0) words += ' and '; }
                if (chunk >= 20) { words += b[Math.floor(chunk / 10)]; chunk %= 10; if (chunk > 0) words += ' '; }
                if (chunk > 0) words += a[chunk];
                parts.unshift(words + (c[ci] ? ' ' + c[ci] : ''));
            }
        }
        return parts.join(' ') || 'Zero';
    }
    document.getElementById('invAmountWords').textContent = 'Amount in words: ' + numToWords(Math.round(total)) + ' BDT Only';
    if (due > 0) { document.getElementById('invDueRow').style.display = ''; document.getElementById('invDue').textContent = 'BDT' + due.toFixed(2); } else { document.getElementById('invDueRow').style.display = 'none'; }

    var bv = d.invoice_no || '';
    if (bv && typeof JsBarcode !== 'undefined') {
        try { JsBarcode(document.getElementById('invBarcodeSvg'), bv, {format:'CODE128',width:1,height:30,displayValue:false,margin:0}); document.getElementById('invBarcodeText').textContent = bv; } catch(e) {}
    }
})();
</script>
@stop
