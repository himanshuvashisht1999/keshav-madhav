@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">

        <!-- HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold">Dispatch Details</h1>
                        <small class="text-muted">Dispatch #{{ $order_dispatch_data['order_dispatch_no'] }}</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.order-dispatch.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print mr-1"></i> Print Slip
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">

                <!-- INFO CARDS -->
                <div class="row">
                    <!-- ORDER INFO -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-left-primary h-100">
                            <div class="card-body">
                                <h6 class="text-primary font-weight-bold text-uppercase mb-3">Order Information</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Order No:</span>
                                    <span class="font-weight-bold text-dark">{{ $order_dispatch_data['order_no'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Dispatch Date:</span>
                                    <span
                                        class="font-weight-bold text-dark">{{ $order_dispatch_data['dispatch_date'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Dispatch No:</span>
                                    <span
                                        class="font-weight-bold text-dark">{{ $order_dispatch_data['order_dispatch_no'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CUSTOMER INFO -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-left-success h-100">
                            <div class="card-body">
                                <h6 class="text-success font-weight-bold text-uppercase mb-3">Customer Details</h6>
                                <div class="mb-2">
                                    <span class="d-block text-muted text-xs">Customer Name</span>
                                    <span
                                        class="font-weight-bold h5 text-dark">{{ $order_dispatch_data['customer'] }}</span>
                                </div>
                                <div>
                                    <span class="d-block text-muted text-xs">Dispatch Address</span>
                                    <span class="text-dark small">{{ $order_dispatch_data['address'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SUMMARY INFO -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-left-info h-100">
                            <div class="card-body p-3">
                                <h6 class="text-info font-weight-bold text-uppercase mb-3">Financial Summary</h6>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Item Subtotal:</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order_dispatch_data['total_dispatch_amount'], 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-danger">
                                    <span class="text-muted small">Discount ({{ number_format($order_dispatch_data['discount_percentage'], 2) }}%):</span>
                                    @php 
                                        $discount_val = ($order_dispatch_data['total_dispatch_amount'] * $order_dispatch_data['discount_percentage']) / 100;
                                    @endphp
                                    <span class="font-weight-bold">- ₹{{ number_format($discount_val, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-primary">
                                    <span class="text-muted small">GST ({{ number_format($order_dispatch_data['gst_percentage'], 2) }}%):</span>
                                    @php 
                                        $gst_val = (($order_dispatch_data['total_dispatch_amount'] - $discount_val) * $order_dispatch_data['gst_percentage']) / 100;
                                    @endphp
                                    <span class="font-weight-bold">+ ₹{{ number_format($gst_val, 2) }}</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-dark font-weight-bold">Grand Total:</span>
                                    <span class="h4 font-weight-bold text-success mb-0">₹{{ number_format($order_dispatch_data['total_amount'], 2) }}</span>
                                </div>
                                <div class="mt-2 text-center border-top pt-2">
                                    <span class="badge badge-light px-3 py-1">
                                        <i class="fas fa-box mr-1"></i> {{ $order_dispatch_data['total_items_dispatch'] }} Items | {{ $order_dispatch_data['total_cartons'] }} Cartons
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARTON TABLE -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-light border-0">
                        <h3 class="card-title font-weight-bold text-dark mt-1">
                            <i class="fas fa-boxes mr-2"></i> Packed Cartons
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="15%">Carton No</th>
                                        <th width="15%">Contents Summary</th>
                                        <th width="10%" class="text-center">Total Qty</th>
                                        <th width="10%" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cartonsDetails as $index => $carton)
                                        <tr>
                                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">Carton - {{ $carton['carton_no'] }}
                                                </div>
                                                <small class="text-muted">Store Room:
                                                    {{ $carton['storeroom'] ?? '' }}</small><br>
                                                <small class="text-muted">Rack: {{ $carton['rack'] ?? '' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $sets = $carton['sets'] ?? [];
                                                @endphp
                                                @if(count($sets))
                                                    <table class="table table-bordered table-sm mb-0 shadow-sm"
                                                        style="font-size: 13px;">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Design | Color</th>
                                                                <th>Sizes (Qty)</th>
                                                                <th class="text-center" width="50">Total</th>
                                                                <th class="text-right" width="100">Price (₹)</th>
                                                                <th class="text-right" width="110">Value (₹)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($sets as $set)
                                                                <tr>
                                                                    <td>
                                                                        <strong>{{ $set['design'] }}</strong> | {{ $set['color'] }}
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge badge-info">{{ $set['size_set'] }}</span>
                                                                    </td>
                                                                    <td class="text-center font-weight-bold">{{ $set['total_qty'] }}
                                                                    </td>
                                                                    <td class="text-right">₹{{ number_format($set['price'], 2) }}</td>
                                                                    <td class="text-right font-weight-bold text-primary">
                                                                        ₹{{ number_format($set['total_qty'] * $set['price'], 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border text-dark px-3 py-2"
                                                    style="font-size:14px;">
                                                    {{ $carton['total_items'] }} Pcs
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($carton['status'] == 2)
                                                    <span class="badge badge-success px-3 py-1">Dispatched</span>
                                                @else
                                                    <span class="badge badge-warning px-3 py-1">Packed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                                                <p>No cartons found in this dispatch.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <style>
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }

        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }

        .border-left-info {
            border-left: 4px solid #17a2b8 !important;
        }

        .card {
            border: none;
            transition: transform 0.2s;
        }

        /* .card:hover { transform: translateY(-2px); } */

        .table thead th {
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            border-top: none;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
        }

        @media print {

            .btn,
            .main-footer,
            .navbar {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
            }
        }
    </style>
@endsection