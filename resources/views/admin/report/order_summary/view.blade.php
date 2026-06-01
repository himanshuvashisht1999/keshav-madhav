@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <h1 class="m-0 font-weight-bold">Order Summary: <span class="text-primary">{{ $order->sku }}</span>
                            @if($order->po_number)
                                <span class="text-muted ml-2">(PO: {{ $order->po_number }})</span>
                            @endif
                            {!! $status !!}</h1>
                    </div>
                    <div class="col-sm-4 text-right">
                        <a href="{{ route('admin.report.order-summary.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Report
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">

                <!-- ORDER INFO CARD -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h3 class="card-title font-weight-bold text-dark">
                                    Order Information
                                </h3>

                                <div class="card-tools">
                                    <a href="{{ route('admin.report.order-summary.pdf', $order->id) }}"
                                        class="btn btn-sm btn-outline-info" title="Download Order Summary PDF">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">Customer</strong>
                                        <span class="h5 text-dark">{{ $order->customer->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">PO Number</strong>
                                        <span class="h5 text-dark">{{ $order->po_number ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong class="d-block text-muted text-uppercase text-xs">Order Date</strong>
                                        <span
                                            class="h5 text-dark">{{ date('d M, Y', strtotime($order->created_at)) }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">Expected Delivery
                                            Date</strong>
                                        <span
                                            class="h5 text-dark">{{ date('d M, Y', strtotime($order->expected_delivery_date)) }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">Order Type</strong>
                                        <span class="h5 text-dark">{{ ucfirst($order->order_type) ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">Order File</strong>
                                        @if($order->corporate_order_file)

                                            <button class="btn btn-sm btn-primary mt-1" data-toggle="modal"
                                                data-target="#fileViewModal"
                                                data-file="{{ asset('assets/products/' . $order->corporate_order_file) }}">
                                                View
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="">
                                        <h5 class="font-weight-bold">Products Details</h5>
                                    </div>
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Barcode</th>
                                                <th>Design No.</th>
                                                <th>Set Size (Set Group)</th>
                                                <th>Lot No's</th>
                                                <th>Colour</th>
                                                <th>Fabric</th>
                                                <th>Fitting</th>
                                                <th>Pattern</th>
                                                <!-- <th>Cutting Master</th> -->
                                                <th>Assignment / PO</th>
                                                <th>Start Date</th>
                                                <th>Expected End Date</th>
                                                <th>Completed Date</th>
                                                <th>Set Quantity</th>
                                                <th>Total Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($order->orderProductSets as $setData)
                                                <tr>
                                                    <td>{{ $setData->bar_code ?? " "  }}</td>
                                                    <td>{{ $setData->design_number ?? " " }}</td>
                                                    <td>{{ $setData->size_measurement->name ?? "- " }}
                                                        ({{ $setData->size_measurement->size_group ?? "-" }})</td>
                                                    <td>{{ $setData->lots->pluck('lot_no')->unique()->implode(', ') ?: '-' }}</td>
                                                    <td>{{ $setData->colors->name ?? " " }}</td>
                                                    <td>{{ $setData->fabric->name ?? " " }}</td>
                                                    <td>{{ $setData->master_product_fitting->name ?? " " }}</td>
                                                    <td>{{ $setData->master_design_pattern->name ?? " " }}</td>
                                                    
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
                                                    <td>
                                                        @if(str_contains($poText, 'PO:'))
                                                            <span class="badge badge-info">{{ $poText }}</span>
                                                        @elseif($poText == 'Fully Assigned')
                                                            <span class="badge badge-success">{{ $poText }}</span>
                                                        @elseif($poText == 'Partial')
                                                            <span class="badge badge-warning">{{ $poText }}</span>
                                                        @else
                                                            <span class="badge badge-primary">{{ $poText }}</span>
                                                        @endif
                                                    </td>

                                                    <td>{{ ($cuttingStage && $cuttingStage->start_date) ? date('d M, Y H:i', strtotime($cuttingStage->start_date)) : '-' }}</td>
                                                    <td>{{ ($cuttingStage && $cuttingStage->end_date) ? date('d M, Y H:i', strtotime($cuttingStage->end_date)) : '-' }}</td>
                                                    <td>{{ ($cuttingStage && $cuttingStage->complete_date) ? date('d M, Y H:i', strtotime($cuttingStage->complete_date)) : '-' }}</td>
                                                    <td class="text-right">{{ $setData->set_quantity ?? 0 }}</td>
                                                    <td class="text-right">{{ $setData->total_quantity ?? 0 }}</td>

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
                                            <tfoot class="bg-light font-weight-bold">
                                                <tr>
                                                    <td colspan="12" class="text-center text-right">Total</td>
                                                    <td class="text-right">
                                                        {{ $order->orderProductSets->sum('set_quantity') }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $order->orderProductSets->sum('total_quantity') }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">Customer</strong>
                                        <span class="h5 text-dark">{{ $order->customer->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong class="d-block text-muted text-uppercase text-xs">PO Number</strong>
                                        <span class="h5 text-dark">{{ $order->po_number ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong class="d-block text-muted text-uppercase text-xs">Order Date</strong>
                                        <span
                                            class="h5 text-dark">{{ date('d M, Y', strtotime($order->created_at)) }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong class="d-block text-muted text-uppercase text-xs">Total Quantity</strong>
                                        <span class="h5 text-dark">{{ getOrderDispatchData($order->id)['total'] ?? 0 }}
                                            Pcs</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong class="d-block text-muted text-uppercase text-xs">Status</strong>
                                        {!! $status !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS -->
                <div class="card card-primary card-outline card-outline-tabs shadow-sm">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="production-tab" data-toggle="pill" href="#production"
                                    role="tab">
                                    <i class="fas fa-industry mr-1"></i>Lots Details
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
                    <div class="card-body">
                        <div class="tab-content" id="reportTabsContent">

                            <!-- PRODUCTION TAB -->
                            <div class="tab-pane fade show active" id="production" role="tabpanel">
                                <!-- <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i> Production Lot details will be shown here.
                                    </div> -->
                                <!-- Placeholder for Lot Table -->
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Lot No</th>
                                            <th>Design No</th>
                                            <th>Lot Quantity</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lotsData as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $row['lot_no'] }}</td>
                                                <td>{{ $row['design_number'] ?? 'N/A' }}</td>
                                                <td class="text-end fw-bold">
                                                    {{ $row['lot_quantity'] ?? '0' }}
                                                </td>
                                                <td class="text-center">

                                                    <a href="{{ route('admin.report.lots.lot-details', ['lot_no' => $row['lot_no']]) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No Lot Data Available</td>
                                            </tr>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>

                            <!-- PACKING TAB -->
                            <div class="tab-pane fade" id="packing" role="tabpanel">
                                <h5 class="mb-3">Packed Cartons</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Carton #</th>
                                                <th>Contents Summary</th>
                                                <th class="text-center">Total Items</th>
                                                <th class="text-center">Status</th>
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
                                                        <div class="d-flex flex-wrap">
                                                            @foreach ($summary as $size => $qty)
                                                                <div class="d-flex align-items-center border rounded mr-2 mb-1 bg-white shadow-xs" style="font-size: 0.85rem; overflow: hidden;">
                                                                    <div class="bg-light px-2 py-1 text-muted border-right small font-weight-bold">SIZE</div>
                                                                    <div class="px-2 py-1 font-weight-bold text-dark">{{ $size }}</div>
                                                                    <div class="bg-light px-2 py-1 text-muted border-left border-right small font-weight-bold">QTY</div>
                                                                    <div class="px-2 py-1 font-weight-bold text-primary">{{ $qty }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="text-center">{{ $carton->items->sum('quantity') }}</td>
                                                    <td class="text-center">
                                                        @if($carton->status == 2)
                                                            <span class="badge badge-success">Dispatched</span>
                                                        @else
                                                            <span class="badge badge-warning">Packed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No cartons packed yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- DISPATCH TAB -->
                            <div class="tab-pane fade" id="dispatch" role="tabpanel">
                                <h5 class="mb-3">Dispatches</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Dispatch No</th>
                                                <th>Date</th>
                                                <th>Address</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($dispatches as $dispatch)
                                                <tr>
                                                    <td><a
                                                            href="{{ route('admin.order-dispatch.view', ['id' => $dispatch->id]) }}">{{ $dispatch->sku }}</a>
                                                    </td>
                                                    <td>{{ date('d M, Y', strtotime($dispatch->dispatch_date)) }}</td>
                                                    <td>{{ $dispatch->orderMain->customer->address ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($dispatch->status == 2) <span
                                                            class="badge badge-success">Complete</span>
                                                        @else <span class="badge badge-primary">Complete</span> @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No dispatches found.</td>
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
            <div class="modal fade" id="fileViewModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Order File</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body text-center">
                            <img id="imagePreview" class="img-fluid d-none" />
                            <iframe id="pdfPreview" width="100%" height="600px" class="d-none"></iframe>
                        </div>

                    </div>
                </div>
            </div>


        </section>
    </div>
    <script>
        $(function () {
            $('#fileViewModal').on('show.bs.modal', function (event) {
                // alert('sdiugvf');
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