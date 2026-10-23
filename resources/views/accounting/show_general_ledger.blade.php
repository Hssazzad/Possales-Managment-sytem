@extends('adminlte::page')

@section('title', 'Show General Ledger')

@section('content_header')
    <h1>Show General Ledger</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Income Statement</h3></div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                    @foreach($income as $row)
                        <tr>
                            <td>{{ $row->gl_group_name }}</td>
                            <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold"><td>Total Income</td><td class="text-right">{{ number_format((float) $totalIncome, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title">Balance Sheet</h3></div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                    @foreach($assets as $row)
                        <tr>
                            <td>{{ $row->gl_group_name }}</td>
                            <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold"><td>Total Asset</td><td class="text-right">{{ number_format((float) $totalAsset, 2) }}</td></tr>
                    @foreach($liabilities as $row)
                        <tr>
                            <td>{{ $row->gl_group_name }}</td>
                            <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold"><td>Total Liability</td><td class="text-right">{{ number_format((float) $totalLiability, 2) }}</td></tr>
                    <tr class="font-weight-bold text-success"><td>Net Income (Income − Expenditure)</td><td class="text-right">{{ number_format((float) $netIncome, 2) }}</td></tr>
                    <tr class="font-weight-bold"><td>Total Liabilities &amp; Income</td><td class="text-right">{{ number_format((float) $totalLiabEquity, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">Expenditure Statement</h3></div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0"><tbody>
                @foreach($expenses as $row)
                    <tr><td>{{ $row->gl_group_name }}</td><td class="text-right">{{ number_format((float) $row->balance, 2) }}</td></tr>
                @endforeach
                <tr class="font-weight-bold"><td>Total Expenditure</td><td class="text-right">{{ number_format((float) $totalExpenditure, 2) }}</td></tr>
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@stop
