@extends('admin.layouts.app')
@section('content')
    <style>
        .invoice-box {
            background: #fff;
            padding: 30px;
            border: 1px solid #e0e0e0;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.05);
            font-size: 14px;
            line-height: 20px;
            color: #333;
        }

        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-details h2 {
            margin: 0;
            font-weight: bold;
            color: #007bff;
        }

        .vendor-box,
        .po-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        table.invoice-table th {
            background: #007bff;
            color: #fff;
            text-align: center;
        }

        table.invoice-table td {
            text-align: center;
            vertical-align: middle;
        }

        .grand-total {
            font-size: 18px;
            font-weight: bold;
            background: #f1f1f1;
        }

        @media print {
            .action-buttons {
                display: none !important;
            }
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1>Fabric Purchase Order ({{ $data->sku }})</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.purchase_order.download_report', ['id' => $data->id]) }}"
                            class="btn btn-success">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <div class="invoice-box">
                    {{-- Header --}}
                    <div class="row invoice-header">
                        <div class="col-md-6 company-details">
                            @if($data->company)
                                <h2>{{ $data->company->name }}</h2>
                                <p class="mb-0">{{ $data->company->address }}</p>
                                <p class="mb-0">{{ $data->company->email }}</p>
                                <p class="mb-0"><b>Phone:</b> {{ $data->company->phone }}</p>
                                <p class="mb-0"><b>GST:</b> {{ $data->company->gst_number }}</p>
                            @else
                                <h2>{{ $general_setting->website_name }}</h2>
                                <p class="mb-0">{{ $general_setting->address }}</p>
                                <p class="mb-0">{{ $general_setting->email }}</p>
                                <p class="mb-0">Phone: {{ $general_setting->phone }}</p>
                            @endif
                            <p class="mt-2"><b>Delivery Warehouse Address:</b> {{ $data->fabric_warehouse->address ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <img src="{{ $general_setting->logo }}" height="80" alt="Logo">
                        </div>
                    </div>

                    {{-- Vendor & PO Info --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="vendor-box h-100" style="border: 1px solid #dee2e6;">
                                <h5 class="text-primary border-bottom pb-2 mb-3"><b><i class="fas fa-user-tie"></i> VENDOR DETAILS</b></h5>
                                <div class="row mb-2">
                                    <div class="col-4"><b>Name</b></div>
                                    <div class="col-8"><b>: {{ $data->vendor->name ?? 'N/A' }}</b></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><b>Email</b></div>
                                    <div class="col-8"><b>: {{ $data->vendor->email ?? 'N/A' }}</b></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><b>Phone</b></div>
                                    <div class="col-8"><b>: {{ $data->vendor->phone ?? 'N/A' }}</b></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><b>Address</b></div>
                                    <div class="col-8"><b>: {{ $data->vendor->address ?? 'N/A' }}</b></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="po-box h-100" style="border: 1px solid #dee2e6;">
                                <h5 class="text-primary border-bottom pb-2 mb-3 text-right"><b>PURCHASE ORDER INFO <i class="fas fa-info-circle"></i></b></h5>
                                <div class="row mb-2">
                                    <div class="col-5 text-right"><b>PO Number</b></div>
                                    <div class="col-7"><b>: {{ $data->sku }}</b></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-right"><b>PO Date</b></div>
                                    <div class="col-7"><b>: {{ getformatDate($data->date) }}</b></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-right"><b>Delivery Date</b></div>
                                    <div class="col-7"><b>: {{ getformatDate($data->delivery_date) }}</b></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-right"><b>Transport</b></div>
                                    <div class="col-7"><b>: {{ $data->transport ?? 'N/A' }}</b></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items --}}
                    <h5 class="mt-4 mb-3 text-primary">Order Items</h5>
                    <table class="table table-bordered invoice-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <!-- <th>Item SKU</th> -->
                                <th>Fabric</th>
                                <th>Meters</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp

                            @foreach($data->items as $index => $item)

                                @php
                                    $total = null;

                                    if ($item->price > 0) {
                                        $total = $item->meter * $item->price;
                                        $grandTotal += $total;
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        {{ $item->fabric->name }}
                                        <br>
                                        <small class="text-muted"><b>Composition: </b>{{ $item->fabric->fabric_composition->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $item->meter }}</td>

                                    <td>
                                        {{ $item->price > 0 ? getIndianCurrency($item->price) : 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $total !== null ? getIndianCurrency($total) : 'N/A' }}
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="grand-total">
                                <td colspan="4" class="text-right">Grand Total</td>
                                <td>{{ $grandTotal > 0 ? getIndianCurrency($grandTotal) : 'N/A' }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="text-primary"><b>Delivery Warehouse Address</b></h5>
                            <div style="background:#f1f1f1; padding:12px; border-radius:6px;">
                                {{ $data->fabric_warehouse->address ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary"><b>Remark</b></h5>
                            <div style="background:#f1f1f1; padding:12px; border-radius:6px;">
                                {{ $data->remark ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <p><b>Authorized Signature</b></p>
                            <p>____________________</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <p class="text-primary"><b>Thank you for your business!</b></p>
                        </div>
                    </div>

                    <div class="mt-4 text-right action-buttons">
                        <a href="{{ route('admin.purchase_order.index') }}" class="btn btn-secondary">Back</a>
                        <button onclick="window.print()" class="btn btn-primary">Print</button>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection