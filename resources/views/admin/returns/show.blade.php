@extends('adminlte::page')

@section('title', 'Return Details | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        <div class="top-toolbar">
            <h5 class="mb-0 font-weight-bold">
                <i class="fas fa-undo mr-2 text-warning"></i> Return #{{ $return->id }}
                <span class="badge badge-light border ml-2">{{ ucfirst($return->status) }}</span>
            </h5>
            <div class="action-buttons">
                <a href="{{ route('returns.index') }}" class="btn-export">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Returns
                </a>
                <a href="{{ route('returns.receipt', $return->id) }}" target="_blank" class="btn-export">
                    <i class="fas fa-print mr-1"></i> Print Receipt
                </a>
                <a href="{{ route('returns.edit', $return->id) }}" class="btn-add-new">
                    <i class="fas fa-edit mr-1"></i> Edit / Approve
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered bg-white">
                    <tr><th style="width:160px;">Sale Invoice</th><td class="font-weight-bold text-primary">{{ $return->sale?->invoice_number ?? ('Sale #'.$return->sale_id) }}</td></tr>
                    <tr><th>Customer</th><td>{{ $return->customer?->name ?? 'Walk-in Customer' }}</td></tr>
                    <tr><th>Product</th><td class="font-weight-bold">{{ $return->product?->name ?? 'Unknown' }} <span class="text-muted">× {{ $return->quantity }}</span></td></tr>
                    <tr><th>Refund</th><td class="font-weight-bold text-danger">৳{{ number_format($return->refund_amount, 2) }} <span class="text-muted">({{ ucfirst($return->refund_method) }})</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered bg-white">
                    <tr><th style="width:160px;">Return Date</th><td>{{ optional($return->return_date)->format('d M Y') ?? $return->created_at->format('d M Y') }}</td></tr>
                    <tr><th>Status</th><td>
                        @if($return->status == 'completed')
                            <span class="badge badge-success">Completed — stock restored</span>
                        @elseif($return->status == 'approved')
                            <span class="badge badge-info">Approved — stock restored</span>
                        @elseif($return->status == 'pending')
                            <span class="badge badge-warning">Pending — stock NOT yet restored</span>
                        @else
                            <span class="badge badge-danger">{{ ucfirst($return->status) }}</span>
                        @endif
                    </td></tr>
                    <tr><th>Reason</th><td>{{ $return->reason }}</td></tr>
                    <tr><th>Notes</th><td>{{ $return->notes ?: '—' }}</td></tr>
                </table>
            </div>
        </div>

        @if(in_array($return->status, ['pending']))
        <div class="alert alert-warning">
            <i class="fas fa-info-circle mr-1"></i>
            This return is <strong>pending</strong> — stock has not been restored yet.
            Approve it to restore stock and update the sale status.
        </div>
        <form method="POST" action="{{ route('returns.update', $return->id) }}" class="form-inline" style="gap:10px;">
            @csrf @method('PUT')
            <input type="hidden" name="quantity" value="{{ $return->quantity }}">
            <input type="hidden" name="reason" value="{{ $return->reason }}">
            <input type="hidden" name="refund_amount" value="{{ $return->refund_amount }}">
            <input type="hidden" name="refund_method" value="{{ $return->refund_method }}">
            <input type="hidden" name="notes" value="{{ $return->notes }}">
            <input type="hidden" name="status" value="completed">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check mr-1"></i> Approve & Restore Stock
            </button>
            <button type="submit" class="btn btn-danger" onclick="this.form.status.value='rejected';return confirm('Reject this return?');">
                <i class="fas fa-times mr-1"></i> Reject
            </button>
        </form>
        @endif
    </div>
</div>
@stop
