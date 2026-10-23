@extends('adminlte::page')

@section('title', 'New Sale Return | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <h5 class="mb-0 font-weight-bold">
                <i class="fas fa-undo mr-2 text-warning"></i> New Sale Return
                @if($sale)
                    <small class="text-muted">— {{ $sale->invoice_number }}</small>
                @endif
            </h5>
            <div class="action-buttons">
                <a href="{{ route('sales.index') }}" class="btn-export">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sales
                </a>
                <a href="{{ route('returns.index') }}" class="btn-export">
                    <i class="fas fa-list mr-1"></i> Return List
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix these errors:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if(!$sale)
            <div class="alert alert-warning">
                No sale selected. Enter a Sale ID to start a return.
            </div>
            <form method="GET" action="{{ route('returns.create') }}" class="form-inline" style="gap:10px;">
                <input type="number" name="sale_id" class="form-control" placeholder="Sale ID" min="1" required>
                <button type="submit" class="btn-add-new">
                    <i class="fas fa-search mr-1"></i> Load Sale
                </button>
            </form>
        @else
            <!-- Sale Summary -->
            <div class="d-flex flex-wrap mb-3" style="gap:0.75rem;">
                <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                    Invoice: <strong class="text-primary">{{ $sale->invoice_number }}</strong>
                </span>
                <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                    Date: <strong>{{ $sale->created_at->format('d M Y, h:i A') }}</strong>
                </span>
                <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                    Customer: <strong>{{ $sale->customer_name ?: ($sale->customer?->name ?: 'Walk-in') }}</strong>
                </span>
                <span class="badge badge-light border" style="font-size:13px;padding:8px 14px;">
                    Total: <strong>৳{{ number_format($sale->total_amount, 2) }}</strong>
                </span>
            </div>

            <form method="POST" action="{{ route('returns.store') }}">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                <input type="hidden" name="customer_id" value="{{ $sale->customer_id }}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="return_product" class="form-control" required>
                                <option value="">Select product from this sale</option>
                                @foreach($sale->items as $item)
                                    <option value="{{ $item->product_id }}"
                                        data-qty="{{ (int) $item->quantity }}"
                                        data-price="{{ (float) $item->unit_price }}"
                                        {{ old('product_id') == $item->product_id ? 'selected' : '' }}>
                                        {{ $item->product_name ?: ($item->product?->name ?: 'Product') }}
                                        (Sold: {{ (int) $item->quantity }} × ৳{{ number_format($item->unit_price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="return_qty" class="form-control"
                                min="1" value="{{ old('quantity', 1) }}" required>
                            <small class="text-muted" id="qty_hint"></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control" list="return_reasons"
                                value="{{ old('reason') }}" placeholder="e.g. Defective item" maxlength="500" required>
                            <datalist id="return_reasons">
                                <option value="Defective item"></option>
                                <option value="Wrong item delivered"></option>
                                <option value="Customer changed mind"></option>
                                <option value="Damaged during delivery"></option>
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Refund Amount <span class="text-danger">*</span></label>
                            <input type="number" name="refund_amount" id="refund_amount" class="form-control"
                                min="0" step="0.01" value="{{ old('refund_amount') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Refund Method <span class="text-danger">*</span></label>
                            <select name="refund_method" class="form-control" required>
                                @foreach(['cash', 'card', 'bank', 'credit'] as $method)
                                    <option value="{{ $method }}"
                                        {{ old('refund_method', $sale->payment_method) === $method ? 'selected' : '' }}>
                                        {{ ucfirst($method) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"
                        placeholder="Optional note">{{ old('notes') }}</textarea>
                </div>

                <div class="text-right">
                    <a href="{{ route('sales.index') }}" class="btn btn-default mr-2">Cancel</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Save Return
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>
@stop

@section('adminlte_js')
@if($sale)
<script>
(function () {
    var productSelect = document.getElementById('return_product');
    var qtyInput = document.getElementById('return_qty');
    var refundInput = document.getElementById('refund_amount');
    var qtyHint = document.getElementById('qty_hint');

    function selectedOption() {
        return productSelect.options[productSelect.selectedIndex];
    }

    function recalc(fromQty) {
        var opt = selectedOption();
        if (!opt || !opt.value) {
            qtyHint.textContent = '';
            return;
        }
        var maxQty = parseInt(opt.dataset.qty) || 1;
        var price = parseFloat(opt.dataset.price) || 0;

        var qty = parseInt(qtyInput.value) || 0;
        if (qty < 1) qty = 1;
        if (qty > maxQty) qty = maxQty;
        qtyInput.value = qty;
        qtyInput.max = maxQty;
        qtyHint.textContent = 'Max returnable: ' + maxQty;

        if (!fromQty || !refundInput.value) {
            refundInput.value = (qty * price).toFixed(2);
        }
    }

    productSelect.addEventListener('change', function () { recalc(false); });
    qtyInput.addEventListener('input', function () { recalc(true); });
    recalc(false);
})();
</script>
@endif
@stop
