@extends('owner.layouts.app')

@section('content')
    <style>
        .order-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-header h2 {
            font-weight: 800;
            margin: 0;
            color: #1e3a8a;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            margin: 20px;
            border: none;
        }

        .tab-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            margin: 0 20px 20px;
            border: none;
        }

        .nav-tabs {
            border-bottom: 1px solid #eee;
            padding: 0 20px;
        }

        .nav-link {
            color: #64748b;
            font-weight: 600;
            padding: 15px 20px;
            border: none;
            border-bottom: 3px solid transparent;
        }

        .nav-link.active {
            color: #1e3a8a;
            border-bottom-color: #1e3a8a;
            background: none;
        }

        @media (max-width: 991.98px) {
            .order-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                color: white;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-header h2 {
                color: white;
                font-size: 20px;
            }

            .info-card,
            .tab-card {
                margin: 15px;
            }

            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .nav-link {
                white-space: nowrap;
                padding: 10px 15px;
            }
        }
    </style>

    <div class="order-header">
        <div>
            <h2>Order: {{ $order->sku }}</h2>
            <div class="text-sm opacity-80">{{ $order->customer->name ?? 'N/A' }}</div>
        </div>
        <div class="header-actions">
            <a href="{{ route('owner.order-summary.pdf', ['id' => $order->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="card info-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <label class="text-xs text-muted text-uppercase font-weight-bold d-block">Order Date</label>
                    <span class="font-weight-bold text-dark">{{ date('d M, Y', strtotime($order->created_at)) }}</span>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <label class="text-xs text-muted text-uppercase font-weight-bold d-block">Expected Delivery</label>
                    <span
                        class="font-weight-bold text-dark text-danger">{{ date('d M, Y', strtotime($order->expected_delivery_date)) }}</span>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <label class="text-xs text-muted text-uppercase font-weight-bold d-block">Type</label>
                    <span class="badge bg-primary">{{ ucfirst($order->order_type) }}</span>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <label class="text-xs text-muted text-uppercase font-weight-bold d-block">Total Pcs</label>
                    <span class="font-weight-bold text-dark">{{ $order->orderProductSets->sum('total_quantity') }}</span>
                </div>
            </div>

            <div class="mt-4">
                <h6 class="font-weight-bold border-bottom pb-2 mb-3">Product Manifest</h6>
                <div class="table-responsive">
                    <table class="table table-sm text-sm">
                        <thead>
                            <tr>
                                <th>Design</th>
                                <th>Color/Fabric</th>
                                <th>Size</th>
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderProductSets as $set)
                                <tr>
                                    <td>{{ $set->design_number }}</td>
                                    <td>{{ $set->colors->name ?? '-' }} / {{ $set->fabric->name ?? '-' }}</td>
                                    <td>{{ $set->size_measurement->name ?? '-' }}</td>
                                    <td class="text-right font-weight-bold">{{ $set->total_quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for detailed tracking -->
    <div class="card tab-card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="summaryTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="lots-tab" data-toggle="tab" href="#lots" role="tab">Lots History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="packing-tab" data-toggle="tab" href="#packing" role="tab">Packing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dispatch-tab" data-toggle="tab" href="#dispatch" role="tab">Dispatch</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- LOTS -->
                <div class="tab-pane fade show active" id="lots">
                    @forelse($lotsData as $lot)
                        <div class="d-flex justify-content-between align-items-center p-3 mb-2 border rounded">
                            <div>
                                <div class="font-weight-bold text-primary">Lot #{{ $lot['lot_no'] }}</div>
                                <div class="text-xs text-muted">{{ $lot['customer_name'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-weight-bold">{{ $lot['lot_quantity'] }} Pcs</div>
                                <a href="{{ route('owner.lot-details', ['lot_no' => $lot['lot_no']]) }}"
                                    class="text-xs text-info">View Tracking</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-sm text-center">No lot history found.</p>
                    @endforelse
                </div>

                <!-- PACKING -->
                <div class="tab-pane fade" id="packing">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Carton #</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cartons as $carton)
                                    <tr>
                                        <td class="font-weight-bold">{{ $carton->carton_no }}</td>
                                        <td>{{ $carton->items->sum('quantity') }}</td>
                                        <td><span
                                                class="badge {{ $carton->status == 2 ? 'bg-success' : 'bg-warning' }}">{{ $carton->status == 2 ? 'Dispatched' : 'Packed' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Awaiting packing...</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DISPATCH -->
                <div class="tab-pane fade" id="dispatch">
                    @forelse($dispatches as $dispatch)
                        <div class="p-3 mb-2 border rounded">
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold">{{ $dispatch->sku }}</span>
                                <span class="badge bg-success">Delivered</span>
                            </div>
                            <div class="text-xs text-muted mt-1">Date: {{ date('d M, Y', strtotime($dispatch->dispatch_date)) }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-sm text-center">No dispatch history found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection