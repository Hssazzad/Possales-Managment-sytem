@extends('adminlte::page')

@section('title', 'Expired Products | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
    <style>
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .stat-card-icon {
        width: 48px; height: 48px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .stat-card-icon.red    { background: #fee2e2; color: #dc2626; }
    .stat-card-icon.orange { background: #ffedd5; color: #ea580c; }
    .stat-card-icon.blue   { background: #dbeafe; color: #2563eb; }
    .stat-card-value {
        font-size: 26px;
        font-weight: 700;
        color: #111;
        line-height: 1;
    }
    .stat-card-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .days-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-dispose {
        padding: 5px 10px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: background .15s;
    }
    .btn-dispose:hover { background: #fecaca; }

    .btn-edit-sm {
        padding: 5px 10px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #93c5fd;
        border-radius: 6px;
        font-size: 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background .15s;
    }
    .btn-edit-sm:hover { background: #dbeafe; color: #1d4ed8; text-decoration: none; }

    .flash-msg {
        position: fixed;
        top: 80px; right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn .3s ease-out, slideOut .3s ease-in 2.7s forwards;
    }
    @keyframes slideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
    @keyframes slideOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(20px); } }
    </style>
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">

        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="expiredSearch" placeholder="Search expired products...">
            </div>
            <div class="action-buttons">
                <a href="{{ route('products.index') }}" class="btn-add-new" style="background:#6b7280; text-decoration:none;">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Products
                </a>
            </div>
        </div>

        @if($products && $products->count() > 0)

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <div class="stat-card-icon red">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <div class="stat-card-value">{{ $products->total() }}</div>
                        <div class="stat-card-label">Total Expired Products</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <div class="stat-card-value">{{ $products->sum('stock_quantity') }}</div>
                        <div class="stat-card-label">Expired Units in Stock</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        @php
                            $oldest = $products->sortBy('expire_date')->first();
                            $oldestDays = $oldest ? (int) \Carbon\Carbon::parse($oldest->expire_date)->diffInDays(now()) : 0;
                        @endphp
                        <div class="stat-card-value">{{ $oldestDays }}</div>
                        <div class="stat-card-label">Days (Oldest Expired)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)">
                        </th>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th class="text-center">Expire Date</th>
                        <th class="text-center">Days Expired</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        $daysExpired = (int) \Carbon\Carbon::parse($product->expire_date)->diffInDays(now());
                    @endphp
                    <tr class="expired-row">
                        <td><input type="checkbox" class="expired-checkbox" value="{{ $product->id }}"></td>
                        <td class="text-muted">#{{ $product->id }}</td>
                        <td>
                            @if($product->image && file_exists(storage_path('app/public/' . $product->image)))
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                     style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #e5e7eb;">
                            @else
                                <div style="width:40px; height:40px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                                    <i class="fas fa-box"></i>
                                </div>
                            @endif
                        </td>
                        <td class="font-weight-bold">{{ $product->name }}</td>
                        <td>
                            <span style="padding:3px 8px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; font-size:12px; font-weight:600; color:#374151; font-family:monospace;">
                                {{ $product->code }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $product->category?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $product->brand?->name ?? '—' }}</td>
                        <td class="text-center" style="color:#dc2626; font-weight:600; font-size:13px;">
                            {{ date('d M Y', strtotime($product->expire_date)) }}
                        </td>
                        <td class="text-center">
                            <span class="days-badge">{{ $daysExpired }} days</span>
                        </td>
                        <td class="text-center font-weight-bold">{{ $product->stock_quantity ?? 0 }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center" style="gap:6px;">
                                <a href="{{ route('products.edit', $product->id) }}" class="btn-edit-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="disposeProduct({{ $product->id }})" class="btn-dispose" title="Dispose">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted text-sm">
                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} entries
            </div>
            <div>{{ $products->links() }}</div>
        </div>
        @endif

        @else

        <!-- Empty State -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" disabled></th>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th class="text-center">Expire Date</th>
                        <th class="text-center">Days Expired</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3" style="color:#22c55e;"></i>
                                <h5>No Expired Products</h5>
                                <p>All products are within their valid date range.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @endif

    </div>
</div>
@stop

@section('adminlte_js')
<script>
// ── Search ───────────────────────────────────────────
document.getElementById('expiredSearch').addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('.expired-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(s) ? '' : 'none';
    });
});

// ── Checkboxes ───────────────────────────────────────
function toggleAllCheckboxes(checkbox) {
    document.querySelectorAll('.expired-checkbox').forEach(cb => cb.checked = checkbox.checked);
}
document.querySelectorAll('.expired-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const all = document.querySelectorAll('.expired-checkbox').length;
        const chk = document.querySelectorAll('.expired-checkbox:checked').length;
        document.getElementById('selectAll').checked = (all === chk);
    });
});

// ── Dispose ──────────────────────────────────────────
function disposeProduct(productId) {
    if (!confirm('Are you sure you want to dispose of this expired product?\n\nThis will set stock quantity to 0 and cannot be undone!')) return;

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
            showFlash('Product marked as disposed.', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showFlash('Error disposing product. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFlash('Error disposing product. Please try again.', 'error');
    });
}

// ── Flash Message ─────────────────────────────────────
function showFlash(message, type) {
    const styles = {
        success: { bg: '#dcfce7', color: '#15803d', border: '#86efac' },
        error:   { bg: '#fee2e2', color: '#dc2626', border: '#fca5a5' },
    };
    const s = styles[type] || styles.success;
    const el = document.createElement('div');
    el.className = 'flash-msg';
    el.style.cssText = `background:${s.bg}; color:${s.color}; border:1px solid ${s.border};`;
    el.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'times'}-circle mr-2"></i>${message}`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}
</script>
@stop