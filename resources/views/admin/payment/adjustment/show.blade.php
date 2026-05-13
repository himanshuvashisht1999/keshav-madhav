@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Adjustment Batch: {{ $batchId }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.payment.adjustment.index') }}">Adjustment List</a></li>
                        <li class="breadcrumb-item active">View Batch</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Batch Summary</h3>
                </div>
                <div class="card-body">
                    @php 
                        $first = $adjustments->first();
                        $total = $adjustments->sum('amount');
                    @endphp
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Date:</strong> {{ date('d-M-Y', strtotime($first->date)) }}
                        </div>
                        <div class="col-md-3">
                            <strong>To Account:</strong> {{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Type:</strong> 
                            @if($first->type == 'credit')
                                <span class="badge badge-success">Credit (+)</span>
                            @else
                                <span class="badge badge-danger">Debit (-)</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-right">
                            <strong>Total Amount:</strong> <span class="h4 text-primary">{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Master Type</th>
                                <th>Item (From)</th>
                                <th>Amount</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustments as $adj)
                            <tr>
                                <td>{{ $adj->master->name ?? 'N/A' }}</td>
                                <td>{{ $adj->entity_name }}</td>
                                <td>{{ number_format($adj->amount, 2) }}</td>
                                <td>{{ str_replace('[Dist] ', '', $adj->remarks) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total:</th>
                                <th colspan="2">{{ number_format($total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('admin.payment.adjustment.index') }}" class="btn btn-default">Back to List</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
