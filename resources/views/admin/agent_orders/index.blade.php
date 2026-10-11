@extends('admin.layouts.app')

@section('title', 'Sales Agent Orders')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-user-tag text-primary"></i> Sales Agent Orders
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-truck"></i> View Dispatches
                </a>
                <a href="{{ route('admin.agent-orders.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> Create Sales Order
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <form action="{{ route('admin.agent-orders.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Order ID</label>
                        <input type="text" name="order_id" class="form-control erp-input" placeholder="Search order ID..." value="{{ request('order_id') }}" autocomplete="off">
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Agent</label>
                        <select name="agent_id" id="agent_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL AGENTS --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                            <option value="direct" {{ request('agent_id') == 'direct' ? 'selected' : '' }}>Direct (No Agent)</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-store mr-1"></i> Party</label>
                        <select name="party_id" id="party_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL PARTIES --</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->combined_id }}" {{ request('party_id') == $party->combined_id ? 'selected' : '' }}>
                                    {{ $party->name }} ({{ ucfirst($party->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-tasks mr-1"></i> Status</label>
                        <select name="status" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ANY STATUS --</option>
                            <optgroup label="1. PENDING">
                                <option value="all_pending" {{ request('status') == 'all_pending' ? 'selected' : '' }}>All pending</option>
                                <option value="fully_pending" {{ in_array(request('status'), ['fully_pending', 'pending']) ? 'selected' : '' }}>Fully pending</option>
                                <option value="part_pending" {{ request('status') == 'part_pending' ? 'selected' : '' }}>Part pending</option>
                                <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>Delayed</option>
                            </optgroup>
                            <optgroup label="2. DISPATCH">
                                <option value="all_dispatch" {{ in_array(request('status'), ['all_dispatch', 'dispatch']) ? 'selected' : '' }}>All dispatch</option>
                                <option value="fully_dispatch" {{ in_array(request('status'), ['fully_dispatch', 'dispatched']) ? 'selected' : '' }}>Fully dispatch</option>
                                <option value="part_dispatch" {{ in_array(request('status'), ['part_dispatch', 'partially_dispatched']) ? 'selected' : '' }}>Part dispatch</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-tag mr-1"></i> Type</label>
                        <select name="sale_type" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">ANY</option>
                            <option value="item" {{ request('sale_type') == 'item' ? 'selected' : '' }}>ITEM</option>
                            <option value="fabric" {{ request('sale_type') == 'fabric' ? 'selected' : '' }}>FABRIC</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From</label>
                        <input type="date" name="from_date" class="form-control erp-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To</label>
                        <input type="date" name="to_date" class="form-control erp-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-auto mb-1 ml-auto">
                        <div class="erp-filter-actions">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.agent-orders.index') }}" class="btn-erp btn-erp-outline">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Totals Summary HUD Cards -->
        <div class="row mb-2">
            <div class="col-md-4 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="hud-stat-box mr-3" style="width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #edf5ee; color: #05421c;">
                        <i class="fas fa-list-ol fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase font-weight-bold">Total Orders</div>
                        <div class="h5 mb-0 font-weight-bold" style="color: #05421c;">{{ number_format($totals->total_orders) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="hud-stat-box mr-3" style="width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #ecfdf5; color: #10b981;">
                        <i class="fas fa-box-open fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase font-weight-bold">
                            @if(in_array(request('status'), ['all_dispatch', 'fully_dispatch', 'part_dispatch']))
                                Total Dispatched Pieces
                            @elseif(in_array(request('status'), ['all_pending', 'fully_pending', 'part_pending', 'delayed']))
                                Total Pending Pieces
                            @else
                                Total Pieces
                            @endif
                        </div>
                        <div class="h5 mb-0 font-weight-bold" style="color: #047857;">
                            {{ number_format($totals->total_pieces, request('sale_type') == 'fabric' ? 2 : 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="hud-stat-box mr-3" style="width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #fefce8; color: #b45309;">
                        <i class="fas fa-rupee-sign fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase font-weight-bold">
                            @if(in_array(request('status'), ['all_dispatch', 'fully_dispatch', 'part_dispatch']))
                                Total Dispatched Grand Total
                            @elseif(in_array(request('status'), ['all_pending', 'fully_pending', 'part_pending', 'delayed']))
                                Total Pending Grand Total
                            @else
                                Total Grand Total
                            @endif
                        </div>
                        <div class="h5 mb-0 font-weight-bold" style="color: #b45309;">
                            ₹ {{ number_format($totals->total_grand_total, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table Card -->
        <form id="dispatchForm" action="{{ route('admin.agent-orders.dispatch-selected') }}" method="POST">
            @csrf
            <div class="erp-card p-0">
                <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                        <i class="fas fa-receipt mr-1"></i> Order Records
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        @php $qs = http_build_query(request()->except('page')); @endphp
                        <a href="{{ route('admin.agent-orders.export-pdf') . ($qs ? '?' . $qs : '') }}"
                           class="btn-erp btn-erp-outline"
                           title="Download PDF"
                           target="_blank">
                            <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
                        </a>
                        <a href="{{ route('admin.agent-orders.export-excel') . ($qs ? '?' . $qs : '') }}"
                           class="btn-erp btn-erp-outline"
                           title="Download Excel">
                            <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                        </a>
                        <button type="button" id="dispatchBtn" class="btn-erp btn-erp-primary" style="display: none;" data-toggle="modal" data-target="#dispatchModal">
                            <i class="fas fa-shipping-fast mr-1"></i> Dispatch Selected (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table erp-table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">
                                    <div class="custom-control custom-checkbox ml-1">
                                        <input type="checkbox" class="custom-control-input" id="checkAll">
                                        <label class="custom-control-label" for="checkAll"></label>
                                    </div>
                                </th>
                                <th class="text-center" style="width: 45px;">Sr. no</th>
                                <th>Order id</th>
                                <th>Agent</th>
                                <th>Shop name</th>
                                <th>Type</th>
                                <th>Total pcs</th>
                                <th class="text-center">Scanned</th>
                                <th class="text-right">Grand total</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                                <th>Delivery date</th>
                                <th class="text-right" style="width: 140px;">Actions</th>
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
                                    <td class="text-center text-muted font-weight-bold">
                                        {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="text-nowrap font-weight-bold">
                                        <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="text-dark">
                                            #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-erp-status badge-erp-info font-weight-bold">{{ $order->agent_name }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $order->shop_name }}</strong>
                                    </td>
                                    <td class="text-nowrap">
                                        <div>
                                            <span class="badge-erp-status badge-erp-secondary font-weight-normal">{{ strtoupper($order->order_type ?? 'normal') }}</span>
                                            <a href="{{ route('admin.agent-orders.edit', $order->id) }}" class="text-decoration-none ml-1">
                                                <small class="text-muted font-weight-bold">({{ ucfirst($order->sale_type ?? 'item') }})</small>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        @php
                                            $baseSubtotal = ($order->total_amount > 0) ? $order->total_amount : (($order->dispatched_amount + $order->pending_amount) ?: 1);
                                            $dispGt = ($baseSubtotal > 0) ? round($order->grand_total * ($order->dispatched_amount / $baseSubtotal), 2) : 0;
                                            $pendGt = round($order->grand_total - $dispGt, 2);
                                            $isDispFilter = in_array(request('status'), ['part_dispatch', 'all_dispatch', 'fully_dispatch']);
                                            $isPendFilter = in_array(request('status'), ['part_pending', 'all_pending', 'fully_pending', 'delayed']);
                                        @endphp

                                        @if($order->status == 'partially_dispatched')
                                            @if($isDispFilter)
                                                <span class="font-weight-bold text-success">{{ number_format($order->dispatched_qty, $order->sale_type == 'fabric' ? 2 : 0) }} {{ $order->sale_type == 'fabric' ? 'm' : 'Pcs' }}</span>
                                                <small class="text-muted d-block">of {{ number_format($order->total_qty, $order->sale_type == 'fabric' ? 2 : 0) }} Total</small>
                                            @elseif($isPendFilter)
                                                <span class="font-weight-bold text-warning">{{ number_format($order->pending_qty, $order->sale_type == 'fabric' ? 2 : 0) }} {{ $order->sale_type == 'fabric' ? 'm' : 'Pcs' }}</span>
                                                <small class="text-muted d-block">of {{ number_format($order->total_qty, $order->sale_type == 'fabric' ? 2 : 0) }} Total</small>
                                            @else
                                                <span class="font-weight-bold">{{ number_format($order->total_qty, $order->sale_type == 'fabric' ? 2 : 0) }} {{ $order->sale_type == 'fabric' ? 'm' : 'Pcs' }}</span>
                                                <small class="d-block">
                                                    <span class="text-success font-weight-bold">{{ number_format($order->dispatched_qty, $order->sale_type == 'fabric' ? 2 : 0) }} disp.</span> /
                                                    <span class="text-warning font-weight-bold">{{ number_format($order->pending_qty, $order->sale_type == 'fabric' ? 2 : 0) }} pend.</span>
                                                </small>
                                            @endif
                                        @else
                                            <span class="font-weight-bold">{{ number_format($order->total_qty, $order->sale_type == 'fabric' ? 2 : 0) }} {{ $order->sale_type == 'fabric' ? 'm' : 'Pcs' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($order->sale_type == 'fabric')
                                            <span class="badge-erp-status badge-erp-secondary font-weight-bold">
                                                {{ $order->total_boxes }} Rolls
                                            </span>
                                        @else
                                            <span class="badge-erp-status {{ $order->scanned_count == $order->total_boxes ? 'badge-erp-primary' : 'badge-erp-info' }} font-weight-bold">
                                                {{ $order->scanned_count }} / {{ $order->total_boxes }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        @if($order->status == 'partially_dispatched')
                                            @if($isDispFilter)
                                                <span class="text-success font-weight-bold">₹{{ number_format($dispGt, 2) }}</span>
                                                <small class="text-muted d-block">of ₹{{ number_format($order->grand_total, 2) }}</small>
                                            @elseif($isPendFilter)
                                                <span class="text-warning font-weight-bold">₹{{ number_format($pendGt, 2) }}</span>
                                                <small class="text-muted d-block">of ₹{{ number_format($order->grand_total, 2) }}</small>
                                            @else
                                                <span class="text-dark font-weight-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                                                <small class="d-block text-success">
                                                    ₹{{ number_format($dispGt, 2) }} disp.
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-dark font-weight-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $isDelayed = ($order->status == 'delayed') || ($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'));
                                        @endphp
                                        <span class="badge-erp-status {{ $isDelayed ? 'badge-erp-danger' : ($order->status == 'pending' ? 'badge-erp-warning' : 'badge-erp-primary') }} font-weight-bold">
                                            {{ $isDelayed ? 'DELAYED' : strtoupper(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap font-weight-bold text-dark">
                                        {{ date('d/m/y', strtotime($order->order_date)) }}
                                    </td>
                                    <td class="text-nowrap">
                                        @if($order->expected_dispatch_date)
                                            <div class="text-success font-weight-bold">{{ date('d/m/y', strtotime($order->expected_dispatch_date)) }}</div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <div class="d-flex justify-content-end align-items-center" style="gap: 4px;">
                                            <div class="btn-group">
                                                <button type="button" class="erp-action-btn erp-btn-edit dropdown-toggle"
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
                                                class="erp-action-btn erp-btn-view" title="View Order">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(in_array($order->status, ['pending', 'delayed']) && $order->scanned_count == 0)
                                            <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                                class="erp-action-btn erp-btn-edit" title="Edit Order">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.agent-orders.destroy', $order->id) }}"
                                                class="erp-action-btn erp-btn-delete"
                                                onclick="return confirm('Are you sure you want to delete this order? This will revert any reserved stock.');"
                                                title="Delete Order">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            @elseif(in_array($order->status, ['pending', 'delayed', 'partially_dispatched']))
                                            <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                                class="erp-action-btn erp-btn-edit" title="Edit Order">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="fas fa-shopping-cart fa-2x mb-2 text-secondary"></i>
                                        <div>No agent orders found matching current criteria.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="p-2 border-top bg-white">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </form>

        <!-- Dispatch Configuration Modal -->
        <div class="modal fade" id="dispatchModal" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow border-0" style="border-radius: 8px;">
                    <div class="modal-header py-2" style="background: #05421c; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <h6 class="modal-title font-weight-bold" id="dispatchModalLabel"><i class="fas fa-shipping-fast mr-2"></i> Configure Dispatch</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="form-group mb-2">
                            <label class="erp-label">Dispatch date</label>
                            <input type="datetime-local" class="form-control form-control-sm erp-input" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d\TH:i') }}" required form="dispatchForm">
                        </div>
                        <div class="form-group mb-2">
                            <label class="erp-label">Subtotal amount (Selected orders)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm erp-input" id="modal_total_amount" name="total_amount" readonly form="dispatchForm">
                        </div>
                        <div class="form-group mb-2">
                            <label class="erp-label">Extra discount</label>
                            <input type="number" step="0.01" class="form-control form-control-sm erp-input" id="modal_discount_amount" name="discount_amount" value="0" form="dispatchForm">
                        </div>
                        <div class="form-group mb-2">
                            <label class="erp-label">Other charges</label>
                            <input type="number" step="0.01" class="form-control form-control-sm erp-input" id="modal_other_charges" name="other_charges" value="0" form="dispatchForm">
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <label class="erp-label">GST %</label>
                                <input type="number" step="any" class="form-control form-control-sm erp-input" id="modal_gst_percentage" name="gst_percentage" value="5" form="dispatchForm">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="erp-label">GST amount</label>
                                <input type="number" step="any" class="form-control form-control-sm erp-input" id="modal_gst_amount_input" value="0">
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="erp-label">Remark</label>
                            <textarea name="remark" class="form-control form-control-sm erp-input" rows="2" placeholder="Enter dispatch remark (optional)" form="dispatchForm"></textarea>
                        </div>
                        <div class="p-2 rounded text-center border mt-3" style="background: #edf5ee; border-color: #cce3d0 !important;">
                            <span class="erp-label d-block text-muted mb-0">Final Grand Total</span>
                            <h4 class="mb-0 font-weight-bold" style="color: #05421c;" id="modal_grand_total_display">₹0.00</h4>
                        </div>
                    </div>
                    <div class="modal-footer p-2 bg-light">
                        <button type="button" class="btn-erp btn-erp-secondary mr-2" data-dismiss="modal">Cancel</button>
                        <button type="submit" form="dispatchForm" class="btn-erp btn-erp-primary">
                            <i class="fas fa-check mr-1"></i> Confirm Dispatch
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

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
        
        $('#modal_total_amount').val(total.toFixed(2));
        calculateDispatch('default');
    });

    $('#dispatchForm').on('submit', function(e) {
        if (!confirm('Are you sure you want to dispatch selected orders?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush