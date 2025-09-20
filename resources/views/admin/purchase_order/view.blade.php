@extends('admin.layouts.app')
@section('content')
<style>
    .invoice-box {
        background: #fff;
        padding: 30px;
        border: 1px solid #e0e0e0;
        box-shadow: 0px 0px 8px rgba(0,0,0,0.05);
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
    .vendor-box, .po-box {
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
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="">Purchase Order</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="invoice-box">
                {{-- Header --}}
                <div class="row invoice-header">
                    <div class="col-md-6 company-details">
                        <h2>{{ $general_setting->website_name }}</h2>
                        <p class="mb-0">{{ $general_setting->address }}</p>
                        <p class="mb-0">{{ $general_setting->email }}</p>
                        <p>Phone: {{ $general_setting->phone }}</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <img src="{{ $general_setting->logo }}" height="80" alt="Logo">
                    </div>
                </div>

                {{-- Vendor & PO Info --}}
                <div class="row">
                    <div class="col-md-6 vendor-box">
                        <h5 class="text-primary"><b>Vendor Details</b></h5>
                        <p class="mb-1"><b>Name:</b> {{ $data->vendor->name ?? 'N/A' }}</p>
                        <p class="mb-1"><b>Email:</b> {{ $data->vendor->email ?? 'N/A' }}</p>
                        <p class="mb-1"><b>Phone:</b> {{ $data->vendor->phone ?? 'N/A' }}</p>
                        <p><b>Address:</b> {{ $data->vendor->address ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 po-box text-right">
                        <h5 class="text-primary"><b>Purchase Order Info</b></h5>
                        <p class="mb-1"><b>PO Number:</b> {{ $data->sku }}</p>
                        <p class="mb-1"><b>PO Date:</b> {{ \Carbon\Carbon::parse($data->date)->format('d-m-Y') }}</p>
                        <p><b>Delivery Date:</b> {{ \Carbon\Carbon::parse($data->delivery_date)->format('d-m-Y') }}</p>
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
                            @php $total = $item->meter * $item->price; $grandTotal += $total; @endphp
                            <tr>
                                <td>{{ $index+1 }}</td>
                                <!-- <td>{{ $item->sku }}</td> -->
                                <td>{{ $item->fabric->sku }}</td>
                                <td>{{ $item->meter }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="grand-total">
                            <td colspan="4" class="text-right">Grand Total</td>
                            <td>{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

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

                <div class="mt-4 text-right">
                    <a href="{{ route('admin.purchase_order.index') }}" class="btn btn-secondary">Back</a>
                    <button onclick="window.print()" class="btn btn-primary">Print</button>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
