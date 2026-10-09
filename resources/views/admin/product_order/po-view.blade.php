@extends('admin.layouts.app')

@section('content')
<style>
    @media print {
        .erp-header-bar,
        .action-buttons,
        .main-sidebar,
        .main-header {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .erp-voucher-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-file-invoice text-primary"></i> Production Purchase Order:
            <span class="erp-badge-yellow px-2 py-1 ml-1 rounded font-weight-bold">{{ $po->po_number }}</span>
        </div>
        <div class="erp-header-actions action-buttons">
            <a href="{{ route('admin.product_order.poList') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('admin.product_order.editBulkPO', $po->id) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-edit text-primary"></i> Edit PO
            </a>
            <a href="{{ route('admin.product_order.downloadBulkPO', $po->id) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="erp-voucher-sheet">
        <!-- Header Row: Company & Logo -->
        <div class="row erp-voucher-header align-items-center">
            <div class="col-8">
                <h4 class="erp-voucher-title">{{ $general_setting->website_name ?? 'SNAPKID' }}</h4>
                <div class="erp-company-meta">
                    <div>{{ $general_setting->address ?? '' }}</div>
                    <div>
                        @if($general_setting->phone)<span><b>Phone:</b> {{ $general_setting->phone }}</span>@endif
                        @if($general_setting->email)<span class="ml-2"><b>Email:</b> {{ $general_setting->email }}</span>@endif
                    </div>
                </div>
            </div>
            <div class="col-4 text-right">
                @if($general_setting && $general_setting->logo)
                    <img src="{{ $general_setting->logo }}" height="55" alt="Logo" style="object-fit: contain;">
                @endif
            </div>
        </div>

        <!-- Two-Column Voucher Details -->
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <div class="erp-card h-100 mb-0">
                    <div class="erp-card-header py-1">
                        <span class="erp-card-title">
                            <i class="fas fa-user-tie text-primary mr-1"></i> PO Assigned To
                        </span>
                    </div>
                    <div class="erp-card-body p-2">
                        <table class="erp-info-table">
                            <tr>
                                <td class="label-col">Party Type</td>
                                <td class="val-col">: {{ $po->vendor_id ? 'Vendor' : 'Customer' }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Name</td>
                                <td class="val-col font-weight-bold">: 
                                    @if($po->vendor)
                                        {{ $po->vendor->name }}
                                    @elseif($po->customer)
                                        {{ $po->customer->name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Mobile</td>
                                <td class="val-col">: 
                                    @if($po->vendor)
                                        {{ $po->vendor->mobile ?? $po->vendor->phone ?? 'N/A' }}
                                    @elseif($po->customer)
                                        {{ $po->customer->mobile ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Address</td>
                                <td class="val-col">: 
                                    @if($po->vendor)
                                        {{ $po->vendor->address ?? 'N/A' }}
                                    @elseif($po->customer)
                                        {{ $po->customer->address ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-2">
                <div class="erp-card h-100 mb-0">
                    <div class="erp-card-header py-1">
                        <span class="erp-card-title">
                            <i class="fas fa-file-invoice text-primary mr-1"></i> PO Information
                        </span>
                    </div>
                    <div class="erp-card-body p-2">
                        <table class="erp-info-table">
                            <tr>
                                <td class="label-col">PO Number</td>
                                <td class="val-col text-primary font-weight-bold">: {{ $po->po_number }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Sales Order</td>
                                <td class="val-col font-weight-bold">: {{ $po->orderMain->sku ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">PO Date</td>
                                <td class="val-col">: {{ $po->created_at->format('j M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Delivery Date</td>
                                <td class="val-col">: {{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Table -->
        <h6 class="font-weight-bold text-uppercase border-bottom pb-1 mb-2" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
            <i class="fas fa-boxes mr-1 text-primary"></i> Order Items
        </h6>

        <div class="table-responsive mb-3">
            <table class="erp-table table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 130px;">Design</th>
                        <th>Product Details</th>
                        <th style="width: 100px;" class="text-right">Quantity</th>
                        <th style="width: 110px;" class="text-right">Rate (Rs.)</th>
                        <th style="width: 120px;" class="text-right font-weight-bold">Total (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; $totalAmount = 0; @endphp
                    @foreach($po->items as $index => $item)
                        @php 
                            $subtotal = $item->quantity * $item->rate;
                            $totalQty += $item->quantity;
                            $totalAmount += $subtotal;

                            $ratioStr = '-';
                            if(!empty($item->productSet->size_measurement->size_group)) {
                                $sizes = array_map('trim', explode(',', $item->productSet->size_measurement->size_group));
                                $counts = array_count_values($sizes);
                                $parts = [];
                                foreach($counts as $s => $c) {
                                    $parts[] = $s . ':' . $c;
                                }
                                $ratioStr = implode(', ', $parts);
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-weight-bold">{{ $item->productSet->design_number ?? 'N/A' }}</td>
                            <td>
                                <div style="font-size: 11.5px; line-height: 1.4;">
                                    <b>Color:</b> {{ $item->productSet->colors->name ?? 'N/A' }} &nbsp;|&nbsp; 
                                    <b>Size:</b> {{ $item->productSet->size_set_name ?? 'N/A' }} &nbsp;|&nbsp; 
                                    <b>Ratio:</b> <span class="text-success font-weight-bold">{{ $ratioStr }}</span><br>
                                    <b>Fabric:</b> {{ $item->fabric_names ?: 'N/A' }}<br>
                                    <b>Pattern:</b> {{ $item->pattern->name ?? '-' }} &nbsp;|&nbsp; 
                                    <b>Fitting:</b> {{ $item->master_fitting->name ?? '-' }}
                                    @if($item->belt) &nbsp;|&nbsp; <b>Belt:</b> {{ $item->belt }} @endif
                                </div>
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format($item->quantity) }}</td>
                            <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                            <td class="text-right font-weight-bold text-primary">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <td colspan="3" class="text-right">TOTAL:</td>
                        <td class="text-right font-weight-bold text-primary">{{ number_format($totalQty) }} Pcs</td>
                        <td></td>
                        <td class="text-right font-weight-bold text-primary">Rs. {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if(!empty($po->remark))
            <div class="mb-3 p-2 border rounded bg-light" style="font-size: var(--erp-font-sm);">
                <strong class="text-dark">Remark:</strong> {{ $po->remark }}
            </div>
        @endif

        <!-- Signatures & Footer -->
        <div class="row pt-4 mt-3 border-top">
            <div class="col-6 text-center">
                <div class="pt-4" style="border-top: 1px dashed #aaa; width: 60%; margin: 0 auto;">
                    <strong>Prepared By</strong>
                    <div class="small text-muted">{{ $po->items->first()->creator->name ?? 'System' }}</div>
                </div>
            </div>
            <div class="col-6 text-center">
                <div class="pt-4" style="border-top: 1px dashed #aaa; width: 60%; margin: 0 auto;">
                    <strong>Authorized Signatory</strong>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-3">
            Thank you for your business! This is a system generated purchase order.
        </div>
    </div>
</div>
@endsection
