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
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Production Purchase Order ({{ $po->po_number }})</h1>
                </div>
                <div class="col-sm-6 text-right action-buttons">
                    <a href="{{ route('admin.product_order.poList') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.product_order.downloadBulkPO', $po->id) }}" class="btn btn-success">
                        <i class="fa fa-file-pdf"></i> Download PDF
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
                        <h2>{{ $general_setting->website_name ?? 'SNAPKID' }}</h2>
                        <p class="mb-0">{{ $general_setting->address ?? '' }}</p>
                        <p class="mb-0">{{ $general_setting->email ?? '' }}</p>
                        <p class="mb-0"><b>Phone:</b> {{ $general_setting->phone ?? '' }}</p>
                    </div>
                    <div class="col-md-6 text-right">
                        @if($general_setting && $general_setting->logo)
                            <img src="{{ $general_setting->logo }}" height="80" alt="Logo">
                        @endif
                    </div>
                </div>

                {{-- Vendor & PO Info --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="vendor-box h-100" style="border: 1px solid #dee2e6;">
                            <h5 class="text-primary border-bottom pb-2 mb-3"><b><i class="fas fa-user-tie"></i> PO TO</b></h5>
                            <div class="row mb-2">
                                <div class="col-4"><b>Name</b></div>
                                <div class="col-8"><b>: 
                                    @if($po->vendor)
                                        {{ $po->vendor->name }}
                                    @elseif($po->customer)
                                        {{ $po->customer->name }}
                                    @else
                                        N/A
                                    @endif
                                </b></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><b>Mobile</b></div>
                                <div class="col-8"><b>: 
                                    @if($po->vendor)
                                        {{ $po->vendor->mobile ?? 'N/A' }}
                                    @elseif($po->customer)
                                        {{ $po->customer->mobile ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </b></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><b>Address</b></div>
                                <div class="col-8"><b>: 
                                    @if($po->vendor)
                                        {{ $po->vendor->address ?? 'N/A' }}
                                    @elseif($po->customer)
                                        {{ $po->customer->address ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </b></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="po-box h-100" style="border: 1px solid #dee2e6;">
                            <h5 class="text-primary border-bottom pb-2 mb-3 text-right"><b>PO INFO <i class="fas fa-info-circle"></i></b></h5>
                            <div class="row mb-2">
                                <div class="col-5 text-right"><b>PO Number</b></div>
                                <div class="col-7"><b>: {{ $po->po_number }}</b></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-right"><b>Order No</b></div>
                                <div class="col-7"><b>: {{ $po->orderMain->sku ?? 'N/A' }}</b></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-right"><b>Date</b></div>
                                <div class="col-7"><b>: {{ $po->created_at->format('j M Y') }}</b></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-right"><b>Delivery Date</b></div>
                                <div class="col-7"><b>: {{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : 'N/A' }}</b></div>
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
                            <th>Design</th>
                            <th>Product Details</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalQty = 0; $totalAmount = 0; @endphp
                        @foreach($po->items as $index => $item)
                            @php 
                                $subtotal = $item->quantity * $item->rate;
                                $totalQty += $item->quantity;
                                $totalAmount += $subtotal;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $item->productSet->design_number ?? 'N/A' }}</strong></td>
                                <td class="text-left" style="text-align: left !important;">
                                    <small>
                                        <b>Color:</b> {{ $item->productSet->colors->name ?? 'N/A' }} | <b>Size:</b> {{ $item->productSet->size_set_name ?? 'N/A' }}<br>
                                        <b>Fabric:</b> {{ $item->fabric_names }}<br>
                                        <b>Pattern:</b> {{ $item->pattern->name ?? '-' }} | <b>Fitting:</b> {{ $item->master_fitting->name ?? '-' }}<br>
                                        <b>Belt:</b> {{ $item->belt ?? '-' }}
                                    </small>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->rate, 2) }}</td>
                                <td>{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="grand-total">
                            <td colspan="3" class="text-right">Total</td>
                            <td>{{ $totalQty }}</td>
                            <td></td>
                            <td>{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5 class="text-primary"><b>Remark</b></h5>
                        <div style="background:#f1f1f1; padding:12px; border-radius:6px;">
                            {{ $po->remark ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="row mt-5">
                    <div class="col-md-6">
                        <p><b>Authorized Signature</b></p>
                        <br><br>
                        <p>____________________</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <p class="text-primary"><b>Thank you for your business!</b></p>
                        <small>Processed By: {{ $po->items->first()->creator->name ?? 'System' }}</small>
                    </div>
                </div>

                <div class="mt-4 text-right action-buttons">
                    <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> Print</button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
