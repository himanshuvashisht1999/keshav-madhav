@extends('admin.layouts.app')

@section('content')
    @php
        $taxable = $order_dispatch_data['total_dispatch_amount'] - ($dispatch->discount_amount ?? 0);
        $gstAmt = ($taxable * ($dispatch->gst_percentage ?? 5)) / 100;
        
        $dispatchDateVal = date('Y-m-d\TH:i', strtotime($dispatch->dispatch_date ?? now()));
        $displayDate = date('d M Y h:i A', strtotime($dispatch->dispatch_date ?? now()));
    @endphp

    <div class="content-wrapper bg-light pb-5">
        <!-- 1. HEADER SECTION -->
        <section class="content-header py-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm overflow-hidden header-card">
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <!-- Left Info -->
                            <div class="col-lg-7 p-4 d-flex align-items-center">
                                <div class="customer-avatar rounded-circle d-flex align-items-center justify-content-center mr-4 shadow-sm">
                                    {{ substr($order_dispatch_data['customer'] ?? 'C', 0, 1) }}
                                </div>
                                <div>
                                    <h1 class="m-0 font-weight-bold h4 text-dark">Dispatch #{{ $order_dispatch_data['order_dispatch_no'] }}</h1>
                                    <p class="text-muted mb-0 small mt-1">
                                        <span class="mr-3"><i class="fas fa-user-tie text-slate mr-1"></i> Customer: <strong class="text-slate-700">{{ $order_dispatch_data['customer'] }}</strong></span>
                                        <span><i class="fas fa-file-invoice text-slate mr-1"></i> Order: <strong class="text-slate-700">#{{ $order_dispatch_data['order_no'] }}</strong></span>
                                    </p>
                                </div>
                            </div>
                            <!-- Right Actions -->
                            <div class="col-lg-5 d-flex align-items-center justify-content-lg-end justify-content-start p-4 bg-white border-left text-nowrap flex-wrap action-gap">
                                <a href="{{ route('admin.order-dispatch.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 py-2 btn-rounded shadow-xs">
                                    <i class="fas fa-arrow-left mr-1"></i> BACK
                                </a>
                                <button type="button" class="btn btn-outline-warning btn-sm font-weight-bold px-3 py-2 btn-rounded shadow-xs" data-toggle="modal" data-target="#editInvoiceModal">
                                    <i class="fas fa-edit mr-1"></i> EDIT INVOICE
                                </button>
                                <a href="{{ route('admin.order-dispatch.download-packing-slip', ['id' => $order_dispatch_data['id']]) }}" id="packingSlipBtn" class="btn btn-outline-info btn-sm font-weight-bold px-3 py-2 btn-rounded shadow-xs">
                                    <i class="fas fa-boxes mr-1"></i> PACKING SLIP
                                </a>
                                <a href="{{ route('admin.order-dispatch.download-invoice', ['id' => $order_dispatch_data['id']]) }}" id="invoiceBtn" class="btn btn-primary btn-sm font-weight-bold px-3 py-2 btn-rounded shadow-sm">
                                    <i class="fas fa-file-invoice mr-1"></i> DOWNLOAD INVOICE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. EDIT INVOICE MODAL (COMPREHENSIVE) -->
        <div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-warning text-white py-3">
                        <h5 class="modal-title font-weight-bold" id="editInvoiceModalLabel"><i class="fas fa-edit mr-2"></i> Update Dispatch Details</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editInvoiceForm">
                        @csrf
                        <input type="hidden" name="dispatch_id" value="{{ $order_dispatch_data['id'] }}">
                        <div class="modal-body p-4">
                            <div class="row">
                                <!-- Left Form Controls -->
                                <div class="col-md-6 border-right">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Dispatch Date</label>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-warning"></i></span>
                                            </div>
                                            <input type="datetime-local" class="form-control form-control-sm" id="modal_dispatch_date" name="dispatch_date" value="{{ $dispatchDateVal }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Company</label>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fas fa-building text-info"></i></span>
                                            </div>
                                            <select class="form-control form-control-sm" id="modal_company_id" name="company_id" required>
                                                <option value="">Select Company</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ ($dispatch->company_id ?? 0) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Remark</label>
                                        <textarea name="remark" id="modal_remark" class="form-control form-control-sm" rows="3" placeholder="Enter remarks..." style="border-radius: 8px;">{{ $dispatch->remark }}</textarea>
                                    </div>
                                </div>

                                <!-- Right Financial Math -->
                                <div class="col-md-6">
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-6 text-right text-muted small">Subtotal Amount (₹)</div>
                                        <div class="col-6">
                                            <input type="number" step="0.01" class="form-control form-control-sm text-right font-weight-bold bg-light" id="subtotal_amount" value="{{ $order_dispatch_data['total_dispatch_amount'] }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-6 text-right text-muted small">Discount Percentage (%)</div>
                                        <div class="col-6">
                                            <input type="number" step="any" class="form-control form-control-sm text-right" id="modal_discount_percentage" name="discount_percentage" value="{{ $dispatch->discount_percentage ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-6 text-right text-muted small">Discount Amount (₹)</div>
                                        <div class="col-6">
                                            <input type="number" step="0.01" class="form-control form-control-sm text-right" id="discount_amount" name="discount_amount" value="{{ $dispatch->discount_amount ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-6 text-right text-muted small">Other Charges (₹)</div>
                                        <div class="col-6">
                                            <input type="number" step="0.01" class="form-control form-control-sm text-right" id="modal_other_charges" name="other_charges" value="{{ $dispatch->other_charges ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-6 text-right text-muted small">GST Percentage (%)</div>
                                        <div class="col-6">
                                            <input type="number" step="0.01" class="form-control form-control-sm text-right" id="gst_percentage" name="gst_percentage" value="{{ $dispatch->gst_percentage ?? 5 }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-6 text-right text-muted small">GST Amount (₹)</div>
                                        <div class="col-6">
                                            <input type="number" step="any" class="form-control form-control-sm text-right" id="modal_gst_amount_input" name="gst_amount" value="{{ $dispatch->gst_amount ?? $gstAmt }}">
                                        </div>
                                    </div>
                                    <hr class="my-3">
                                    <div class="bg-light p-3 rounded-lg text-center border shadow-xs" style="border-radius: 12px;">
                                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Updated Grand Total</h6>
                                        <h3 class="mb-0 text-primary font-weight-bold" id="grand_total_display">₹{{ number_format($dispatch->total_amount, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3">
                            <button type="button" class="btn btn-outline-secondary px-4 mr-2" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-warning px-5 font-weight-bold text-white" style="border-radius: 8px;">UPDATE INVOICE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. KPI METRIC CARDS -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Dispatch Date -->
                    <div class="col-md-3 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 5px solid var(--dark);">
                            <div class="icon-box bg-soft-dark text-dark mr-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fas fa-calendar-alt text-slate"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Dispatch Date</span>
                                <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.9rem;">{{ $displayDate }}</h6>
                            </div>
                        </div>
                    </div>
                    <!-- Total Units -->
                    <div class="col-md-3 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 5px solid var(--success);">
                            <div class="icon-box bg-soft-success text-success mr-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Pieces</span>
                                <h4 class="font-weight-bold mb-0 text-dark">{{ number_format($order_dispatch_data['total_items_dispatch']) }} <span class="text-xs text-muted font-normal">PCs</span></h4>
                            </div>
                        </div>
                    </div>
                    <!-- Total Cartons -->
                    <div class="col-md-3 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 5px solid var(--info);">
                            <div class="icon-box bg-soft-info text-info mr-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fas fa-archive"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Boxes</span>
                                <h4 class="font-weight-bold mb-0 text-dark">{{ number_format($order_dispatch_data['total_cartons']) }} <span class="text-xs text-muted font-normal">Box</span></h4>
                            </div>
                        </div>
                    </div>
                    <!-- Grand Total -->
                    <div class="col-md-3 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 5px solid var(--warning);">
                            <div class="icon-box bg-soft-warning text-warning mr-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Grand Total</span>
                                <h4 class="font-weight-bold mb-0 text-dark">₹{{ number_format($dispatch->total_amount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. BILLING DETAILS GRID -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Dispatch Metadata -->
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                                <span class="indicator bg-primary mr-2"></span>
                                <h6 class="font-weight-bold text-dark mb-0">Dispatch Metadata</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table mb-0 text-sm">
                                    <tbody>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Billing Company</td>
                                            <td class="font-weight-bold text-dark py-3 px-4">{{ $dispatch->company->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Bill Number</td>
                                            <td class="font-weight-bold text-dark py-3 px-4">{{ $order_dispatch_data['bill_number'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Dispatch Address</td>
                                            <td class="text-dark py-3 px-4">{{ $order_dispatch_data['address'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Status</td>
                                            <td class="py-3 px-4">
                                                <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 6px;">DISPATCHED</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-3 px-4">Remarks</td>
                                            <td class="text-dark py-3 px-4" style="white-space: normal;">{{ $dispatch->remark ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Math Breakdown -->
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                                <span class="indicator bg-success mr-2"></span>
                                <h6 class="font-weight-bold text-dark mb-0">Financial Summary</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table mb-0 text-sm">
                                    <tbody>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Subtotal (Net Value)</td>
                                            <td class="font-weight-bold text-dark text-right py-3 px-4">₹{{ number_format($order_dispatch_data['total_dispatch_amount'], 2) }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Discount ({{ number_format($dispatch->discount_percentage ?? 0, 2) }}%)</td>
                                            <td class="font-weight-bold text-danger text-right py-3 px-4">- ₹{{ number_format($dispatch->discount_amount ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Other Charges</td>
                                            <td class="font-weight-bold text-info text-right py-3 px-4">+ ₹{{ number_format($dispatch->other_charges ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">GST ({{ number_format($dispatch->gst_percentage ?? 5, 2) }}%)</td>
                                            <td class="font-weight-bold text-dark text-right py-3 px-4">+ ₹{{ number_format($dispatch->gst_amount ?? $gstAmt, 2) }}</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td class="font-weight-bold text-dark py-3 px-4" style="font-size: 1rem;">Final Billing Amount</td>
                                            <td class="font-weight-bold text-success text-right py-3 px-4" style="font-size: 1.15rem;">₹{{ number_format($dispatch->total_amount, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. CONSOLIDATED SHIPPING LIST -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3 border-0">
                        <div class="d-flex align-items-center">
                            <span class="header-indicator mr-2" style="background: var(--primary);"></span>
                            <h5 class="font-weight-bold text-dark mb-0">Consolidated Shipping Summary</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center text-sm">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="border-0 py-3 text-left pl-4">Design Number</th>
                                        <th class="border-0 py-3">Size Set</th>
                                        <th class="border-0 py-3">Color</th>
                                        <th class="border-0 py-3">Boxes Count</th>
                                        <th class="border-0 py-3 text-right">Selling Price</th>
                                        <th class="border-0 py-3">Total Quantity</th>
                                        <th class="border-0 py-3 text-right pr-4">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupedItems as $group)
                                        <tr class="border-bottom">
                                            <td class="py-3 text-left pl-4 font-weight-bold text-dark">{{ $group['product_name'] }}</td>
                                            <td class="py-3 font-weight-bold text-slate">{{ $group['size_set_name'] }}</td>
                                            <td class="py-3"><span class="badge badge-light border">{{ $group['color_name'] }}</span></td>
                                            <td class="py-3 font-weight-bold text-info">{{ $group['carton_count'] }} Box</td>
                                            <td class="py-3 text-right text-muted">₹{{ number_format($group['selling_price'], 2) }}</td>
                                            <td class="py-3 font-weight-bold text-primary">{{ number_format($group['total_qty']) }} pcs</td>
                                            <td class="py-3 text-right font-weight-bold text-success pr-4" style="font-size: 0.95rem;">
                                                ₹{{ number_format($group['total_qty'] * $group['selling_price'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted py-5">No consolidated cargo logs.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. DETAILED PACKAGE CONTENTS (GRID-BASED CARDS FOR PREMIUM ERP LOOK) -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3 border-0">
                        <div class="d-flex align-items-center">
                            <span class="header-indicator mr-2" style="background: var(--info);"></span>
                            <h5 class="font-weight-bold text-dark mb-0">Detailed Carton Breakdown</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <div class="row">
                            @forelse($cartonsDetails as $carton)
                                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                                    <div class="card border-0 shadow-sm h-100 bg-white carton-card">
                                        <div class="card-header bg-white pt-3 pb-2 border-0 d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="badge badge-dark px-2 py-1 font-weight-bold" style="font-size: 0.78rem; border-radius: 6px; background-color: var(--dark);">
                                                    Box #{{ $carton['carton_no'] }}
                                                </span>
                                                <div class="text-muted text-xs mt-2 font-weight-normal">
                                                    <i class="fas fa-warehouse mr-1 text-slate"></i> {{ $carton['storeroom'] }} / {{ $carton['rack'] }}
                                                </div>
                                            </div>
                                            <span class="badge badge-soft-success font-weight-bold px-3 py-1.5" style="border-radius: 8px; font-size: 0.82rem;">
                                                {{ $carton['total_items'] }} Pcs
                                            </span>
                                        </div>
                                        <div class="card-body pt-0 px-3 pb-3 d-flex flex-column justify-content-between">
                                            <div>
                                                <hr class="mt-0 mb-2" style="border-color: #f1f5f9;">
                                                
                                                @php
                                                    $groupedSets = collect($carton['sets'])->groupBy('size_set')->map(function($items) {
                                                        return $items->sum('total_qty');
                                                    });
                                                @endphp
                                                <div class="size-sets-list mb-3">
                                                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                                                        <tbody>
                                                            @foreach($groupedSets as $sizeSetName => $totalQty)
                                                                <tr>
                                                                    <td class="text-muted pl-0 py-1 text-truncate" style="max-width: 140px;" title="{{ $sizeSetName }}">
                                                                        <i class="fas fa-tag mr-1 text-slate" style="font-size: 0.7rem;"></i>{{ $sizeSetName }}
                                                                    </td>
                                                                    <td class="text-right font-weight-bold text-dark pr-0 py-1">{{ $totalQty }} Pcs</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-block mt-auto btn-rounded view-carton-btn"
                                                    data-carton-id="{{ $carton['id'] }}"
                                                    data-carton-no="{{ $carton['carton_no'] }}"
                                                    data-carton-storage="{{ $carton['storeroom'] }} / {{ $carton['rack'] }}"
                                                    data-carton-pcs="{{ $carton['total_items'] }}"
                                                    data-sets="{{ json_encode($carton['sets']) }}">
                                                <i class="fas fa-eye mr-1"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-5 text-center text-muted bg-white rounded-lg border shadow-xs" style="border-radius: 12px;">
                                    <i class="fas fa-boxes fa-3x mb-3 text-slate"></i>
                                    <p class="mb-0 font-weight-semibold">No carton details available for this dispatch.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. CARTON DETAILS MODAL -->
        <div class="modal fade" id="cartonDetailsModal" tabindex="-1" role="dialog" aria-labelledby="cartonDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-info text-white py-3">
                        <h5 class="modal-title font-weight-bold" id="cartonDetailsModalLabel"><i class="fas fa-box-open mr-2"></i> Box Details - Box #<span id="modal-carton-no"></span></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                            <div>
                                <span class="text-muted small d-block">STORAGE LOCATION</span>
                                <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-warehouse mr-1 text-slate"></i> <span id="modal-carton-storage"></span></h6>
                            </div>
                            <div class="text-right">
                                <span class="text-muted small d-block">TOTAL QUANTITY</span>
                                <h5 class="font-weight-bold text-success mb-0"><span id="modal-carton-pcs"></span> Pcs</h5>
                            </div>
                        </div>
                        <div class="table-responsive rounded border shadow-xs">
                            <table class="table table-striped table-hover mb-0 text-center text-sm">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="py-3 text-left pl-4">Design | Color</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Quantity</th>
                                        <th class="py-3 text-right">Price / Piece</th>
                                        <th class="py-3 text-right pr-4">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-carton-items-body">
                                    <!-- Dynamic items loaded by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal" style="border-radius: 8px;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --info: #0ea5e9;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-500: #64748b;
            --slate-700: #334155;
        }

        .content-wrapper {
            font-family: 'Outfit', sans-serif !important;
        }

        /* Header Card Style */
        .header-card {
            border-radius: 16px;
        }

        .customer-avatar {
            width: 64px;
            height: 64px;
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--dark), #1e293b);
            color: #fff;
        }

        .btn-rounded {
            border-radius: 8px;
        }

        .action-gap {
            gap: 8px;
        }

        /* Indicators */
        .indicator {
            width: 4px;
            height: 18px;
            display: inline-block;
            border-radius: 2px;
        }
        
        .header-indicator {
            width: 4px;
            height: 18px;
            display: inline-block;
            border-radius: 2px;
        }

        /* Stat Card Styles */
        .stat-card {
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease-in-out;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05) !important;
        }

        .icon-box {
            width: 44px;
            height: 44px;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .bg-soft-dark {
            background: rgba(15, 23, 42, 0.05) !important;
        }
        
        .bg-soft-success {
            background: rgba(16, 185, 129, 0.05) !important;
        }
        
        .bg-soft-info {
            background: rgba(14, 165, 233, 0.05) !important;
        }
        
        .bg-soft-warning {
            background: rgba(245, 158, 11, 0.05) !important;
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.08) !important;
            color: var(--success) !important;
        }

        /* Carton card design */
        .carton-card {
            border-radius: 14px;
            transition: all 0.2s ease-in-out;
            border: 1px solid rgba(0, 0, 0, 0.02);
        }
        
        .carton-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06) !important;
        }

        .size-pill {
            background-color: var(--slate-50);
            border-color: var(--slate-200) !important;
            border-radius: 4px;
            font-size: 0.72rem;
            color: var(--slate-700);
            padding: 3px 8px;
        }

        .text-slate {
            color: var(--slate-500) !important;
        }
        
        .text-slate-700 {
            color: var(--slate-700) !important;
        }

        .bg-light {
            background-color: var(--slate-50) !important;
        }
    </style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Carton Details Modal Event
        $(document).on('click', '.view-carton-btn', function() {
            const cartonNo = $(this).data('carton-no');
            const storage = $(this).data('carton-storage');
            const pcs = $(this).data('carton-pcs');
            const sets = $(this).data('sets');

            $('#modal-carton-no').text(cartonNo);
            $('#modal-carton-storage').text(storage);
            $('#modal-carton-pcs').text(pcs);

            let html = '';
            if (Array.isArray(sets) && sets.length > 0) {
                sets.forEach(function(set) {
                    const price = parseFloat(set.price) || 0;
                    const qty = parseInt(set.total_qty) || 0;
                    const totalVal = price * qty;
                    
                    let sizesHtml = '';
                    if (Array.isArray(set.sizes_text) && set.sizes_text.length > 0) {
                        sizesHtml = '<div class="d-flex flex-wrap mt-1 justify-content-start" style="gap: 4px;">';
                        set.sizes_text.forEach(function(sz) {
                            sizesHtml += `<span class="badge text-slate-700 font-weight-normal border px-2 py-0.5" style="background-color: #f8fafc; border-color: #e2e8f0 !important; font-size: 0.72rem; border-radius: 4px;">${sz}</span>`;
                        });
                        sizesHtml += '</div>';
                    }

                    html += `
                        <tr class="border-bottom">
                            <td class="py-3 text-left pl-4">
                                <div class="font-weight-bold text-dark">${set.design}</div>
                                <div class="text-xs text-muted">Color: <span class="text-slate-700 font-weight-bold">${set.color}</span></div>
                            </td>
                            <td class="py-3">
                                <span class="badge badge-light border">${set.size_set}</span>
                            </td>
                            <td class="py-3 font-weight-bold text-primary">
                                ${qty} pcs
                                ${sizesHtml}
                            </td>
                            <td class="py-3 text-right text-muted">₹${price.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="py-3 text-right font-weight-bold text-success pr-4">₹${totalVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="5" class="text-muted py-4">No items inside this carton.</td></tr>';
            }

            $('#modal-carton-items-body').html(html);
            $('#cartonDetailsModal').modal('show');
        });

        // Bi-directional Linkage and Math Calculations in Modal
        function calculateInvoice(changedField = 'default') {
            const subtotal = parseFloat($('#subtotal_amount').val()) || 0;
            let discountP = parseFloat($('#modal_discount_percentage').val()) || 0;
            let discountV = parseFloat($('#discount_amount').val()) || 0;

            if (changedField === 'discount_percentage') {
                discountV = (subtotal * discountP) / 100;
                $('#discount_amount').val(discountV.toFixed(2));
            } else if (changedField === 'discount_amount') {
                if (subtotal > 0) {
                    discountP = (discountV / subtotal) * 100;
                    $('#modal_discount_percentage').val(discountP.toFixed(6));
                } else {
                    discountP = 0;
                    $('#modal_discount_percentage').val(0);
                }
            } else {
                discountV = (subtotal * discountP) / 100;
                $('#discount_amount').val(discountV.toFixed(2));
            }

            const otherCharges = parseFloat($('#modal_other_charges').val()) || 0;
            const baseForGst = (subtotal - discountV) + otherCharges;

            let gstP = parseFloat($('#gst_percentage').val()) || 0;
            let gstV = parseFloat($('#modal_gst_amount_input').val()) || 0;

            if (changedField === 'gst_percentage') {
                gstV = (baseForGst * gstP) / 100;
                $('#modal_gst_amount_input').val(gstV.toFixed(2));
            } else if (changedField === 'gst_amount') {
                if (baseForGst > 0) {
                    gstP = (gstV / baseForGst) * 100;
                    $('#gst_percentage').val(gstP.toFixed(6));
                } else {
                    gstP = 0;
                    $('#gst_percentage').val(0);
                }
            } else {
                gstV = (baseForGst * gstP) / 100;
                $('#modal_gst_amount_input').val(gstV.toFixed(2));
            }

            const grandTotal = baseForGst + gstV;
            $('#grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        // Event triggers
        $('#modal_discount_percentage').on('input', function() { calculateInvoice('discount_percentage'); });
        $('#discount_amount').on('input', function() { calculateInvoice('discount_amount'); });
        $('#gst_percentage').on('input', function() { calculateInvoice('gst_percentage'); });
        $('#modal_gst_amount_input').on('input', function() { calculateInvoice('gst_amount'); });
        $('#modal_other_charges').on('input', function() { calculateInvoice('default'); });

        // Submission
        $('#editInvoiceForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> UPDATING...');

            $.ajax({
                url: "{{ route('admin.order-dispatch.update-invoice') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                        btn.prop('disabled', false).text('UPDATE INVOICE');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong';
                    toastr.error(msg);
                    btn.prop('disabled', false).text('UPDATE INVOICE');
                }
            });
        });
    });
</script>
@endpush