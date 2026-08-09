@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark h4"><i class="fas fa-shopping-cart mr-2"></i>Sales Agent Orders</h1>
                        <small class="text-muted">Review and dispatch orders placed by sales agents.</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn btn-sm btn-info shadow-sm px-3 mr-1" style="border-radius: 8px;">
                            <i class="fas fa-truck mr-1"></i> VIEW DISPATCHES
                        </a>
                        <a href="{{ route('admin.agent-orders.create') }}" class="btn btn-sm btn-primary shadow-sm px-3" style="border-radius: 8px;">
                            <i class="fas fa-plus mr-1"></i> CREATE SALES ORDER
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-2">
                        <form action="{{ route('admin.agent-orders.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md mb-2">
                                <label class="small text-muted font-weight-bold mb-1">Filter by Agent</label>
                                <select name="agent_id" id="agent_id" class="form-control select2 form-control-sm">
                                    <option value="">All Agents</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                    <option value="direct" {{ request('agent_id') == 'direct' ? 'selected' : '' }}>Direct (No Agent)</option>
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small text-muted font-weight-bold mb-1">Filter by Party</label>
                                <select name="party_id" id="party_id" class="form-control select2 form-control-sm">
                                    <option value="">All Parties</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->combined_id }}" {{ request('party_id') == $party->combined_id ? 'selected' : '' }}>
                                            {{ $party->name }} ({{ ucfirst($party->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small text-muted font-weight-bold mb-1">Status</label>
                                <select name="status" class="form-control select2 form-control-sm">
                                    <option value="">Any Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>DELAYED</option>
                                    <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>DISPATCHED</option>
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small text-muted font-weight-bold mb-1">Sale Type</label>
                                <select name="sale_type" class="form-control select2 form-control-sm">
                                    <option value="">Any Type</option>
                                    <option value="item" {{ request('sale_type') == 'item' ? 'selected' : '' }}>ITEM (Box)</option>
                                    <option value="fabric" {{ request('sale_type') == 'fabric' ? 'selected' : '' }}>FABRIC (Roll)</option>
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small text-muted font-weight-bold mb-1">From Date</label>
                                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md mb-2">
                                <label class="small text-muted font-weight-bold mb-1">To Date</label>
                                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-auto mb-2 text-right d-flex">
                                <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm mr-1">
                                    <i class="fas fa-filter"></i> APPLY
                                </button>
                                <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-sm btn-outline-secondary px-3 shadow-sm">
                                    <i class="fas fa-undo"></i> RESET
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ══ TOTALS SUMMARY BAR ══ --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="border-radius:10px; border-left: 4px solid #6366f1 !important;">
                            <div class="card-body py-3 px-4 d-flex align-items-center">
                                <div class="mr-3" style="width:42px;height:42px;border-radius:10px;background:#ede9fe;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-list-ol" style="color:#6366f1;font-size:1.1rem;"></i>
                                </div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Total Orders</div>
                                    <div class="font-weight-bold" style="font-size:1.4rem;line-height:1.2;color:#1e1b4b;">{{ number_format($totals->total_orders) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="border-radius:10px; border-left: 4px solid #10b981 !important;">
                            <div class="card-body py-3 px-4 d-flex align-items-center">
                                <div class="mr-3" style="width:42px;height:42px;border-radius:10px;background:#d1fae5;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box-open" style="color:#10b981;font-size:1.1rem;"></i>
                                </div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Total Pieces</div>
                                    <div class="font-weight-bold" style="font-size:1.4rem;line-height:1.2;color:#064e3b;">{{ number_format($totals->total_pieces) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="border-radius:10px; border-left: 4px solid #f59e0b !important;">
                            <div class="card-body py-3 px-4 d-flex align-items-center">
                                <div class="mr-3" style="width:42px;height:42px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-rupee-sign" style="color:#f59e0b;font-size:1.1rem;"></i>
                                </div>
                                <div>
                                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Total Grand Total</div>
                                    <div class="font-weight-bold" style="font-size:1.4rem;line-height:1.2;color:#78350f;">₹ {{ number_format($totals->total_grand_total, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="dispatchForm" action="{{ route('admin.agent-orders.dispatch-selected') }}" method="POST">
                @csrf
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">Order Records</h5>
                        <div class="d-flex align-items-center">
                            {{-- Download buttons — carry all current filters in query string --}}
                            @php $qs = http_build_query(request()->except('page')); @endphp
                            <a href="{{ route('admin.agent-orders.export-pdf') . ($qs ? '?' . $qs : '') }}"
                               class="btn btn-sm btn-outline-danger px-3 shadow-sm mr-2"
                               title="Download PDF"
                               target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <a href="{{ route('admin.agent-orders.export-excel') . ($qs ? '?' . $qs : '') }}"
                               class="btn btn-sm btn-outline-success px-3 shadow-sm mr-2"
                               title="Download Excel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </a>
                            <button type="button" id="dispatchBtn" class="btn btn-success btn-sm px-4 shadow-sm" style="border-radius: 20px; display: none;" data-toggle="modal" data-target="#dispatchModal">
                                <i class="fas fa-shipping-fast mr-1"></i> DISPATCH SELECTED (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <style>
                                .ultra-compact-table th, .ultra-compact-table td {
                                    padding: 0.3rem 0.2rem !important;
                                    font-size: 0.8rem;
                                }
                                .ultra-compact-table .btn-sm {
                                    padding: 0.15rem 0.4rem;
                                    font-size: 0.75rem;
                                }
                            </style>
                            <table class="table table-sm table-hover mb-0 align-middle ultra-compact-table">
                                <thead class="bg-light text-muted">
                                <tr>
                                    <th width="30" class="text-center align-middle">
                                        <div class="custom-control custom-checkbox ml-1">
                                            <input type="checkbox" class="custom-control-input" id="checkAll">
                                            <label class="custom-control-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th class="align-middle">Order ID</th>
                                    <th class="align-middle">Agent</th>
                                    <th class="align-middle">Shop Name</th>
                                    <th class="align-middle">Type</th>
                                    <th class="align-middle">Total Pcs</th>
                                    <th class="text-center align-middle">Scanned</th>
                                    <th class="align-middle">Grand Total</th>
                                    <th class="align-middle">Status</th>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Delivery Date</th>
                                    <th class="text-right align-middle">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr data-shop-id="{{ $order->master_customer_id }}">
                                        <td class="text-center">
                                            @if($order->status != 'dispatched')
                                            <div class="custom-control custom-checkbox ml-1">
                                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" 
                                                    class="custom-control-input order-checkbox" id="check_{{ $order->id }}"
                                                    data-scanned-amount="{{ $order->scanned_amount }}"
                                                    data-scanned-count="{{ $order->scanned_count }}">
                                                <label class="custom-control-label" for="check_{{ $order->id }}"></label>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="font-weight-bold">
                                                #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                            </a>
                                        </td>
                                        <td><span class="badge badge-info">{{ $order->agent_name }}</span></td>
                                        <td><strong style="font-size: 0.8rem; line-height: 1.1;">{{ $order->shop_name }}</strong></td>
                                        <td class="text-nowrap">
                                            <div style="line-height: 1.1;">
                                                <span class="badge badge-outline-secondary" style="font-size:0.65rem;">{{ strtoupper($order->order_type ?? 'normal') }}</span><br>
                                                <a href="{{ route('admin.agent-orders.edit', $order->id) }}" class="text-decoration-none">
                                                    <span class="text-muted font-weight-bold" style="font-size:0.75rem;">{{ ucfirst($order->sale_type ?? 'item') }}</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-nowrap">{{ number_format($order->total_qty, $order->sale_type == 'fabric' ? 2 : 0) }} {{ $order->sale_type == 'fabric' ? 'm' : 'Pcs' }}</td>
                                        <td class="text-center">
                                            @if($order->sale_type == 'fabric')
                                                <span class="badge badge-secondary px-2 py-1">
                                                    {{ $order->total_boxes }} Rolls
                                                </span>
                                            @else
                                                <span class="badge {{ $order->scanned_count == $order->total_boxes ? 'badge-success' : 'badge-info' }} px-2 py-1">
                                                    {{ $order->scanned_count }} / {{ $order->total_boxes }}
                                                </span>
                                            @endif
                                        </td>
                                        <td><span
                                                class="text-primary font-weight-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $isDelayed = ($order->status == 'delayed') || ($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'));
                                            @endphp
                                            <span
                                                class="badge {{ $isDelayed ? 'badge-danger' : ($order->status == 'pending' ? 'badge-warning' : 'badge-success') }}">
                                                {{ $isDelayed ? 'DELAYED' : strtoupper($order->status) }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            @if($order->total_paid >= $order->grand_total && $order->grand_total > 0)
                                                <a href="{{ route('admin.payment.history.index', ['paymentable_type' => 'App\Models\AgentOrder', 'paymentable_id' => $order->id]) }}">
                                                    <span class="badge badge-success">PAID</span>
                                                </a>
                                            @else
                                                <span class="badge badge-danger">UNPAID</span>
                                                <br><small class="text-muted">₹{{ number_format($order->total_paid, 2) }}</small>
                                            @endif
                                        </td> -->
                                        <td class="text-nowrap">
                                            <div class="text-dark font-weight-bold">{{ date('d/m/y', strtotime($order->order_date)) }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            @if($order->expected_dispatch_date)
                                                <div class="text-success font-weight-bold">{{ date('d/m/y', strtotime($order->expected_dispatch_date)) }}</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <div class="d-flex justify-content-end align-items-center" style="gap: 2px;">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill dropdown-toggle" style="padding: 0.15rem 0.35rem;"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Download Order Sheet">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=1">
                                                            <i class="fas fa-tag text-success mr-2"></i> With Price
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=1&with_warehouse=1">
                                                            <i class="fas fa-warehouse text-success mr-2"></i> With Price & Wh. Details
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=0">
                                                            <i class="fas fa-barcode text-muted mr-2"></i> Without Price
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=0&with_warehouse=1">
                                                            <i class="fas fa-warehouse text-muted mr-2"></i> Without Price & Wh. Details
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=2">
                                                            <i class="fas fa-file-invoice text-info mr-2"></i> Unit Price Only
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=2&with_warehouse=1">
                                                            <i class="fas fa-warehouse text-info mr-2"></i> Unit Price & Wh. Details
                                                        </a>
                                                    </div>
                                                </div>
                                                <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                                                    class="btn btn-primary btn-sm shadow-sm rounded-pill" style="padding: 0.15rem 0.35rem;" title="View Order">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(in_array($order->status, ['pending', 'delayed']) && $order->scanned_count == 0)
                                                <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                                    class="btn btn-info btn-sm shadow-sm rounded-pill" style="padding: 0.15rem 0.35rem;" title="Edit Order">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.agent-orders.destroy', $order->id) }}"
                                                    class="btn btn-danger btn-sm shadow-sm rounded-pill" style="padding: 0.15rem 0.35rem;" 
                                                    onclick="return confirm('Are you sure you want to delete this order? This will revert any reserved stock.');"
                                                    title="Delete Order">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                @elseif(in_array($order->status, ['pending', 'delayed']))
                                                <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                                    class="btn btn-info btn-sm shadow-sm rounded-pill" style="padding: 0.15rem 0.35rem;" title="Edit Order">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">No agent orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            </table>
                        </div>
                    </div>
                    @if($orders->hasPages())
                        <div class="card-footer bg-white border-0">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
                </form>
            </div>
        </section>

        <!-- Dispatch Configuration Modal -->
        <div class="modal fade" id="dispatchModal" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                    <div class="modal-header bg-success text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="dispatchModalLabel"><i class="fas fa-shipping-fast mr-2"></i> Configure Dispatch</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Dispatch Date</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt text-success"></i></span>
                                </div>
                                <input type="datetime-local" class="form-control border-left-0" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d\TH:i') }}" required form="dispatchForm">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Subtotal Amount (Selected Orders)</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-primary"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_total_amount" name="total_amount" readonly form="dispatchForm">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Extra Discount</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-minus-circle text-danger"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_discount_amount" name="discount_amount" value="0" form="dispatchForm">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Other Charges</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-plus-circle text-info"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_other_charges" name="other_charges" value="0" form="dispatchForm">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">GST %</label>
                                    <div class="input-group shadow-sm">
                                        <input type="number" step="any" class="form-control" id="modal_gst_percentage" name="gst_percentage" value="5" form="dispatchForm">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"><i class="fas fa-percentage text-secondary"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">GST Amount</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-muted"></i></span>
                                        </div>
                                        <input type="number" step="any" class="form-control border-left-0" id="modal_gst_amount_input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Remark</label>
                            <textarea name="remark" class="form-control" rows="2" placeholder="Enter dispatch remark (optional)" form="dispatchForm"></textarea>
                        </div>
                        <hr class="my-4">
                        <div class="bg-light p-3 rounded-lg text-center shadow-sm border">
                            <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Final Grand Total</h6>
                            <h3 class="mb-0 text-success font-weight-bold" id="modal_grand_total_display">₹0.00</h3>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-outline-secondary px-4 mr-2" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                        <button type="submit" form="dispatchForm" class="btn btn-success px-5 font-weight-bold" style="border-radius: 8px;">CONFIRM DISPATCH</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
<script>
$(document).ready(function() {
    $('#agent_id').on('change', function() {
        var agent_id = $(this).val();
        $.ajax({
            url: "{{ route('admin.agent-orders.get-shops') }}",
            type: "GET",
            data: { agent_id: agent_id },
            success: function(data) {
                var partySelect = $('#party_id');
                partySelect.empty();
                partySelect.append('<option value="">All Parties</option>');
                $.each(data, function(index, party) {
                    var combinedId = party.type + '_' + party.id;
                    var typeLabel = party.type.charAt(0).toUpperCase() + party.type.slice(1);
                    partySelect.append('<option value="' + combinedId + '">' + party.name + ' (' + typeLabel + ')</option>');
                });
                partySelect.trigger('change');
            }
        });
    });

    // Multi-dispatch logic
    const checkAll = $('#checkAll');
    const checkboxes = $('.order-checkbox');
    const dispatchBtn = $('#dispatchBtn');
    const selectedCount = $('#selectedCount');

    function updateBtn() {
        let checked = $('.order-checkbox:checked');
        let count = checked.length;
        selectedCount.text(count);
        
        if (count > 0) {
            dispatchBtn.fadeIn();
        } else {
            dispatchBtn.fadeOut();
        }

        // Shop verification
        let shopId = null;
        let diffShop = false;
        checked.each(function() {
            let sId = $(this).closest('tr').data('shop-id');
            if (shopId === null) {
                shopId = sId;
            } else if (shopId !== sId) {
                diffShop = true;
            }
        });

        if (diffShop) {
            dispatchBtn.attr('disabled', true);
            toastr.warning('Please select orders for the SAME Shop only.');
        } else {
            // Check if at least one selected order has scanned items
            let totalScanned = 0;
            checked.each(function() {
                totalScanned += parseInt($(this).data('scanned-count')) || 0;
            });

            if (totalScanned === 0) {
                dispatchBtn.attr('disabled', true);
                // toastr.info('None of the selected orders have scanned items.');
            } else {
                dispatchBtn.attr('disabled', false);
            }
        }
    }

    checkAll.on('change', function() {
        checkboxes.prop('checked', $(this).prop('checked'));
        updateBtn();
    });

    checkboxes.on('change', function() {
        updateBtn();
    });

    // Modal Calculation Logic
    function calculateDispatch(source) {
        const totalAmount = parseFloat($('#modal_total_amount').val()) || 0;
        const discountAmount = parseFloat($('#modal_discount_amount').val()) || 0;
        const otherCharges = parseFloat($('#modal_other_charges').val()) || 0;
        const taxableAmount = totalAmount - discountAmount;

        let gstPercentage = parseFloat($('#modal_gst_percentage').val()) || 0;
        let gstAmount = parseFloat($('#modal_gst_amount_input').val()) || 0;

        if (source === 'percentage') {
            gstAmount = taxableAmount * (gstPercentage / 100);
            $('#modal_gst_amount_input').val(gstAmount.toFixed(2));
        } else if (source === 'amount') {
            if (taxableAmount > 0) {
                gstPercentage = (gstAmount / taxableAmount) * 100;
                $('#modal_gst_percentage').val(gstPercentage.toFixed(6));
            } else {
                $('#modal_gst_percentage').val(0);
            }
        } else {
            gstAmount = taxableAmount * (gstPercentage / 100);
            $('#modal_gst_amount_input').val(gstAmount.toFixed(2));
        }

        const grandTotal = taxableAmount + gstAmount + otherCharges;
        $('#modal_grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }

    $('#modal_gst_percentage').on('input', function() { calculateDispatch('percentage'); });
    $('#modal_gst_amount_input').on('input', function() { calculateDispatch('amount'); });
    $('#modal_discount_amount, #modal_other_charges').on('input', function() { calculateDispatch('default'); });

    $('#dispatchModal').on('show.bs.modal', function() {
        let total = 0;
        $('.order-checkbox:checked').each(function() {
            total += parseFloat($(this).data('scanned-amount')) || 0;
        });
        
        // Since grand_total includes GST/Discount, we might need a better way to get subtotal.
        // Let's assume the user wants to adjust starting from the current grand total as subtotal 
        // OR we add data-subtotal to the row.
        
        $('#modal_total_amount').val(total.toFixed(2));
        calculateDispatch('default');
    });

    $('#dispatchForm').on('submit', function(e) {
        if (!confirm('Are you sure you want to dispatch selected orders?')) {
            e.preventDefault();
        }
    });

    // Unified Create Modal Logic removed
});
</script>
@endpush