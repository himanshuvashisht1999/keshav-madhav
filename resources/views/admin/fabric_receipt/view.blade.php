@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Receipt</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.fabric_receipt.index') }}">Fabric Receipts</a></li>
                        <li class="breadcrumb-item active">View Receipt</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Receipt Info -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Receipt Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><strong>SKU:</strong> {{ $data->sku }}</div>
                        <div class="col-md-4"><strong>Vendor:</strong> {{ $data->vendor->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Truck Number:</strong> {{ $data->truck_number }}</div>
                        <div class="col-md-4"><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($data->time)->format('d M Y, h:i A') }}</div>
                        <div class="col-md-4"><strong>Rolls / Boxes:</strong> {{ $data->roll }}</div>
                        <div class="col-md-4"><strong>Received By:</strong> {{ $data->received_by }}</div>
                        <div class="col-md-4">
                            <strong>Shipment Photo:</strong><br>
                            @if($data->shipment_photo)
                                <img src="{{$data->shipment_photo}}" alt="Shipment" height="100">
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Details -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Receipt Details</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Purchase Order SKU</th>
                                <th>Fabric SKU</th>
                                <!-- <th>Fabric Name</th> -->
                                <th>Meters</th>
                                <th>Rolls</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->details as $key => $detail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $detail->purchase_order->sku ?? '-' }}</td>
                                    <td>{{ $detail->fabric_sku }}</td>
                                    <!-- <td>{{ $detail->fabric->name ?? '-' }}</td> -->
                                    <td>{{ $detail->meter }}</td>
                                    <td>{{ $detail->roll }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No details found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-2">
                <a href="{{ route('admin.fabric_receipt.index') }}" class="btn btn-primary">Back to List</a>
            </div>

        </div>
    </section>
</div>
@endsection
