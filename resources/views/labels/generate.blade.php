<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label Print</title>
    <style>
        /* Screen preview uses the chosen column count (set inline as --cols). */
        .label-wrapper {
            --cols: 1;
        }

        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #eef2f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #000;
        }

        .no-print {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .print-btn {
            padding: 8px 24px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 6px;
            background: #003d5c;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .print-btn:hover {
            background: #00263a;
        }

        .label-wrapper {
            display: grid;
            grid-template-columns: repeat(var(--cols), 50mm);
            justify-content: center;
            gap: 12px;
            padding-bottom: 30px;
        }

        /* Single Sticker Box (50mm x 25mm) */
        .label {
            width: 50mm;
            height: 25mm;
            min-height: 25mm;
            max-height: 25mm;
            padding: 0.6mm 1mm 0.6mm 1mm;
            box-sizing: border-box;
            background: #fff;
            border-radius: 2px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border: 0.8px solid #000;
            /* Legacy + modern fragmentation properties together for Chrome/Firefox parity */
            page-break-inside: avoid;
            break-inside: avoid-page;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow: hidden;
        }

        /* Labels flow continuously — no forced page break, so all
           labels pack onto as few pages as possible. */
        .label:not(:last-of-type) {
            page-break-after: auto;
            break-after: auto;
        }

        /* 1. Header Row: Brand | Article (Clean White Background) */
        .box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding-bottom: 0.3mm;
            border-bottom: 0.6px solid #000;
            line-height: 1.1;
            background: #fff;
            flex-shrink: 0;
        }
        .brand-name {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
            flex: 1 1 auto;
        }
        .art-code {
            font-size: 8.5px;
            font-weight: 800;
            color: #000;
            text-align: right;
            white-space: nowrap;
            letter-spacing: 0.2px;
            margin-left: 4px;
            flex-shrink: 0;
        }

        /* 2. Barcode Section — barcode size unchanged (9mm) */
        .box-barcode {
            text-align: center;
            width: 100%;
            padding: 0.2mm 0 0.2mm 0;
            border-bottom: 0.6px solid #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            flex-shrink: 0;
        }
        .barcode-svg-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            overflow: hidden;
        }
        .barcode-svg-wrap svg {
            width: 100% !important;
            max-width: 100% !important;
            height: 9mm !important;
            max-height: 9.5mm !important;
            display: block;
            margin: 0 auto;
        }
        .barcode-number {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Courier New", monospace;
            font-size: 8.5px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.8px;
            line-height: 1.15;
            margin-top: 0.5mm;
            color: #000;
            background: #fff;
            display: block;
            width: 100%;
        }
        .unit-code-number {
            font-size: 8px;
            font-weight: 700;
            line-height: 1.1;
            text-align: center;
            color: #000;
        }

        /* 3. Product Name (with Letter-Spacing) */
        .box-product-name {
            width: 100%;
            padding: 0.3mm 0;
            border-bottom: 0.6px solid #000;
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #000;
            text-align: left;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
            background: #fff;
        }

        /* 4. Footer Section: Clean MRP ৳900.00 & 🔴 Red [XS] */
        .box-footer {
            width: 100%;
            padding-top: 0.3mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            line-height: 1.1;
            flex-shrink: 0;
            background: #fff;
        }
        .mrp-price-text {
            font-size: 10px;
            font-weight: 900;
            color: #000;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .footer-right {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .size-badge {
            font-size: 8.5px;
            font-weight: 900;
            color: #000;
            border: 0.7px solid #000;
            padding: 0.5px 3.5px;
            border-radius: 2px;
            text-transform: uppercase;
            line-height: 1;
        }
        .color-text {
            font-size: 8.5px;
            font-weight: 800;
            color: #000;
            display: inline-flex;
            align-items: center;
            gap: 2.5px;
            line-height: 1;
        }
        .color-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            border: 0.4px solid rgba(0,0,0,0.3);
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm;
            }
            body {
                background: #fff;
                display: block;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .label-wrapper {
                padding: 0;
                gap: 3mm;
                display: grid;
                grid-template-columns: repeat(var(--cols), 50mm);
                justify-content: center;
            }
            .no-print {
                display: none !important;
            }
            .label {
                border: 0.8px solid #000 !important;
                border-radius: 0;
                box-shadow: none;
                margin: 0;
                padding: 0.6mm 1mm 0.6mm 1mm;
                page-break-inside: avoid;
                break-inside: avoid-page;
                width: 50mm;
                height: 25mm; /* Exact sticker height */
                min-height: 25mm;
                max-height: 25mm;
            }
            /* Same no-forced-break rule inside print media, since this block
               overrides the base .label rule above with equal specificity. */
            .label:not(:last-of-type) {
                page-break-after: auto;
                break-after: auto;
            }
            .box-header {
                background: #fff !important;
                color: #000 !important;
                border-bottom: 0.6px solid #000 !important;
            }
            .brand-name,
            .art-code {
                color: #000 !important;
            }
            .box-barcode,
            .box-product-name {
                border-bottom: 0.6px solid #000 !important;
            }
            .size-badge {
                border: 0.7px solid #000 !important;
            }
            .barcode-svg-wrap svg {
                width: 100% !important;
                max-width: 100% !important;
                height: 9mm !important;
            }
            .barcode-number {
                font-size: 8.5px !important;
                font-weight: 800 !important;
                color: #000 !important;
                display: block !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Barcode</button>
        <div style="font-size:11px; color:#666; margin-top:5px; font-weight:600;">{{ count($labels) }} Label(s) ready to print</div>
    </div>

    <div class="label-wrapper" style="--cols: 1;">
        @php $bcGen = new \Milon\Barcode\DNS1D(); @endphp
        @foreach($labels as $item)
        @php
            $sizeText = $item['size'] ?? '';
            $colorText = $item['color'] ?? '';
            if(empty($sizeText) && !empty($item['variation'])) {
                $parts = explode(' - ', $item['variation']);
                $colorText = count($parts) > 1 ? $parts[0] : '';
                $sizeText  = count($parts) > 1 ? $parts[1] : $parts[0];
            }
            $prodName = !empty($item['name']) ? $item['name'] : 'PANT';
            $catName = $item['category'] ?? '';
            $productCode = $item['product_code'] ?? '';
            $barcodeVal = ($item['scan_value'] ?? '') ?: ($item['barcode'] ?? '') ?: $productCode;
            // Header = Brand name (na thakle category), middle = Product name.
            $brandOnly = trim((string) ($item['brand'] ?? ''));
            $headerText = $brandOnly !== '' ? $brandOnly : (trim((string) $catName) !== '' ? $catName : $prodName);
            $middleText = $prodName;
            if ($middleText === '' || mb_strtolower(trim((string) $middleText)) === mb_strtolower(trim((string) $headerText))) {
                $middleText = '';
            }

            // Color mapping for color dot
            $colorMap = [
                'red'    => '#e53e3e',
                'blue'   => '#3182ce',
                'green'  => '#38a169',
                'yellow' => '#d69e2e',
                'black'  => '#000000',
                'white'  => '#e2e8f0',
                'gray'   => '#718096',
                'grey'   => '#718096',
                'pink'   => '#ed64a6',
                'purple' => '#805ad5',
                'orange' => '#dd6b20',
                'navy'   => '#1a365d',
                'maroon' => '#742a2a',
                'olive'  => '#6b7280',
            ];
            $dotColor = $colorMap[strtolower(trim($colorText))] ?? '#e53e3e';
        @endphp

        <div class="label">
            {{-- 1. Header: Brand name left, Art code right --}}
            <div class="box-header">
                <span class="brand-name">{{ $headerText }}</span>
                @if(!empty($productCode))
                <span class="art-code">Art: {{ $productCode }}</span>
                @endif
            </div>

            {{-- 2. Barcode & single number below (BB250000001) --}}
            <div class="box-barcode">
                @if(!empty($barcodeVal))
                    @php
                        // Stretch bars edge-to-edge: uniform horizontal scaling
                        // keeps bar/space ratios intact, so it stays scannable.
                        $barcodeSvg = $bcGen->getBarcodeSVG($barcodeVal, 'C128', 1.25, 30, 'black', false, false);
                        // Strip XML prolog/DOCTYPE (invalid inside HTML body and
                        // can cause rendering quirks) then force full-width stretch.
                        $barcodeSvg = preg_replace('/<\?xml.*?\?>\s*/s', '', $barcodeSvg, 1);
                        $barcodeSvg = preg_replace('/<!DOCTYPE.*?>\s*/s', '', $barcodeSvg, 1);
                        // Milon emits width/height but no viewBox — without a
                        // viewBox preserveAspectRatio can't scale, leaving a
                        // right-side gap. Inject viewBox + none so bars fill 100%.
                        if (preg_match('/<svg[^>]*width="([0-9.]+)"[^>]*height="([0-9.]+)"/', $barcodeSvg, $vbMatch)) {
                            $barcodeSvg = preg_replace('/<svg\b/', '<svg viewBox="0 0 ' . $vbMatch[1] . ' ' . $vbMatch[2] . '"', $barcodeSvg, 1);
                        }
                        $barcodeSvg = preg_replace('/<svg\b(?![^>]*preserveAspectRatio)/', '<svg preserveAspectRatio="none"', $barcodeSvg, 1);
                    @endphp
                    <div class="barcode-svg-wrap">
                        {!! $barcodeSvg !!}
                    </div>
                @endif
                <div class="barcode-number">{{ $barcodeVal }}</div>
            </div>

            {{-- 3. Product name line --}}
            @if(!empty($middleText))
            <div class="box-product-name">
                {{ $middleText }}
            </div>
            @endif

            {{-- 4. Footer: MRP :900.00 & 🔴 Red [XS] --}}
            <div class="box-footer">
                <span class="mrp-price-text">MRP :{{ number_format((float) ($item['sale_price'] ?? $item['price'] ?? 0), 2, '.', '') }}</span>
                <div class="footer-right">
                    @if($colorText)
                        <span class="color-text">
                            <span class="color-dot" style="background-color: {{ $dotColor }};"></span> {{ $colorText }}
                        </span>
                    @endif
                    @if($sizeText)
                        <span class="size-badge">{{ $sizeText }}</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

</body>
</html>
