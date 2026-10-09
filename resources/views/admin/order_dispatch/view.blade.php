@extends('admin.layouts.app')

@section('content')
    @php
        $taxable = $order_dispatch_data['total_dispatch_amount'] - ($dispatch->discount_amount ?? 0);
        $gstAmt = ($taxable * ($dispatch->gst_percentage ?? 5)) / 100;
        
        $dispatchDateVal = date('Y-m-d\TH:i', strtotime($dispatch->dispatch_date ?? now()));
        $displayDate = date('d M Y h:i A', strtotime($dispatch->dispatch_date ?? now()));
    @endphp

    <div class="content-wrapper erp-page p-2">
        <!-- 1. SLIM ERP HEADER BAR -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title d-flex align-items-center flex-wrap" style="gap: 8px;">
                <i class="fas fa-truck-loading" style="color: var(--erp-green-primary);"></i>
                <span>Dispatch Voucher:</span>
                <span class="erp-badge-yellow px-2 py-0.5 rounded font-weight-bold" style="font-size: 0.95rem;">#{{ $order_dispatch_data['order_dispatch_no'] }}</span>
            </div>
            <div class="erp-header-actions d-flex align-items-center flex-wrap" style="gap: 6px;">
                <a href="{{ route('admin.order-dispatch.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <button type="button" class="btn-erp btn-erp-outline" data-toggle="modal" data-target="#editInvoiceModal">
                    <i class="fas fa-edit mr-1" style="color: #05421c;"></i> Edit Invoice
                </button>
                <a href="{{ route('admin.order-dispatch.download-packing-slip', ['id' => $order_dispatch_data['id']]) }}" id="packingSlipBtn" class="btn-erp btn-erp-outline">
                    <i class="fas fa-boxes mr-1" style="color: #05421c;"></i> Packing Slip
                </a>
                <a href="{{ route('admin.order-dispatch.download-invoice', ['id' => $order_dispatch_data['id']]) }}" id="invoiceBtn" class="btn-erp btn-erp-primary">
                    <i class="fas fa-file-invoice mr-1 text-warning"></i> Download Invoice
                </a>
            </div>
        </div>

        <!-- 2. DISPATCH METADATA STRIP -->
        <div class="erp-card mb-3" style="border-top: 3px solid var(--erp-green-primary); background: #ffffff;">
            <div class="erp-card-body p-2 px-3">
                <div class="row align-items-center">
                    <div class="col-md-4 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-user-tie mr-1" style="color: var(--erp-green-primary);"></i> Customer
                        </div>
                        <div class="font-weight-bold mt-1 text-truncate" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);" title="{{ $order_dispatch_data['customer'] }}">
                            {{ $order_dispatch_data['customer'] }}
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-file-invoice mr-1" style="color: var(--erp-green-primary);"></i> Order Number
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-green-primary);">
                            #{{ $order_dispatch_data['order_no'] }}
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-calendar-alt mr-1" style="color: var(--erp-green-primary);"></i> Dispatch Date
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
                            {{ $displayDate }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. EDIT INVOICE MODAL -->
        <div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header py-3" style="background: #05421c; color: #fff;">
                        <h5 class="modal-title font-weight-bold" id="editInvoiceModalLabel"><i class="fas fa-edit mr-2 text-warning"></i> Update Dispatch Details</h5>
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
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-success"><i class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="datetime-local" class="form-control form-control-sm" id="modal_dispatch_date" name="dispatch_date" value="{{ $dispatchDateVal }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Company</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-success"><i class="fas fa-building"></i></span>
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
                                        <textarea name="remark" id="modal_remark" class="form-control form-control-sm" rows="3" placeholder="Enter remarks..." style="border-radius: 6px;">{{ $dispatch->remark }}</textarea>
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
                                    <div class="p-3 text-center border" style="border-radius: 8px; background: #edf7e4; border-color: #c3e6cb !important;">
                                        <h6 class="text-uppercase mb-1 small font-weight-bold" style="color: #05421c;">Updated Grand Total</h6>
                                        <h3 class="mb-0 font-weight-bold" style="color: #05421c;" id="grand_total_display">₹{{ number_format($dispatch->total_amount, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3">
                            <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-erp btn-erp-primary">UPDATE INVOICE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. KPI METRIC CARDS -->
        <section class="mb-3">
            <div class="row">
                <!-- Dispatch Date -->
                <div class="col-md-3 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 4px solid #05421c; border-radius: 8px;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center" style="background: #05421c; width: 40px; height: 40px;">
                            <i class="fas fa-calendar-alt text-warning"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Dispatch Date</span>
                            <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.88rem;">{{ $displayDate }}</h6>
                        </div>
                    </div>
                </div>
                <!-- Total Units -->
                <div class="col-md-3 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 4px solid #10b981; border-radius: 8px;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center" style="background: #10b981; width: 40px; height: 40px;">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Pieces</span>
                            <h5 class="font-weight-bold mb-0 text-dark">{{ number_format($order_dispatch_data['total_items_dispatch']) }} <span class="text-xs text-muted font-normal">PCs</span></h5>
                        </div>
                    </div>
                </div>
                <!-- Total Cartons -->
                <div class="col-md-3 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 4px solid #d97706; border-radius: 8px;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center" style="background: #d97706; width: 40px; height: 40px;">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Boxes</span>
                            <h5 class="font-weight-bold mb-0 text-dark">{{ number_format($order_dispatch_data['total_cartons']) }} <span class="text-xs text-muted font-normal">Box</span></h5>
                        </div>
                    </div>
                </div>
                <!-- Grand Total -->
                <div class="col-md-3 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-left: 4px solid #05421c; border-radius: 8px;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center" style="background: #edf7e4; color: #05421c; width: 40px; height: 40px;">
                            <i class="fas fa-rupee-sign" style="color: #05421c;"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Grand Total</span>
                            <h5 class="font-weight-bold mb-0" style="color: #05421c;">₹{{ number_format($dispatch->total_amount, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. BILLING DETAILS GRID -->
        <section class="mb-3">
            <div class="row">
                <!-- Dispatch Metadata -->
                <div class="col-lg-6 mb-2">
                    <div class="erp-card h-100 bg-white" style="border-radius: 8px;">
                        <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="font-weight-bold mb-0" style="color: #05421c;"><i class="fas fa-info-circle mr-2 text-warning"></i> Dispatch Details</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table erp-table mb-0 text-sm">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3" style="width: 35%;">Billing Company</td>
                                        <td class="font-weight-bold text-dark py-2 px-3">{{ $dispatch->company->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">Bill Number</td>
                                        <td class="font-weight-bold text-dark py-2 px-3">{{ $order_dispatch_data['bill_number'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">Dispatch Address</td>
                                        <td class="text-dark py-2 px-3">{{ $order_dispatch_data['address'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">Status</td>
                                        <td class="py-2 px-3">
                                            <span class="badge px-2 py-1 font-weight-bold" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; border-radius: 4px;">DISPATCHED</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2 px-3">Remarks</td>
                                        <td class="text-dark py-2 px-3" style="white-space: normal;">{{ $dispatch->remark ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Financial Math Breakdown -->
                <div class="col-lg-6 mb-2">
                    <div class="erp-card h-100 bg-white" style="border-radius: 8px;">
                        <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="font-weight-bold mb-0" style="color: #05421c;"><i class="fas fa-calculator mr-2 text-warning"></i> Financial Summary</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table erp-table mb-0 text-sm">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">Subtotal (Net Value)</td>
                                        <td class="font-weight-bold text-dark text-right py-2 px-3">₹{{ number_format($order_dispatch_data['total_dispatch_amount'], 2) }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">Discount ({{ number_format($dispatch->discount_percentage ?? 0, 2) }}%)</td>
                                        <td class="font-weight-bold text-danger text-right py-2 px-3">- ₹{{ number_format($dispatch->discount_amount ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">Other Charges</td>
                                        <td class="font-weight-bold text-muted text-right py-2 px-3">+ ₹{{ number_format($dispatch->other_charges ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 px-3">GST ({{ number_format($dispatch->gst_percentage ?? 5, 2) }}%)</td>
                                        <td class="font-weight-bold text-dark text-right py-2 px-3">+ ₹{{ number_format($dispatch->gst_amount ?? $gstAmt, 2) }}</td>
                                    </tr>
                                    <tr style="background: #edf7e4;">
                                        <td class="font-weight-bold py-2 px-3" style="font-size: 0.95rem; color: #05421c;">Final Billing Amount</td>
                                        <td class="font-weight-bold text-right py-2 px-3" style="font-size: 1.1rem; color: #05421c;">₹{{ number_format($dispatch->total_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. CONSOLIDATED SHIPPING LIST -->
        <section class="mb-3">
            <div class="erp-card bg-white" style="border-radius: 8px;">
                <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #05421c;"><i class="fas fa-list-alt mr-2 text-warning"></i> Consolidated Shipping Summary</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0 text-center text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left pl-3">Design Number</th>
                                    <th>Size Set</th>
                                    <th>Color</th>
                                    <th>Boxes Count</th>
                                    <th class="text-right">Selling Price</th>
                                    <th>Total Quantity</th>
                                    <th class="text-right pr-3">Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupedItems as $group)
                                    <tr>
                                        <td class="text-left pl-3 font-weight-bold text-dark">{{ $group['product_name'] }}</td>
                                        <td class="font-weight-bold text-muted">{{ $group['size_set_name'] }}</td>
                                        <td><span class="badge badge-light border">{{ $group['color_name'] }}</span></td>
                                        <td class="font-weight-bold" style="color: #05421c;">{{ $group['carton_count'] }} Box</td>
                                        <td class="text-right text-muted">₹{{ number_format($group['selling_price'], 2) }}</td>
                                        <td class="font-weight-bold" style="color: #05421c;">{{ number_format($group['total_qty']) }} pcs</td>
                                        <td class="text-right font-weight-bold pr-3" style="font-size: 0.92rem; color: #05421c;">
                                            ₹{{ number_format($group['total_qty'] * $group['selling_price'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted py-4">No consolidated cargo logs.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. DETAILED PACKAGE CONTENTS -->
        <section class="mb-3">
            <div class="erp-card bg-white" style="border-radius: 8px;">
                <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #05421c;"><i class="fas fa-boxes mr-2 text-warning"></i> Detailed Carton Breakdown</h6>
                </div>
                <div class="card-body p-3" style="background: #f8fafc;">
                    <div class="row">
                        @forelse($cartonsDetails as $carton)
                            <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                                <div class="card border h-100 bg-white carton-card" style="border-radius: 8px;">
                                    <div class="card-header bg-white pt-2 pb-1 border-0 d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge px-2 py-1 font-weight-bold text-white" style="font-size: 0.78rem; border-radius: 4px; background: #05421c;">
                                                Box #{{ $carton['carton_no'] }}
                                            </span>
                                            <div class="text-muted text-xs mt-1 font-weight-normal">
                                                <i class="fas fa-warehouse mr-1 text-muted"></i> {{ $carton['storeroom'] }} / {{ $carton['rack'] }}
                                            </div>
                                        </div>
                                        <span class="badge font-weight-bold px-2 py-1" style="border-radius: 4px; font-size: 0.8rem; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;">
                                            {{ $carton['total_items'] }} Pcs
                                        </span>
                                    </div>
                                    <div class="card-body pt-0 px-2 pb-2 d-flex flex-column justify-content-between">
                                        <div>
                                            <hr class="mt-0 mb-2">
                                            @php
                                                $groupedSets = collect($carton['sets'])->groupBy('size_set')->map(function($items) {
                                                    return $items->sum('total_qty');
                                                });
                                            @endphp
                                            <div class="size-sets-list mb-2">
                                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                                    <tbody>
                                                        @foreach($groupedSets as $sizeSetName => $totalQty)
                                                            <tr>
                                                                <td class="text-muted pl-0 py-0 text-truncate" style="max-width: 130px;" title="{{ $sizeSetName }}">
                                                                    <i class="fas fa-tag mr-1 text-muted" style="font-size: 0.68rem;"></i>{{ $sizeSetName }}
                                                                </td>
                                                                <td class="text-right font-weight-bold text-dark pr-0 py-0">{{ $totalQty }} Pcs</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <button type="button" class="btn-erp btn-erp-outline btn-block mt-2 view-carton-btn text-center"
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
                            <div class="col-12 py-5 text-center text-muted bg-white rounded border">
                                <i class="fas fa-boxes fa-3x mb-3 text-muted"></i>
                                <p class="mb-0 font-weight-semibold">No carton details available for this dispatch.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. CARTON DETAILS MODAL -->
        <div class="modal fade" id="cartonDetailsModal" tabindex="-1" role="dialog" aria-labelledby="cartonDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header py-3" style="background: #05421c; color: #fff;">
                        <h5 class="modal-title font-weight-bold" id="cartonDetailsModalLabel"><i class="fas fa-box-open mr-2 text-warning"></i> Box Details - Box #<span id="modal-carton-no"></span></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <div>
                                <span class="text-muted small d-block">STORAGE LOCATION</span>
                                <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-warehouse mr-1 text-muted"></i> <span id="modal-carton-storage"></span></h6>
                            </div>
                            <div class="text-right">
                                <span class="text-muted small d-block">TOTAL QUANTITY</span>
                                <h5 class="font-weight-bold mb-0" style="color: #05421c;"><span id="modal-carton-pcs"></span> Pcs</h5>
                            </div>
                        </div>
                        <div class="table-responsive rounded border">
                            <table class="table erp-table table-hover mb-0 text-center text-sm">
                                <thead>
                                    <tr>
                                        <th class="py-2 text-left pl-3">Design | Color</th>
                                        <th class="py-2">Size Set</th>
                                        <th class="py-2">Quantity</th>
                                        <th class="py-2 text-right">Price / Piece</th>
                                        <th class="py-2 text-right pr-3">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-carton-items-body">
                                    <!-- Dynamic items loaded by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .carton-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
        .carton-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 66, 28, 0.08) !important;
            border-color: #05421c;
        }
        .stat-card {
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .view-carton-btn {
            font-size: 0.78rem;
            padding: 4px 8px;
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