@extends('admin.layouts.app')
@section('title', 'Transfer Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Transfer Details: {{ $transfer->transfer_no }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.fabric_transfer.history') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to History
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title">Summary</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Date:</th>
                                    <td>{{ $transfer->transfer_date }}</td>
                                </tr>
                                <tr>
                                    <th>From:</th>
                                    <td>{{ $transfer->fromWarehouse->cutting_master_name }}</td>
                                </tr>
                                <tr>
                                    <th>To:</th>
                                    <td>{{ $transfer->toWarehouse->cutting_master_name }}</td>
                                </tr>
                                <tr>
                                    <th>User:</th>
                                    <td>{{ $transfer->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Total Items:</th>
                                    <td>{{ $transfer->items->count() }} Rolls</td>
                                </tr>
                            </table>
                            @if($transfer->remarks)
                                <div class="mt-3">
                                    <strong>Remarks:</strong>
                                    <p class="text-muted small">{{ $transfer->remarks }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h3 class="card-title">Transferred Rolls</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fabric</th>
                                        <th>Roll No</th>
                                        <th>Qty (Mtr)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transfer->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->fabric->name }}</td>
                                            <td><span class="badge badge-info">{{ $item->fabricReceiptDetail->roll_number }}</span></td>
                                            <td>{{ $item->meter }} mtr</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
