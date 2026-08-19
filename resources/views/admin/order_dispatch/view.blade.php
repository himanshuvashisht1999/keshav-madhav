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
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <!-- Left Info -->
                            <div class="col-lg-7 p-4 d-flex align-items-center">
                                <div class="customer-avatar rounded-circle d-flex align-items-center justify-content-center mr-4 shadow-sm"
                                    style="width: 72px; height: 72px; font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;">
                                    {{ substr($order_dispatch_data['customer'] ?? 'C', 0, 1) }}
                                </div>
                                <div>
                                    <h1 class="m-0 font-weight-bold h4 text-dark">Dispatch #{{ $order_dispatch_data['order_dispatch_no'] }}</h1>
                                    <p class="text-muted mb-0 small">
                                        <span class="mr-3"><i class="fas fa-user-tie text-slate mr-1"></i> Customer: <strong>{{ $order_dispatch_data['customer'] }}</strong></span>
                                        <span><i class="fas fa-file-invoice text-slate mr-1"></i> Order: <strong>#{{ $order_dispatch_data['order_no'] }}</strong></span>
                                    </p>
                                </div>
                            </div>
                            <!-- Right Actions -->
                            <div class="col-lg-5 d-flex align-items-center justify-content-lg-end justify-content-start p-4 bg-white border-left text-nowrap flex-wrap" style="gap: 8px;">
                                <a href="{{ route('admin.order-dispatch.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 py-2 shadow-xs" style="border-radius: 8px;">
                                    <i class="fas fa-arrow-left mr-1"></i> BACK
                                </a>
                                <button type="button" class="btn btn-outline-warning btn-sm font-weight-bold px-3 py-2 shadow-xs"
                                    style="border-radius: 8px;" data-toggle="modal" data-target="#editInvoiceModal">
                                    <i class="fas fa-edit mr-1"></i> EDIT INVOICE
                                </button>
                                <a href="{{ route('admin.order-dispatch.download-packing-slip', ['id' => $order_dispatch_data['id']]) }}"
                                    id="packingSlipBtn" class="btn btn-outline-info btn-sm font-weight-bold px-3 py-2 shadow-xs" style="border-radius: 8px;">
                                    <i class="fas fa-boxes mr-1"></i> PACKING SLIP
                                </a>
                                <a href="{{ route('admin.order-dispatch.download-invoice', ['id' => $order_dispatch_data['id']]) }}" id="invoiceBtn"
                                    class="btn btn-primary btn-sm font-weight-bold px-3 py-2 shadow-sm" style="border-radius: 8px;">
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
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                    <div class="modal-header bg-warning text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
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
                                            <input type="datetime-local" class="form-control" id="modal_dispatch_date" name="dispatch_date" value="{{ $dispatchDateVal }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Company</label>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fas fa-building text-info"></i></span>
                                            </div>
                                            <select class="form-control" id="modal_company_id" name="company_id" required>
                                                <option value="">Select Company</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ ($dispatch->company_id ?? 0) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Remark</label>
                                        <textarea name="remark" id="modal_remark" class="form-control form-control-sm" rows="3" placeholder="Enter remarks...">{{ $dispatch->remark }}</textarea>
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
                                    <div class="bg-light p-3 rounded text-center border shadow-xs">
                                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Updated Grand Total</h6>
                                        <h3 class="mb-0 text-primary font-weight-bold" id="grand_total_display">₹{{ number_format($dispatch->total_amount, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
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
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #0f172a; height: 100%;">
                            <div class="icon-box bg-soft-dark text-dark mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
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
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #10b981; height: 100%;">
                            <div class="icon-box bg-soft-success text-success mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Units</span>
                                <h4 class="font-weight-bold mb-0 text-dark">{{ number_format($order_dispatch_data['total_items_dispatch']) }} <span class="text-xs text-muted font-normal">PCs</span></h4>
                            </div>
                        </div>
                    </div>
                    <!-- Total Cartons -->
                    <div class="col-md-3 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #0ea5e9; height: 100%;">
                            <div class="icon-box bg-soft-info text-info mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <i class="fas fa-archive"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Cartons</span>
                                <h4 class="font-weight-bold mb-0 text-dark">{{ number_format($order_dispatch_data['total_cartons']) }} <span class="text-xs text-muted font-normal">Box</span></h4>
                            </div>
                        </div>
                    </div>
                    <!-- Grand Total -->
                    <div class="col-md-3 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #f59e0b; height: 100%;">
                            <div class="icon-box bg-soft-warning text-warning mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
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
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i> Dispatch Metadata</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table mb-0 text-sm">
                                    <tbody>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Billing Company</td>
                                            <td class="font-weight-bold text-dark py-3 px-4">{{ $dispatch->company->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Dispatch Address</td>
                                            <td class="text-dark py-3 px-4">{{ $order_dispatch_data['address'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-3 px-4">Status</td>
                                            <td class="py-3 px-4">
                                                <span class="badge badge-success px-3 py-1 font-weight-bold">DISPATCHED</span>
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
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i> Financial Summary</h6>
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
                            <span class="header-indicator mr-2" style="width: 4px; height: 18px; background: #6366f1; display: inline-block; border-radius: 2px;"></span>
                            <h5 class="font-weight-bold text-dark mb-0">Consolidated Shipping Summary</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center text-sm">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="border-0 py-3">Design Number</th>
                                        <th class="border-0 py-3">Size Set</th>
                                        <th class="border-0 py-3">Color</th>
                                        <th class="border-0 py-3">Cartons Count</th>
                                        <th class="border-0 py-3">Selling Price</th>
                                        <th class="border-0 py-3">Total Quantity</th>
                                        <th class="border-0 py-3">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupedItems as $group)
                                        <tr class="border-bottom">
                                            <td class="py-3 font-weight-bold text-dark">{{ $group['product_name'] }}</td>
                                            <td class="py-3 font-weight-bold text-slate">{{ $group['size_set_name'] }}</td>
                                            <td class="py-3"><span class="badge badge-light border">{{ $group['color_name'] }}</span></td>
                                            <td class="py-3 font-weight-bold text-info">{{ $group['carton_count'] }} Box</td>
                                            <td class="py-3 text-muted">₹{{ number_format($group['selling_price'], 2) }}</td>
                                            <td class="py-3 font-weight-bold text-primary">{{ number_format($group['total_qty']) }} pcs</td>
                                            <td class="py-3 font-weight-bold text-success" style="font-size: 0.95rem;">
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

        <!-- 6. DETAILED PACKAGE CONTENTS -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3 border-0">
                        <div class="d-flex align-items-center">
                            <span class="header-indicator mr-2" style="width: 4px; height: 18px; background: #0ea5e9; display: inline-block; border-radius: 2px;"></span>
                            <h5 class="font-weight-bold text-dark mb-0">Detailed Carton Breakdown</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @forelse($cartonsDetails as $carton)
                            <div class="p-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 10px;">
                                    <div>
                                        <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 0.95rem;">Carton No: #{{ $carton['carton_no'] }}</h6>
                                        <p class="text-muted mb-0 small"><i class="fas fa-warehouse text-slate mr-1"></i> Storage: <strong>{{ $carton['storeroom'] }} / {{ $carton['rack'] }}</strong></p>
                                    </div>
                                    <span class="badge badge-soft-success font-weight-bold px-3 py-2">{{ $carton['total_items'] }} Pcs Packed</span>
                                </div>
                                <div class="table-responsive rounded border shadow-xs">
                                    <table class="table table-striped table-hover mb-0 text-center text-sm">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="py-3">Design | Color</th>
                                                <th class="py-3">Size Set</th>
                                                <th class="py-3">Quantity</th>
                                                <th class="py-3">Price / Piece</th>
                                                <th class="py-3">Total Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($carton['sets'] as $set)
                                                <tr>
                                                    <td class="py-3 font-weight-bold text-dark">{{ $set['design'] }} <span class="text-muted font-weight-normal">| {{ $set['color'] }}</span></td>
                                                    <td class="py-3"><span class="badge badge-light border">{{ $set['size_set'] }}</span></td>
                                                    <td class="py-3 font-weight-bold text-primary">{{ $set['total_qty'] }} pcs</td>
                                                    <td class="py-3 text-muted">₹{{ number_format($set['price'], 2) }}</td>
                                                    <td class="py-3 font-weight-bold text-success">₹{{ number_format($set['total_qty'] * $set['price'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <i class="fas fa-boxes fa-3x mb-3 text-slate"></i>
                                <p class="mb-0">No cartons pack logs found for this dispatch.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .content-wrapper {
            font-family: 'Outfit', sans-serif !important;
        }

        /* Stat Card Hover Effects */
        .stat-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05) !important;
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }
        .bg-soft-dark {
            background: rgba(30, 41, 59, 0.05) !important;
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
        .text-slate {
            color: #64748b !important;
        }
        .bg-light {
            background-color: #f8fafc !important;
        }
    </style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
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