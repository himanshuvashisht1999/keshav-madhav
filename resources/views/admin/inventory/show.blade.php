@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-box-open mr-2 text-primary"></i>Box Details
                        </h1>
                        <small class="text-muted">Viewing contents for Box: {{ $box_info->box_no ?: 'Direct' }} | Order: {{ $box_info->orderMain->sku ?? 'N/A' }}</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- LEFT COLUMN: INFO -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-primary py-3">
                                <h3 class="card-title font-weight-bold mb-0">General Information</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Order No</th>
                                        <td class="py-3 font-weight-bold">{{ $box_info->orderMain->sku ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Box No</th>
                                        <td class="py-3">
                                            @if($box_info->box_no)
                                                <span class="badge badge-info px-3">{{ $box_info->box_no }}</span>
                                            @else
                                                <span class="text-muted">Direct Packing</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Carton No</th>
                                        <td class="py-3">{{ $box_info->carton_no ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Barcode</th>
                                        <td class="py-3">
                                            @if($box_info->barcode)
                                                <div class="mb-2" style="max-width: 100%; overflow-x: auto; overflow-y: hidden;">
                                                    {!! Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($box_info->barcode, 'C128', 1.2, 40) !!}
                                                </div>
                                                <code class="text-danger small">{{ $box_info->barcode }}</code>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">QR Code</th>
                                        <td class="py-3">
                                            @if($box_info->qrcode)
                                                <div class="mb-2">
                                                    {!! Milon\Barcode\Facades\DNS2DFacade::getBarcodeHTML($box_info->qrcode, 'QRCODE', 3, 3) !!}
                                                </div>
                                                <code class="text-info small">{{ $box_info->qrcode }}</code>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Total Quantity</th>
                                        <td class="py-3 font-weight-bold text-primary" style="font-size: 1.2rem;">{{ $items->sum('quantity') }} <small>Pcs</small></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: TABLE -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-success py-3">
                                <h3 class="card-title font-weight-bold mb-0">Content Details</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light contrast-text">
                                        <tr>
                                            <th class="pl-4 py-3">Design No</th>
                                            <th class="py-3">Product</th>
                                            <th class="py-3">Color</th>
                                            <th class="py-3">Size Set</th>
                                            <th class="py-3">MRP</th>
                                            <th class="py-3">Selling Price</th>
                                            <th class="text-center py-3">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td class="pl-4 py-3 font-weight-bold">{{ $item->design_number ?? 'N/A' }}</td>
                                                <td class="py-3">{{ $item->product_name ?? 'N/A' }}</td>
                                                <td class="py-3">{{ $item->color_name ?? 'N/A' }}</td>
                                                <td class="py-3"><span class="badge badge-light border">{{ $item->size_set_name ?? 'N/A' }}</span></td>
                                                <td class="py-3 font-weight-bold text-dark">₹{{ number_format($item->mrp, 2) }}</td>
                                                <td class="py-3 font-weight-bold text-dark">₹{{ number_format($item->selling_price, 2) }}</td>
                                                <td class="text-center py-3 font-weight-bold text-success">{{ $item->quantity }}</td>
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

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .card-header {
            border: none;
        }
    </style>
@endsection