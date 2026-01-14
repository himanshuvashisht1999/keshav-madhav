@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold">Order Summary: <span class="text-primary">{{ $order->sku }}</span></h1>
                </div>
                <div class="col-sm-6 text-right">
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
                            <h3 class="card-title font-weight-bold text-dark">Order Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong class="d-block text-muted text-uppercase text-xs">Customer</strong>
                                    <span class="h5 text-dark">{{ $order->customer->name ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong class="d-block text-muted text-uppercase text-xs">Order Date</strong>
                                    <span class="h5 text-dark">{{ date('d M, Y', strtotime($order->created_at)) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong class="d-block text-muted text-uppercase text-xs">Total Quantity</strong>
                                    <span class="h5 text-dark">{{ $order->total_pcs ?? 0 }} Pcs</span>
                                </div>
                                <div class="col-md-3">
                                    <strong class="d-block text-muted text-uppercase text-xs">Status</strong>
                                    <span class="badge badge-success px-3">Active</span>
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
                            <a class="nav-link active" id="production-tab" data-toggle="pill" href="#production" role="tab">
                                <i class="fas fa-industry mr-1"></i> Production & Lots
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
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> Production Lot details will be shown here.
                            </div>
                            <!-- Placeholder for Lot Table -->
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Lot No</th>
                                        <th>Design</th>
                                        <th>Stage</th>
                                        <th class="text-center">Start Date</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No Lot Data Available (Placeholder)</td>
                                    </tr>
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
                                                <!-- Simple logic to show contents, similar to dispatch view -->
                                                @php
                                                    $summary = [];
                                                    foreach($carton->items as $item) {
                                                        $name = $item->detail->size ?? $item->size_id;
                                                        if(!isset($summary[$name])) $summary[$name] = 0;
                                                        $summary[$name] += $item->quantity;
                                                    }
                                                    $text = [];
                                                    foreach($summary as $k => $v) $text[] = "$k ($v)";
                                                @endphp
                                                {{ implode(', ', $text) }}
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
                                        <tr><td colspan="4" class="text-center text-muted">No cartons packed yet.</td></tr>
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
                                            <td><a href="{{ route('admin.order-dispatch.view', ['id' => $dispatch->id]) }}">{{ $dispatch->sku }}</a></td>
                                            <td>{{ date('d M, Y', strtotime($dispatch->dispatch_date)) }}</td>
                                            <td>{{ $dispatch->dispatch_address ?? 'N/A' }}</td>
                                            <td>
                                                @if($dispatch->status == 2) <span class="badge badge-success">Complete</span>
                                                @else <span class="badge badge-primary">Processing</span> @endif
                                            </td>
                                        </tr>
                                        @empty
                                         <tr><td colspan="4" class="text-center text-muted">No dispatches found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                             </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
