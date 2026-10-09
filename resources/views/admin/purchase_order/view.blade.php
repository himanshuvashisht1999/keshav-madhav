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
                <i class="fas fa-file-invoice text-primary"></i> Fabric Purchase Order:
                <span class="erp-badge-yellow px-2 py-1 ml-1 rounded font-weight-bold">{{ $data->sku }}</span>
            </div>
            <div class="erp-header-actions action-buttons">
                <a href="{{ route('admin.purchase_order.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('admin.purchase_order.edit', ['id' => $data->id]) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-edit text-primary"></i> Edit
                </a>
                <a href="{{ route('admin.purchase_order.send_whatsapp_report', ['id' => $data->id]) }}"
                    class="btn-erp btn-erp-success"
                    onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $data->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="{{ route('admin.purchase_order.download_report', ['id' => $data->id]) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-file-pdf text-danger"></i> PDF
                </a>
                <button onclick="window.print()" class="btn-erp btn-erp-primary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="erp-voucher-sheet">
            <!-- Header Row: Company & Logo -->
            <div class="row erp-voucher-header align-items-center">
                <div class="col-8">
                    @if($data->company)
                        <h4 class="erp-voucher-title">{{ $data->company->name }}</h4>
                        <div class="erp-company-meta">
                            <div>{{ $data->company->address }}</div>
                            <div>
                                @if($data->company->phone)<span><b>Phone:</b> {{ $data->company->phone }}</span>@endif
                                @if($data->company->email)<span class="ml-2"><b>Email:</b> {{ $data->company->email }}</span>@endif
                                @if($data->company->gst_number)<span class="ml-2"><b>GST:</b> {{ $data->company->gst_number }}</span>@endif
                            </div>
                        </div>
                    @else
                        <h4 class="erp-voucher-title">{{ $general_setting->website_name }}</h4>
                        <div class="erp-company-meta">
                            <div>{{ $general_setting->address }}</div>
                            <div>
                                @if($general_setting->phone)<span><b>Phone:</b> {{ $general_setting->phone }}</span>@endif
                                @if($general_setting->email)<span class="ml-2"><b>Email:</b> {{ $general_setting->email }}</span>@endif
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-4 text-right">
                    @if($general_setting->logo)
                        <img src="{{ $general_setting->logo }}" height="55" alt="Logo" style="object-fit: contain;">
                    @endif
                </div>
            </div>

            <!-- Two-Column Voucher Details -->
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="erp-card h-100 mb-0">
                        <div class="erp-card-header py-1">
                            <span class="erp-card-title"><i class="fas fa-user-tie text-primary mr-1"></i> Vendor Details</span>
                        </div>
                        <div class="erp-card-body p-2">
                            <table class="erp-info-table">
                                <tr>
                                    <td class="label-col">Name</td>
                                    <td class="val-col">: {{ $data->vendor->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label-col">Phone</td>
                                    <td class="val-col">: {{ $data->vendor->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label-col">Email</td>
                                    <td class="val-col">: {{ $data->vendor->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label-col">Address</td>
                                    <td class="val-col">: {{ $data->vendor->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="erp-card h-100 mb-0">
                        <div class="erp-card-header py-1">
                            <span class="erp-card-title"><i class="fas fa-file-invoice text-primary mr-1"></i> Purchase Order Info</span>
                        </div>
                        <div class="erp-card-body p-2">
                            <table class="erp-info-table">
                                <tr>
                                    <td class="label-col">PO Number</td>
                                    <td class="val-col text-primary font-weight-bold">: {{ $data->sku }}</td>
                                </tr>
                                <tr>
                                    <td class="label-col">PO Date</td>
                                    <td class="val-col">: {{ getformatDate($data->date) }}</td>
                                </tr>
                                <tr>
                                    <td class="label-col">Delivery Date</td>
                                    <td class="val-col">: {{ getformatDate($data->delivery_date) }}</td>
                                </tr>
                                <tr>
                                    <td class="label-col">Transport</td>
                                    <td class="val-col">: {{ $data->transport ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="erp-card mb-3">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title"><i class="fas fa-layer-group text-primary mr-1"></i> Fabric Order Items</span>
                </div>
                <div class="table-responsive p-0">
                    <table class="erp-table table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Fabric</th>
                                <th>Composition</th>
                                <th style="width: 140px;" class="text-right">Meters / Qty</th>
                                <th style="width: 140px;" class="text-right">Rate / Price (₹)</th>
                                <th style="width: 160px;" class="text-right">Total Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalMeters = 0;
                                $grandTotal = 0;
                            @endphp

                            @foreach($data->items as $index => $item)
                                @php
                                    $total = null;
                                    if ($item->price > 0) {
                                        $total = $item->meter * $item->price;
                                        $grandTotal += $total;
                                    }
                                    $totalMeters += $item->meter;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="font-weight-bold">{{ $item->fabric->name }}</td>
                                    <td>{{ $item->fabric->fabric_composition->name ?? 'N/A' }}</td>
                                    <td class="text-right font-weight-bold text-primary">{{ number_format($item->meter, 2) }}</td>
                                    <td class="text-right">{{ $item->price > 0 ? getIndianCurrency($item->price) : 'N/A' }}</td>
                                    <td class="text-right font-weight-bold text-success">{{ $total !== null ? getIndianCurrency($total) : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="erp-table-grand-total">
                                <td colspan="3" class="text-right text-uppercase" style="letter-spacing: 0.5px;">Grand Total:</td>
                                <td class="text-right font-weight-bold grand-total-val">{{ number_format($totalMeters, 2) }}</td>
                                <td></td>
                                <td class="text-right font-weight-bold grand-total-val">{{ $grandTotal > 0 ? getIndianCurrency($grandTotal) : 'N/A' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Warehouse & Remarks Row -->
            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <div class="erp-card h-100 mb-0">
                        <div class="erp-card-header py-1">
                            <span class="erp-card-title"><i class="fas fa-warehouse text-primary mr-1"></i> Delivery Warehouse Address</span>
                        </div>
                        <div class="erp-card-body p-2" style="background: var(--erp-bg-card);">
                            {{ $data->fabric_warehouse->address ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="erp-card h-100 mb-0">
                        <div class="erp-card-header py-1">
                            <span class="erp-card-title"><i class="fas fa-comment-dots text-primary mr-1"></i> Remarks / Notes</span>
                        </div>
                        <div class="erp-card-body p-2" style="background: var(--erp-bg-card);">
                            {{ $data->remark ?? 'None' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature & Sign-off -->
            <div class="row pt-3 align-items-end" style="border-top: 1px dashed var(--erp-border);">
                <div class="col-6">
                    <div style="width: 180px; border-bottom: 1px solid var(--erp-border); height: 35px;"></div>
                    <div class="mt-1 font-weight-bold text-muted text-uppercase" style="font-size: var(--erp-font-xs);">Authorized Signature</div>
                </div>
                <div class="col-6 text-right">
                    <span class="text-muted font-italic" style="font-size: var(--erp-font-xs);">Thank you for your business!</span>
                </div>
            </div>
        </div>
    </div>
@endsection