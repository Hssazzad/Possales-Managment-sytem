@extends('adminlte::page')

@section('title', 'Label Preview | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center no-print">
    <h1 style="font-size:1.3rem; font-weight:600; color:#343a40;">
        <i class="fas fa-barcode mr-2" style="color:#17a2b8;"></i> Label Preview
    </h1>
    <div>
        <button onclick="window.print()" class="btn btn-primary btn-sm mr-2">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <a href="{{ route('labels.print') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid no-print">
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>{{ count($labels) }}</strong> labels ready to print. Click the Print button above.
    </div>
</div>

<!-- Label Sheet Container -->
<div class="label-sheet">
    @foreach($labels as $label)
    <div class="label-item">
        <div class="label-content">
            <!-- Business Name -->
            @if($settings['business_name'])
            <div class="business-name" style="font-size: {{ $settings['business_name_size'] }}px; font-weight: bold; text-align: center; margin-bottom: 2px;">
                {{ $settings['business_name'] }}
            </div>
            @endif
            
            <!-- Product Name -->
            <div class="product-name" style="font-size: {{ $settings['product_name_size'] }}px; font-weight: bold; text-align: center; margin-bottom: 2px;">
                {{ $label['product']->name }}
            </div>
            
            <!-- Product Price -->
            @if($settings['show_price'])
            <div class="product-price" style="font-size: {{ $settings['price_size'] }}px; text-align: center; margin-bottom: 2px;">
                @if($settings['inc_tax'])
                    Price: ৳{{ number_format($label['product']->sale_price, 2) }} (Inc.)
                @else
                    Price: ৳{{ number_format($label['product']->sale_price, 2) }}
                @endif
            </div>
            @endif
            
            <!-- Product Code -->
            <div class="product-code" style="font-size: {{ $settings['code_size'] }}px; text-align: center; margin-bottom: 2px;">
                Code: {{ $label['product']->code }}
            </div>
            
            <!-- Packing Date -->
            @if($label['packing_date'])
            <div class="packing-date" style="font-size: {{ $settings['date_size'] }}px; text-align: center; margin-bottom: 2px;">
                Mfg: {{ date('d/m/Y', strtotime($label['packing_date'])) }}
            </div>
            @endif
            
            <!-- Barcode -->
            <div class="barcode-container" style="text-align: center; margin-top: 5px;">
                <svg class="barcode" 
                     jsbarcode-format="{{ strtolower($settings['barcode_type']) }}"
                     jsbarcode-value="{{ $label['product']->code }}"
                     jsbarcode-textposition="bottom"
                     jsbarcode-fontoptions="bold"
                     jsbarcode-fontsize="12"
                     jsbarcode-height="40"
                     jsbarcode-width="2"
                     jsbarcode-margin="0">
                </svg>
            </div>
        </div>
    </div>
    @endforeach
</div>
@stop

@section('adminlte_js')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate barcodes
    JsBarcode(".barcode").init();
});
</script>
@stop

@section('adminlte_css')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .main-header,
    .main-sidebar,
    .content-header,
    .card-header,
    .alert {
        display: none !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}

.label-sheet {
    display: flex;
    flex-wrap: wrap;
    gap: 3.1mm;
    padding: 10mm;
    justify-content: flex-start;
}

.label-item {
    width: 50mm;
    height: 25mm;
    background: white;
    border: 1px solid #ddd;
    padding: 2mm;
    box-sizing: border-box;
    page-break-inside: avoid;
    display: flex;
    align-items: center;
    justify-content: center;
}

.label-content {
    width: 100%;
    text-align: center;
    overflow: hidden;
}

.barcode-container svg {
    max-width: 100%;
    height: auto;
}

.business-name,
.product-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-price,
.product-code,
.packing-date {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@stop
