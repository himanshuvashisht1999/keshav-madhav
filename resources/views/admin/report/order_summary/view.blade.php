@extends(isset($is_unit) && $is_unit ? 'layouts.unit' : 'admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title d-flex align-items-center flex-wrap gap-2">
            <div>
                <i class="fas fa-chart-line text-warning mr-1"></i> 
                <span class="font-weight-bold" style="color: var(--erp-text-heading);">ORDER SUMMARY:</span> 
                <span class="font-weight-bold ml-1" style="color: var(--erp-green-primary);">{{ $order->sku }}</span>
            </div>
            @if($order->po_number)
                <span class="badge erp-badge-yellow ml-2">PO: {{ $order->po_number }}</span>
            @endif
            <div class="ml-2">
                {!! $status !!}
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.report.order-summary.pdf', $order->id) }}" class="btn-erp btn-erp-primary" title="Download Order Summary PDF">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
            @if(isset($is_unit) && $is_unit)
                <a href="{{ route('unit.assignments') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Assignments
                </a>
            @else
                <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                </a>
            @endif
        </div>
    </div>

    <!-- Order Metadata Card -->
    <div class="erp-card mb-2" style="border-top: 3.5px solid var(--erp-yellow-bright);">
        <div class="erp-card-body p-3">
            <div class="row">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Customer</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-text-heading);">
                        {{ $order->customer->name ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">PO Number</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-text-heading);">
                        {{ $order->po_number ?? '-' }}
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Order Date</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-text-heading);">
                        {{ date('d M, Y', strtotime($order->created_at)) }}
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Exp. Delivery Date</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-text-heading);">
                        {{ date('d M, Y', strtotime($order->expected_delivery_date)) }}
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Order Type</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-text-heading);">
                        {{ ucfirst($order->order_type) ?? '-' }}
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Order File</div>
                    @if($order->corporate_order_file)
                        <button class="btn-erp btn-erp-primary mt-1" data-toggle="modal" data-target="#fileViewModal" data-file="{{ asset('assets/products/' . $order->corporate_order_file) }}" style="font-size: 11px !important; padding: 2px 8px !important;">
                            <i class="fas fa-eye mr-1"></i> View File
                        </button>
                    @else
                        <span class="text-muted font-weight-bold">-</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Products Details Table Card -->
    <div class="erp-card mb-2">
        <div class="erp-card-header py-1">
            <span class="erp-card-title">
                <i class="fas fa-boxes"></i> Product Sets & Design Details
            </span>
        </div>
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Barcode</th>
                        <th style="width: 120px;">Design No.</th>
                        <th>Set Size (Group)</th>
                        <th style="width: 100px;">Lot No's</th>
                        <th style="width: 90px;">Colour</th>
                        <th>Fabric</th>
                        <th style="width: 90px;">Fitting</th>
                        <th style="width: 90px;">Pattern</th>
                        <th style="width: 130px;" class="text-center">Assignment / PO</th>
                        <th style="width: 110px;" class="text-center">Start Date</th>
                        <th style="width: 110px;" class="text-center">Exp. End Date</th>
                        <th style="width: 110px;" class="text-center">Completed Date</th>
                        <th style="width: 80px;" class="text-right">Set Qty</th>
                        <th style="width: 90px;" class="text-right font-weight-bold">Total Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->orderProductSets as $setData)
                        <tr>
                            <td>{{ $setData->bar_code ?? "-" }}</td>
                            <td class="font-weight-bold">{{ $setData->design_number ?? "-" }}</td>
                            <td>{{ $setData->size_measurement->name ?? "-" }} ({{ $setData->size_measurement->size_group ?? "-" }})</td>
                            <td>{{ $setData->lots->pluck('lot_no')->unique()->implode(', ') ?: '-' }}</td>
                            <td>{{ $setData->colors->name ?? "-" }}</td>
                            <td>{{ $setData->fabric->name ?? "-" }}</td>
                            <td>{{ $setData->master_product_fitting->name ?? "-" }}</td>
                            <td>{{ $setData->master_design_pattern->name ?? "-" }}</td>
                            
                            @php 
                                $cuttingStage = $setData->order_cutting_stage; 
                                $poText = '-';
                                if ($setData->remain_total_quantity <= 0) {
                                    if ($cuttingStage && $cuttingStage->is_po) {
                                        $entity = $cuttingStage->vendor_id ? ($cuttingStage->vendor->name ?? 'Vendor') : ($cuttingStage->customer->name ?? 'Customer');
                                        $poText = 'PO: ' . $entity;
                                    } else {
                                        $poText = 'Fully Assigned';
                                    }
                                } elseif ($setData->remain_total_quantity < $setData->total_quantity) {
                                    $poText = 'Partial';
                                } else {
                                    $poText = 'Not Assigned';
                                }
                            @endphp
                            <td class="text-center">
                                @if(str_contains($poText, 'PO:'))
                                    <span class="badge badge-info" style="font-size: 11px; padding: 3px 6px;">{{ $poText }}</span>
                                @elseif($poText == 'Fully Assigned')
                                    <span class="badge badge-success" style="font-size: 11px; padding: 3px 6px;">{{ $poText }}</span>
                                @elseif($poText == 'Partial')
                                    <span class="badge badge-warning" style="font-size: 11px; padding: 3px 6px;">{{ $poText }}</span>
                                @else
                                    <span class="badge badge-primary" style="font-size: 11px; padding: 3px 6px;">{{ $poText }}</span>
                                @endif
                            </td>

                            <td class="text-center">{{ ($cuttingStage && $cuttingStage->start_date) ? date('d M, Y', strtotime($cuttingStage->start_date)) : '-' }}</td>
                            <td class="text-center">{{ ($cuttingStage && $cuttingStage->end_date) ? date('d M, Y', strtotime($cuttingStage->end_date)) : '-' }}</td>
                            <td class="text-center">{{ ($cuttingStage && $cuttingStage->complete_date) ? date('d M, Y', strtotime($cuttingStage->complete_date)) : '-' }}</td>
                            <td class="text-right">{{ $setData->set_quantity ?? 0 }}</td>
                            <td class="text-right font-weight-bold text-primary">{{ $setData->total_quantity ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-3">
                                No Product Data Available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($order->orderProductSets->count())
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="12" class="text-right">Total:</td>
                            <td class="text-right">{{ $order->orderProductSets->sum('set_quantity') }}</td>
                            <td class="text-right text-primary font-weight-bold">{{ $order->orderProductSets->sum('total_quantity') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- TABS CARD -->
    <div class="erp-card mb-2">
        <div class="erp-card-header p-0">
            <ul class="nav erp-tabs" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="production-tab" data-toggle="pill" href="#production" role="tab">
                        <i class="fas fa-industry mr-1"></i> Lots Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="packing-tab" data-toggle="pill" href="#packing" role="tab">
                        <i class="fas fa-box-open mr-1"></i> Packing Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dispatch-tab" data-toggle="pill" href="#dispatch" role="tab">
                        <i class="fas fa-shipping-fast mr-1"></i> Dispatch History
                    </a>
                </li>
            </ul>
        </div>
        <div class="erp-card-body p-2">
            <div class="tab-content" id="reportTabsContent">

                <!-- PRODUCTION TAB -->
                <div class="tab-pane fade show active" id="production" role="tabpanel">
                    <div class="table-responsive">
                        <table class="erp-table table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45px;" class="text-center">#</th>
                                    <th>Lot No</th>
                                    <th>Design No</th>
                                    <th>Current Stage</th>
                                    <th style="width: 120px;" class="text-right">Lot Quantity</th>
                                    <th style="width: 80px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lotsData as $index => $row)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                        <td class="font-weight-bold">{{ $row['lot_no'] }}</td>
                                        <td>{{ $row['design_number'] ?? 'N/A' }}</td>
                                        <td>{{ $row['last_current_stage'] ?? 'N/A' }}</td>
                                        <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">{{ $row['lot_quantity'] ?? '0' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.report.lots.lot-details', ['lot_no' => $row['lot_no']]) }}" class="erp-action-btn erp-btn-view" title="View Lot Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No Lot Data Available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($lotsData) > 0)
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="4" class="text-right">Total:</td>
                                        <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">{{ collect($lotsData)->sum('lot_quantity') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- PACKING TAB -->
                <div class="tab-pane fade" id="packing" role="tabpanel">
                    <div class="table-responsive">
                        <table class="erp-table table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Carton #</th>
                                    <th>Contents Summary</th>
                                    <th style="width: 120px;" class="text-right">Total Items</th>
                                    <th style="width: 110px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cartons as $carton)
                                    <tr>
                                        <td class="font-weight-bold">{{ $carton->carton_no }}</td>
                                        <td>
                                            @php
                                                $summary = [];
                                                foreach ($carton->items as $item) {
                                                    $name = $item->detail->size ?? $item->size_id;
                                                    if (!isset($summary[$name]))
                                                        $summary[$name] = 0;
                                                    $summary[$name] += $item->quantity;
                                                }
                                            @endphp
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($summary as $size => $qty)
                                                    <span class="badge badge-light border mr-1 mb-1 font-weight-normal px-2 py-1" style="font-size: 11.5px;">
                                                        <strong>{{ $size }}:</strong> <span class="font-weight-bold" style="color: var(--erp-green-primary);">{{ $qty }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-right font-weight-bold">{{ $carton->items->sum('quantity') }}</td>
                                        <td class="text-center">
                                            @if($carton->status == 2)
                                                <span class="badge badge-success" style="font-size: 11px; padding: 3px 6px;">Dispatched</span>
                                            @else
                                                <span class="badge badge-warning" style="font-size: 11px; padding: 3px 6px;">Packed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No cartons packed yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DISPATCH TAB -->
                <div class="tab-pane fade" id="dispatch" role="tabpanel">
                    <div class="table-responsive">
                        <table class="erp-table table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Dispatch No</th>
                                    <th style="width: 120px;" class="text-center">Date</th>
                                    <th>Address</th>
                                    <th style="width: 110px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dispatches as $dispatch)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.order-dispatch.view', ['id' => $dispatch->id]) }}" class="font-weight-bold" style="color: var(--erp-green-primary);">
                                                {{ $dispatch->sku }}
                                            </a>
                                        </td>
                                        <td class="text-center">{{ date('d M, Y', strtotime($dispatch->dispatch_date)) }}</td>
                                        <td>{{ $dispatch->orderMain->customer->address ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-success" style="font-size: 11px; padding: 3px 6px;">Complete</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No dispatches found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- FILE VIEW MODAL -->
<div class="modal fade" id="fileViewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden; border: 1px solid var(--erp-border);">
            <div class="modal-header py-2 px-3" style="background: var(--erp-green-primary); color: #fff;">
                <h5 class="modal-title font-weight-bold" style="font-size: var(--erp-font-md);">
                    <i class="fas fa-file mr-1"></i> Order Attachment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.9;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="imagePreview" class="img-fluid d-none" style="max-height: 550px; border-radius: 4px;" />
                <iframe id="pdfPreview" width="100%" height="600px" class="d-none" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#fileViewModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var fileUrl = button.data('file');

            var imagePreview = $('#imagePreview');
            var pdfPreview = $('#pdfPreview');

            imagePreview.addClass('d-none').attr('src', '');
            pdfPreview.addClass('d-none').attr('src', '');

            var extension = fileUrl.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                imagePreview.attr('src', fileUrl).removeClass('d-none');
            } else if (extension === 'pdf') {
                pdfPreview.attr('src', fileUrl).removeClass('d-none');
            }
        });
    });
</script>
@endsection