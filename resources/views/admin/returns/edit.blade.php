@extends('adminlte::page')

@section('title', 'Edit Return | PosSales')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        <div class="top-toolbar">
            <h5 class="mb-0 font-weight-bold">
                <i class="fas fa-edit mr-2 text-warning"></i> Edit Return #{{ $return->id }}
            </h5>
            <div class="action-buttons">
                <a href="{{ route('returns.index') }}" class="btn-export">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="alert alert-info">
            Sale: <strong class="text-primary">{{ $return->sale?->invoice_number ?? ('#'.$return->sale_id) }}</strong>
            &nbsp;|&nbsp; Product: <strong>{{ $return->product?->name }}</strong>
            &nbsp;|&nbsp; Current status: <strong>{{ ucfirst($return->status) }}</strong>
            @if(in_array($return->status, ['approved','completed']))
                <br><small>Stock was already restored for this return. Changing to Pending/Rejected will deduct stock again. Changing quantity will adjust the difference.</small>
            @else
                <br><small>Stock NOT yet restored. Changing to Approved/Completed will restore stock.</small>
            @endif
        </div>

        <form method="POST" action="{{ route('returns.update', $return->id) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" value="{{ old('quantity', $return->quantity) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Refund Amount <span class="text-danger">*</span></label>
                        <input type="number" name="refund_amount" class="form-control" min="0" step="0.01" value="{{ old('refund_amount', $return->refund_amount) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Refund Method <span class="text-danger">*</span></label>
                        <select name="refund_method" class="form-control" required>
                            @foreach(['cash','card','bank','credit'] as $m)
                                <option value="{{ $m }}" {{ old('refund_method', $return->refund_method) === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            @foreach(['pending','approved','completed','rejected'] as $s)
                                <option value="{{ $s }}" {{ old('status', $return->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Reason <span class="text-danger">*</span></label>
                <input type="text" name="reason" class="form-control" maxlength="500" value="{{ old('reason', $return->reason) }}" required>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes', $return->notes) }}</textarea>
            </div>
            <div class="text-right">
                <a href="{{ route('returns.index') }}" class="btn btn-default mr-2">Cancel</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@stop
