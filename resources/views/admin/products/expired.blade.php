@extends('adminlte::page')

@section('title', 'Expired Products | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-exclamation-triangle mr-2" style="color:#cc0000;"></i> Expired Products
    </h1>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        {{-- Summary Cards --}}
        @if($products->count() > 0)
        <div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:160px; background:#ffeeee; border:2px solid #ffcccc; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#cc0000; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Expired</div>
                <div style="font-size:28px; font-weight:700; color:#cc0000; margin-top:4px;">{{ $products->total() }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Expired Stock</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $products->sum('stock_quantity') }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Oldest Days</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">
                    @php
                        $oldestExpire = $products->sortBy('expire_date')->first();
                        $oldestDays = $oldestExpire ? \Carbon\Carbon::parse($oldestExpire->expire_date)->diffInDays(now()) : 0;
                    @endphp
                    {{ $oldestDays }}
                </div>
            </div>
        </div>
        @endif

        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#ffeeee; border-bottom:2px solid #ffcccc; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#cc0000;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Products with Expired Dates
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                @if($products->count() > 0)
                    <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
                        <table class="table" style="margin:0; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#ffeeee; border-bottom:2px solid #ffcccc;">
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">SL.</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Image</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Product Name</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Code</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Category</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Brand</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Expire Date</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Days Expired</th>
                                    <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #ffcccc;">Stock</th>
                                    <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                @php
                                    $expireDate = \Carbon\Carbon::parse($product->expire_date);
                                    $today = \Carbon\Carbon::now();
                                    $daysExpired = $expireDate->diffInDays($today);
                                @endphp
                                <tr style="background:#fff9f9; border-bottom:1px solid #ffcccc;">
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc;">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc;">
                                        @if($product->image && file_exists(storage_path('app/public/' . $product->image)))
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #333333;">
                                        @else
                                            <div style="width:40px; height:40px; background:#f5f5f5; border:1px solid #333333; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#666666; font-size:12px;">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ $product->name }}</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ $product->code }}</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ $product->category->name ?? '-' }}</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ $product->brand->name ?? '-' }}</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ date('d/m/Y', strtotime($product->expire_date)) }}</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ $daysExpired }} days</td>
                                    <td style="padding:12px; border-right:1px solid #ffcccc; border-bottom:1px solid #ffcccc; color:#000000;">{{ $product->stock_quantity }}</td>
                                    <td style="padding:12px; border-bottom:1px solid #ffcccc;">
                                        <div class="btn-group btn-group-sm" style="gap:4px;">
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="disposeProduct({{ $product->id }})" class="btn btn-sm" title="Mark as Disposed" style="background:#ffffff; color:#cc0000; border:2px solid #cc0000; padding:6px 10px;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div style="text-align:center; margin-top:20px;">
                        {{ $products->links() }}
                    </div>
                @else
                    <div style="text-align:center; padding:48px; color:#000000;">
                        <i class="fas fa-check-circle fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                        <h5 style="color:#000000;">No expired products found</h5>
                        <p style="color:#666666;">All products are within their valid date range.</p>
                        <a href="{{ route('products.index') }}" class="btn" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:10px 20px; margin-top:10px;">
                            <i class="fas fa-box mr-2"></i> View All Products
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
function disposeProduct(productId) {
    if (confirm('Are you sure you want to mark this expired product as disposed? This action will set the stock to 0.')) {
        fetch(`/admin/products/${productId}/dispose`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error disposing product. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error disposing product. Please try again.');
        });
    }
}
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }
@media print {
    body * { visibility: hidden; }
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .table-responsive { display: block !important; }
    .card { display: block !important; }
    .card-header { display: block !important; }
    .card-body { display: block !important; }
    table { display: block !important; }
    thead { display: table-header-group !important; }
    tbody { display: table-row-group !important; }
    tr { display: table-row !important; }
    th, td {
        display: table-cell !important;
        border: 1px solid #000 !important;
        padding: 8px !important;
        vertical-align: top !important;
    }
    th {
        background: #f5f5f5 !important;
        font-weight: bold !important;
        color: #000 !important;
    }
}
</style>
@stop
