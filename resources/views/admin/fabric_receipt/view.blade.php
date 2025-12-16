@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">
                        Fabric Shipment Details ({{ $data->shipment_id }})
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- ================= Receipt Information ================= -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Receipt Information</h3>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-4 mb-2">
                            <strong>Shipment Number:</strong>
                            {{ $data->shipment_id }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>Vendor:</strong>
                            {{ $data->vendor->name ?? '-' }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>Cutting Master:</strong>
                            {{ $data->cutting_master->cutting_master_name ?? '-' }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>Date & Time:</strong>
                            {{ getformatDateTime($data->time) }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>Received By:</strong>
                            {{ $data->received_by ?? '-' }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>Challan Photo:</strong><br>
                            @if($data->challan_photo)
                                <img
                                    src="{{ $data->challan_photo }}"
                                    height="100"
                                    class="border rounded"
                                    alt="Challan Photo"
                                >
                            @else
                                -
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <!-- ================= Receipt Details ================= -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Receipt Details</h3>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Fabric</th>
                                <th>Roll No</th>
                                <th>Meter</th>
                                <th>QR Number</th>
                                <th>QR Code</th>
                                <th>Barcode</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($data->details as $key => $detail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        {{ $detail->fabric->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $detail->roll_number }}
                                    </td>

                                    <td>
                                        {{ $detail->meter }}
                                    </td>

                                    <td>
                                        {{ $detail->qrcode_number }}
                                    </td>

                                    <!-- QR Code -->
                                    <td>
                                        <img
                                            src="{{ $detail->qrcode }}"
                                            width="80"
                                            height="80"
                                            alt="QR Code"
                                        >
                                    </td>

                                    <!-- Barcode -->
                                    <td>
                                        <div style="display:flex; flex-direction:column; align-items:center;">
                                            <img
                                                src="{{ $detail->barcode }}"
                                                width="160"
                                                height="60"
                                                alt="Barcode"
                                            >
                                            <small style="margin-top:4px; font-weight:600; letter-spacing:1px;">
                                                {{ $detail->qrcode_number }}
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No details found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= Back Button ================= -->
            <div class="mt-3">
                <a href="{{ route('admin.fabric_receipt.index') }}" class="btn btn-primary">
                    Back to List
                </a>
            </div>

        </div>
    </section>
</div>
@endsection
