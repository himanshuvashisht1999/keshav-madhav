@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Content Header -->
    <div class="erp-header-bar">
        <div>
            <div class="erp-header-title"><i class="fas fa-file-alt text-primary"></i> Item Receipt</div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid p-0">

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
                        <div class="col-md-4"><strong>Date & Time:</strong> {{ getformatDateTime($data->time) }}</div>
                        <div class="col-md-4"><strong>Boxes:</strong> {{ $data->box }}</div>
                        <div class="col-md-4"><strong>Received By:</strong> {{ $data->received_by }}</div>
                        <div class="col-md-4">
                            <strong>Challan Photo:</strong><br>
                            @if($data->challan_photo)
                                <img src="{{$data->challan_photo}}" alt="Challan" height="100">
                            @else
                                -
                            @endif
                        </div>
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
                <div class="erp-card-body p-2 table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Purchase Order</th>
                                <th>Item SKU</th>
                                <th>Box</th>
                                <th>Quantity</th>
                                <th>Batch No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->details as $key => $detail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $detail->purchase_order->sku ?? '-' }}</td>
                                    <td>{{ $detail->item_sku }}</td>
                                    <td>{{ $detail->box }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>{{ $detail->batch_no }}</td>
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
                <a href="{{ route('admin.item_receipt.index') }}" class="btn btn-primary">Back to List</a>
            </div>

        </div>
    </section>
</div>
@endsection