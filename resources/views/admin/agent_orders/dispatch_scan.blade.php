@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="erp-header-bar">
            <div>
                <div class="erp-header-title">
                    <i class="fas fa-barcode text-primary"></i> Dispatch Scanning
                    @php
                        $totalRequiredBoxes = 0;
                        $totalScannedBoxes = 0;
                        foreach($groupedItems as $group) {
                            $totalRequiredBoxes += $group['required'];
                            $totalScannedBoxes += $group['scanned'];
                        }
                    @endphp
                    <span class="badge badge-primary ml-2" style="font-size: 12px;" id="header_scanned_count">
                        Scanned: <span id="header_scanned_val">{{ $totalScannedBoxes }}</span> / <span id="header_required_val">{{ $totalRequiredBoxes }}</span> Boxes
                    </span>
                </div>
                <div class="erp-page-subtitle">Order #ORD-{{ $order->id }} &nbsp;|&nbsp; {{ $order->shop_name }}</div>
            </div>
            <div class="erp-header-actions">
                <button type="button" id="dispatchBtn" class="btn-erp btn-erp-yellow" data-toggle="modal" data-target="#dispatchModal" {{ $scannedTotal > 0 ? '' : 'disabled' }}>
                    <i class="fas fa-shipping-fast"></i> DISPATCH NOW
                </button>
                <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to Details
                </a>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid p-0">
                <div class="row">
                    <!-- SCANNING AREA -->
                    <div class="col-md-12 mb-2 ds-scan-wrap">
                        <div class="card shadow-sm border-0 ds-scan-card">
                            <div class="card-body p-1">
                                <div class="row align-items-center no-gutters">
                                    <div class="col-md-8 pr-md-2">
                                        <div class="input-group ds-scan-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"
                                                    title="Scan a barcode or QR code. Each scan assigns one available box of that type from inventory. The same barcode/QR applies to all boxes of the same design, color &amp; size.">
                                                    <i class="fas fa-qrcode"></i>
                                                </span>
                                            </div>
                                            <input type="text" id="barcode_input" class="form-control border-left-0"
                                                placeholder="Scan QR / Barcode or enter Box # ..." autofocus autocomplete="off">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary px-4" type="button" id="submit_barcode">
                                                    <i class="fas fa-search-plus mr-1"></i> SCAN
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mt-2 mt-md-0">
                                        <div id="scan_status"
                                            class="alert alert-secondary border-secondary mb-0 d-flex align-items-center justify-content-center ds-status">
                                            <div class="text-center text-white">
                                                <i class="fas fa-qrcode fa-2x mb-2 opacity-50"></i>
                                                <div class="font-weight-bold">Ready to Scan</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS LIST & HISTORY -->
                    <div class="col-lg-9 col-md-8">
                        <div class="card shadow-sm border-0 ds-req-card">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0"><i class="fas fa-boxes mr-2"></i>Order Requirements</h3>
                                <div>
                                    <button type="button" class="btn-erp btn-erp-yellow" id="selectAllBtn">
                                        <i class="fas fa-check-double mr-1"></i> Select All Items
                                    </button>
                                    <button type="button" class="btn-erp btn-erp-outline" style="color:#b91c1c !important; border-color:#fca5a5 !important;" id="unselectAllBtn">
                                        <i class="fas fa-times-circle mr-1"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th width="36" class="text-center align-middle">
                                                    <input type="checkbox" id="selectAllCheckbox" style="cursor: pointer;" title="Select All Items">
                                                </th>
                                                <th>Design</th>
                                                <th>Product</th>
                                                <th>Color</th>
                                                <th>Size</th>
                                                <th>Location</th>
                                                <th>Barcode</th>
                                                <th class="text-center" width="78">Progress</th>
                                                <th class="text-center" width="52">Count</th>
                                                <th class="text-center" width="40">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupedItems as $key => $group)
                                                <tr id="row_{{ $key }}"
                                                    class="variation-row {{ $group['scanned'] == $group['required'] ? 'bg-light-success' : '' }}">
                                                    <td class="text-center vertical-align-middle">
                                                        <input type="checkbox" class="item-checkbox" data-key="{{ $key }}" data-barcode="{{ $group['barcode'] }}" style="cursor: pointer;" {{ $group['scanned'] == $group['required'] ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="ds-c-design"><span class="ds-design">#{{ $group['design_number'] }}</span></td>
                                                    <td class="ds-c-prod" title="{{ $group['product_name'] }}@if(!empty($group['pattern_name'])) | P: {{ $group['pattern_name'] }}@endif @if(!empty($group['fitting_name'])) | F: {{ $group['fitting_name'] }}@endif">{{ $group['product_name'] }}</td>
                                                    <td>{{ $group['color_name'] }}</td>
                                                    <td>{{ $group['size_set_name'] }}</td>
                                                    <td class="ds-c-loc">
                                                        @if($group['warehouse_name'] && $group['warehouse_name'] !== 'N/A')
                                                            <span class="ds-chip ds-chip-wh"><i class="fas fa-warehouse"></i>{{ $group['warehouse_name'] }} / {{ $group['rack_name'] }}@if($group['rack_id']) ({{ $group['rack_id'] }})@endif</span>
                                                        @else
                                                            <span class="ds-chip ds-chip-warn"><i class="fas fa-exclamation-triangle"></i>Advance Sample / Depleted</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="ds-chip ds-chip-code">{{ $group['barcode'] }}</span></td>
                                                    <td class="vertical-align-middle">
                                                        <div class="progress rounded-pill">
                                                            <div id="progress_bar_{{ $key }}"
                                                                class="progress-bar progress-bar-striped progress-bar-animated {{ $group['scanned'] == $group['required'] ? 'bg-success' : 'bg-primary' }}"
                                                                role="progressbar"
                                                                style="width: {{ ($group['scanned'] / $group['required']) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span id="count_{{ $key }}" class="font-weight-bold">{{ $group['scanned'] }}</span>
                                                        <span class="text-muted">/ {{ $group['required'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div id="status_icon_{{ $key }}">
                                                            @if($group['scanned'] == $group['required'])
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            @else
                                                                <i class="fas fa-clock text-warning"></i>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- RECENT SCANS HISTORY -->
                    <div class="col-lg-3 col-md-4">
                        <div class="card shadow-sm border-0 h-100 ds-history-card">
                            <div class="card-header bg-secondary text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Recent Scans</h3>
                            </div>
                            <div class="card-body p-0">
                                <div id="scan_history" class="list-group list-group-flush"
                                    style="max-height: 600px; overflow-y: auto;">
                                    @forelse($scannedBoxes as $box)
                                        @php $boxKey = "{$box->product_id}_{$box->color_id}_{$box->size_set_id}"; @endphp
                                        <div class="list-group-item" id="history_{{ $boxKey }}">
                                            <div class="ds-hist" title="{{ $box->product_name }}">
                                                <div class="ds-hist-main">
                                                    <b>{{ $box->design_number }}</b>
                                                    <span>{{ $box->color_name }}</span>
                                                    <span class="ds-hist-count">{{ $box->scanned_box_qty }}/{{ $box->box_qty }}</span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger undo-btn"
                                                    data-id="{{ $box->box_no }}" data-barcode="{{ $box->barcode }}" title="Undo one scan">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div id="no_scans_msg" class="p-4 text-center text-muted">
                                            <i class="fas fa-barcode fa-3x mb-3 opacity-25"></i>
                                            <p>No boxes scanned yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

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
                <form id="dispatchForm" action="{{ route('admin.agent-orders.dispatch-selected') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Dispatch Date</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt text-success"></i></span>
                                </div>
                                <input type="datetime-local" class="form-control border-left-0" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Company</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-building text-info"></i></span>
                                </div>
                                <select class="form-control border-left-0" id="company_id" name="company_id">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Subtotal Amount</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-primary"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_total_amount" name="total_amount" value="{{ $scannedTotal }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">Extra Discount %</label>
                                    <div class="input-group shadow-sm">
                                        <input type="number" step="any" class="form-control" id="modal_discount_percentage" name="discount_percentage" value="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"><i class="fas fa-percentage text-secondary"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">Extra Discount Amt</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-minus-circle text-danger"></i></span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control border-left-0" id="modal_discount_amount" name="discount_amount" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Other Charges</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-plus-circle text-info"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_other_charges" name="other_charges" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">GST %</label>
                                    <div class="input-group shadow-sm">
                                        <input type="number" step="any" class="form-control" id="modal_gst_percentage" name="gst_percentage" value="{{ $order->gst_percentage ?? 5 }}">
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
                            <textarea name="remark" class="form-control" rows="2" placeholder="Enter dispatch remark (optional)"></textarea>
                        </div>
                        <hr class="my-4">
                        <div class="bg-light p-3 rounded-lg text-center shadow-sm border">
                            <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Final Grand Total</h6>
                            <h3 class="mb-0 text-success font-weight-bold" id="modal_grand_total_display">₹0.00</h3>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn-erp btn-erp-outline mr-2" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-erp btn-erp-primary px-4">CONFIRM DISPATCH</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>

        
        /* ---- Dispatch scan: round 2 (compact, easier to scan with) ---- */
        .erp-page .ds-scan-wrap { position: sticky; top: 0; z-index: 20; padding-bottom: 4px; background: var(--erp-bg-page, #f8fafc); }
        .erp-page .ds-scan-card { margin-bottom: 4px; }
        .erp-page .ds-scan-card .card-body { padding: 10px 14px !important; }
        .erp-page .ds-scan-label { margin-bottom: 4px; font-size: 12px; }
        .erp-page .ds-scan-group .form-control { height: 42px !important; font-size: 17px !important; }
        .erp-page .ds-scan-group .input-group-text, .erp-page .ds-scan-group .btn { height: 42px; }
        .erp-page .ds-scan-card small.text-muted { font-size: 11.5px; margin-top: 4px !important; line-height: 1.35; }
        .erp-page .ds-status { min-height: 0; height: 100%; padding: 6px 12px; }
        .erp-page .ds-status i.fa-2x { font-size: 1.5rem; margin-bottom: 2px !important; }
        .erp-page .ds-status .h5 { font-size: 15px; }

        .erp-page .ds-name { font-size: 14px; font-weight: 800; color: var(--erp-green-primary); line-height: 1.25; }
        .erp-page .ds-design { display: inline-block; margin-left: 6px; padding: 0 7px; border-radius: 4px; background: var(--brand-yellow, #fcee21); color: var(--erp-green-primary); font-size: 11.5px; font-weight: 800; vertical-align: middle; }
        .erp-page .ds-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
        .erp-page .ds-chip { display: inline-flex; align-items: center; gap: 4px; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; line-height: 1.5; background: #fff; border: 1px solid var(--erp-green-border); color: var(--erp-green-primary); }
        .erp-page .ds-chip i { font-size: 10px; opacity: .8; }
        .erp-page .ds-chip-wh { background: var(--erp-green-light); }
        .erp-page .ds-chip-code { background: var(--erp-green-primary); border-color: var(--erp-green-primary); color: #fff; letter-spacing: .3px; }
        .erp-page .ds-chip-warn { background: var(--erp-yellow-bg); border-color: var(--brand-yellow, #fcee21); color: #6b5d00; }

        .erp-page .variation-row td { padding: 7px 10px !important; vertical-align: middle !important; }
        .erp-page .variation-row .h5 { font-size: 18px; }
        .erp-page .variation-row .progress { height: 8px; }
        .erp-page .variation-row .item-checkbox, .erp-page #selectAllCheckbox { accent-color: var(--erp-green-primary); }
        .erp-page .ds-history-card #scan_history .list-group-item { padding: 8px 12px; }
        .erp-page .ds-history-card #scan_history .font-weight-bold { font-size: 13px; color: var(--erp-green-primary); }
        .erp-page .ds-history-card #scan_history small { font-size: 11.5px; }
        .erp-page .ds-history-card #scan_history .btn { padding: 2px 8px; }
        .erp-page .ds-history-card .bg-success.text-white, .erp-page #scan_history .bg-success { background: #36b54a !important; }

        
        /* ---- Dispatch scan: round 3 - slim one-line rows ---- */
        .erp-page .ds-scan-card .card-body { padding: 8px 10px !important; }
        .erp-page .ds-scan-group .form-control { height: 38px !important; font-size: 15px !important; }
        .erp-page .ds-scan-group .input-group-text, .erp-page .ds-scan-group .btn { height: 38px; padding-top: 0; padding-bottom: 0; }
        .erp-page .ds-status { height: 38px; min-height: 0; padding: 0 10px; border-radius: 4px; }
        .erp-page .ds-status > .text-center { display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap; }
        .erp-page .ds-status i.fa-2x, .erp-page .ds-status i { font-size: 1.05rem !important; margin: 0 !important; }
        .erp-page .ds-status .h5, .erp-page .ds-status .font-weight-bold { font-size: 13.5px !important; margin: 0 !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .erp-page .ds-status .spinner-border { margin: 0 !important; }
        .erp-page .ds-hint { margin-top: 4px; font-size: 11px; color: var(--erp-text-subtle); line-height: 1.3; }
        .erp-page .ds-hint i { margin-right: 4px; }

        .erp-page .variation-row td { padding: 3px 5px !important; font-size: 12.5px; white-space: nowrap; height: 32px; }
        .erp-page .variation-row .ds-c-prod { max-width: 130px; overflow: hidden; text-overflow: ellipsis; font-weight: 700; color: var(--erp-green-primary); }
        .erp-page .variation-row .ds-design { margin-left: 0; font-size: 12px; }
        .erp-page .variation-row .ds-chip { font-size: 10.5px; padding: 0 6px; }
        .erp-page .variation-row .ds-chip i { display: none; }
        .erp-page .variation-row .progress { height: 7px; margin: 0; }
        .erp-page .variation-row .item-checkbox, .erp-page #selectAllCheckbox { transform: none; width: 15px; height: 15px; }
        .erp-page .variation-row .fa-check-circle, .erp-page .variation-row .fa-clock { font-size: 15px; }
        .erp-page .ds-req-card table thead th { padding: 5px 5px !important; font-size: 11.5px; white-space: nowrap; }

        .erp-page .ds-history-card #scan_history { max-height: 560px !important; }
        .erp-page .ds-history-card #scan_history .list-group-item { padding: 0 !important; border-left: 0; border-right: 0; }
        .erp-page .ds-hist { display: flex; align-items: center; justify-content: space-between; gap: 6px; padding: 3px 10px; min-height: 30px; }
        .erp-page .ds-hist-main { display: flex; align-items: center; gap: 8px; min-width: 0; font-size: 12.5px; white-space: nowrap; overflow: hidden; }
        .erp-page .ds-hist-main b { color: var(--erp-green-primary); }
        .erp-page .ds-hist-main span { overflow: hidden; text-overflow: ellipsis; color: var(--erp-text-main); }
        .erp-page .ds-hist-count { margin-left: auto; padding: 0 7px; border-radius: 10px; background: var(--erp-green-light); border: 1px solid var(--erp-green-border); color: var(--erp-green-primary) !important; font-weight: 800; font-size: 11.5px; }
        .erp-page .ds-hist .undo-btn { padding: 0 7px; line-height: 22px; font-size: 11px; flex: 0 0 auto; }
        .erp-page .ds-history-card .card-header, .erp-page .ds-req-card .card-header { padding: 6px 12px; }


        /* ---- scan bar: minimal height ---- */
        .erp-page .ds-scan-wrap { padding-bottom: 2px; }
        .erp-page .ds-scan-card { background: #fff; border-top: 2px solid var(--brand-yellow, #fcee21) !important; margin-bottom: 0; }
        .erp-page .ds-scan-card .card-body { padding: 5px 8px !important; }
        .erp-page .ds-scan-wrap .ds-scan-group .form-control { height: 34px !important; font-size: 14px !important; padding-top: 0; padding-bottom: 0; }
        .erp-page .ds-scan-wrap .ds-scan-group .input-group-text, .erp-page .ds-scan-wrap .ds-scan-group .btn { height: 34px !important; font-size: 13px; }
        .erp-page .ds-scan-wrap .ds-status { height: 34px !important; min-height: 0 !important; }
        .erp-page .ds-scan-wrap .ds-status .h5, .erp-page .ds-scan-wrap .ds-status .font-weight-bold { font-size: 13px !important; }

        /* ---- Dispatch scan: brand colours (page + modal) ---- */
        .bg-light-success { background-color: rgba(54, 181, 74, 0.12) !important; }
        .erp-page .ds-scan-card { border-top: 3px solid var(--brand-yellow, #fcee21) !important; background: var(--erp-green-light); }
        .erp-page .ds-scan-label { display: block; font-size: 13px; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; color: var(--erp-green-primary); margin-bottom: 8px; }
        .erp-page .ds-scan-group .form-control { height: 46px !important; font-size: 18px !important; font-weight: 600; }
        .erp-page .ds-scan-group .input-group-text { height: 46px; background: #fff; border-color: #cbd5e1; color: var(--erp-green-primary); }
        .erp-page .ds-scan-group .btn { height: 46px; padding: 0 22px; font-size: 14px; font-weight: 800; letter-spacing: .5px; }

        /* scan status box (script switches alert-secondary / info / success / danger) */
        .erp-page .ds-status { min-height: 74px; border: 0 !important; border-radius: 6px; padding: 10px 14px; }
        .erp-page .ds-status.alert-secondary { background: var(--erp-green-primary) !important; color: #fff !important; }
        .erp-page .ds-status.alert-info { background: #fff !important; color: var(--erp-green-primary) !important; border: 1px dashed var(--erp-green-border) !important; }
        .erp-page .ds-status.alert-success { background: #36b54a !important; color: #fff !important; }
        .erp-page .ds-status.alert-danger { background: #b91c1c !important; color: #fff !important; }
        .erp-page .ds-status .text-white { color: #fff !important; }
        .erp-page .ds-status.alert-info .text-primary { color: var(--erp-green-primary) !important; }

        .erp-page .progress { background: #e6efe0; height: 10px; }
        .erp-page .progress-bar.bg-primary { background-color: var(--erp-green-primary) !important; }
        .erp-page .progress-bar.bg-success { background-color: #36b54a !important; }
        .erp-page .badge-info { background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); }
        .erp-page .badge-light.border { background: #fff; color: var(--erp-green-primary); border-color: var(--erp-green-border) !important; }
        .erp-page .card-header.bg-secondary { background: var(--erp-green-primary) !important; color: #fff !important; border-bottom: 3px solid var(--brand-yellow, #fcee21); }
        .erp-page .card-header.bg-secondary .card-title, .erp-page .card-header.bg-secondary h3 { color: #fff !important; }
        .erp-page .card-header.bg-secondary i { color: var(--brand-yellow, #fcee21); }
        .erp-page .list-group-item { border-color: #e2e8f0; }
        .erp-page #scan_history .list-group-item:hover { background: var(--erp-green-light); }
        .erp-page .undo-btn { color: #b91c1c; border-color: #fca5a5; }
        .erp-page .undo-btn:hover { background: #b91c1c; color: #fff; }
        .erp-page table thead.bg-light th { background: var(--erp-green-primary) !important; color: #fff !important; }
        .erp-page .fa-check-circle.text-success { color: #36b54a !important; }
        .erp-page .fa-clock.text-warning { color: #d4b800 !important; }

        /* dispatch modal (outside .erp-page) */
        #dispatchModal .modal-content { border-radius: 8px !important; overflow: hidden; }
        #dispatchModal .modal-header { background: var(--erp-green-primary) !important; color: #fff; border-bottom: 4px solid var(--brand-yellow, #fcee21); border-radius: 0 !important; }
        #dispatchModal .modal-title { font-size: 15px; }
        #dispatchModal .modal-title i { color: var(--brand-yellow, #fcee21); }
        #dispatchModal label.text-muted { color: var(--erp-green-primary) !important; letter-spacing: .3px; }
        #dispatchModal .input-group-text { background: var(--erp-green-light) !important; border: 1px solid #cbd5e1; }
        #dispatchModal .input-group-text i { color: var(--erp-green-primary) !important; }
        #dispatchModal .form-control { border: 1px solid #cbd5e1; border-radius: 4px; height: 36px; font-size: 13px; }
        #dispatchModal textarea.form-control { height: auto; }
        #dispatchModal .form-control:focus { border-color: var(--erp-green-primary); box-shadow: 0 0 0 2px rgba(5,66,28,.15); }
        #dispatchModal .bg-light.rounded-lg { background: var(--erp-green-light) !important; border: 1px solid var(--erp-green-border) !important; }
        #dispatchModal #modal_grand_total_display { color: var(--erp-green-primary) !important; }
        #dispatchModal .modal-footer { background: #f8fafc !important; border-top: 1px solid #e2e8f0; border-radius: 0 !important; }


        .vertical-align-middle {
            vertical-align: middle !important;
        }

        .pulse {
            animation: pulse-animation 1s infinite;
        }

        @keyframes pulse-animation {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }
    </style>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const barcodeInput = $('#barcode_input');
            const scanStatus = $('#scan_status');
            const orderId = "{{ $order->id }}";

            // Auto-focus on load
            barcodeInput.focus();

            // Aggressive refocus and global input capture
            // If user types/scans anywhere on the page, send it to the input field
            $(document).on('keydown', function (e) {
                console.log('Global KeyDown:', e.key, 'code:', e.which);

                // Ignore if we're already in an input field (could be another field we added later)
                if ($(e.target).is('input, textarea, select')) return;

                // Ignore function keys, modifier keys, etc.
                if (e.ctrlKey || e.altKey || e.metaKey || e.which < 32 || e.which > 126) return;

                console.log('Redirecting focus to barcode input...');
                barcodeInput.focus();
            });

            // Refocus if focus is lost on click
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.input-group, .undo-btn, button, a, textarea, input, select').length) {
                    barcodeInput.focus();
                }
            });

            let scanTimer;
            // barcodeInput.on('input', function () {
            //     const val = $(this).val().trim();
            //     console.log('Input Received:', val);
            //     $('.input-group-text i').addClass('fa-spin text-warning').removeClass('text-primary');
            //     setTimeout(() => {
            //         $('.input-group-text i').removeClass('fa-spin text-warning').addClass('text-primary');
            //     }, 100);

            //     // Auto-submit after 400ms of no typing (useful for scanners that don't send Enter)
            //     clearTimeout(scanTimer);
            //     scanTimer = setTimeout(function () {
            //         if (val.length >= 4) { // Trigger only if length is 4 or more
            //             console.log('Auto-submitting due to inactivity timeout (400ms)...');
            //             triggerScan();
            //         }
            //     }, 400);
            // });

            barcodeInput.on('paste', function (e) {
                setTimeout(() => {
                    triggerScan();
                }, 100); // wait for paste to complete
            });

            barcodeInput.on('keydown', function (e) {
                console.log('Input KeyDown:', e.key, 'code:', e.which);
                // Handle Enter (13), Line Feed (10), or Tab (9)
                if (e.which == 13 || e.which == 10 || e.which == 9) {
                    e.preventDefault();
                    console.log('Manual Submit Key Detected:', e.key);
                    clearTimeout(scanTimer);
                    triggerScan();
                }
            });

            $('#submit_barcode').on('click', function () {
                console.log('Scan Button Clicked');
                clearTimeout(scanTimer);
                triggerScan();
            });

            function triggerScan() {
                const barcode = barcodeInput.val().trim();
                console.log('--- TRIGGERING SCAN ---');
                console.log('Raw Val:', barcode);
                if (barcode) {
                    processScan(barcode);
                } else {
                    console.warn('Scan canceled: Input is empty');
                }
                barcodeInput.val('');
                barcodeInput.focus();
            }

            function processScan(barcode) {
                setLoadingStatus(barcode);
                console.log('AJAX Start: processing barcode', barcode);

                $.ajax({
                    url: "{{ route('admin.agent-orders.process-scan', $order->id) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode
                    },
                    success: function (response) {
                        console.log('AJAX Success:', response);
                        barcodeInput.focus(); // Ensure focus after AJAX
                        if (response.success) {
                            setSuccessStatus(response.message);
                            updateUI(response.variation_key, response);
                            playBeep(true);
                        } else {
                            setErrorStatus(response.message);
                            playBeep(false);
                        }
                    },
                    error: function (xhr) {
                        console.error('AJAX Error:', xhr.status, xhr.responseText);
                        barcodeInput.focus();
                        setErrorStatus('Server error occurred.');
                        playBeep(false);
                    }
                });
            }

            // Remove Scan Logic
            $(document).on('click', '.undo-btn', function () {
                const boxNo = $(this).data('id');
                const btn = $(this);

                if (!confirm('Are you sure you want to remove this scan?')) return;

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.agent-orders.remove-scan', $order->id) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: $(this).data('barcode')
                    },
                    success: function (response) {
                        if (response.success) {
                            setSuccessStatus("Scan removed successfully");
                            revertUI(response.variation_key, boxNo, response);
                            playBeep(true);
                        } else {
                            setErrorStatus(response.message);
                            btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                        }
                    },
                    error: function () {
                        setErrorStatus('Server error occurred.');
                        btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                    }
                });
            });
            function updateHeaderTotalScanned() {
                let totalScanned = 0;
                $('.variation-row').each(function() {
                    const countSpan = $(this).find('td:eq(3) span[id^="count_"]');
                    if (countSpan.length) {
                        totalScanned += parseInt(countSpan.text()) || 0;
                    }
                });
                $('#header_scanned_val').text(totalScanned);
            }

            function checkMasterCheckboxStatus() {
                let allChecked = true;
                let total = $('.item-checkbox').length;
                if (total === 0) {
                    $('#selectAllCheckbox').prop('checked', false);
                    return;
                }
                $('.item-checkbox').each(function() {
                    if (!$(this).is(':checked')) {
                        allChecked = false;
                    }
                });
                $('#selectAllCheckbox').prop('checked', allChecked);
            }

            // Run initial check on page load
            checkMasterCheckboxStatus();

            function updateUI(key, response) {
                const countSpan = $(`#count_${key}`);
                const progressBar = $(`#progress_bar_${key}`);
                const statusIcon = $(`#status_icon_${key}`);
                const row = $(`#row_${key}`);
                const checkbox = row.find('.item-checkbox');

                let current = response.scanned;
                const total = response.required;

                countSpan.text(current);

                const percentage = total > 0 ? (current / total) * 100 : 0;
                progressBar.css('width', percentage + '%');

                if (current >= total && total > 0) {
                    progressBar.removeClass('bg-primary pulse').addClass('bg-success');
                    statusIcon.html('<i class="fas fa-check-circle text-success animate__animated animate__bounceIn"></i>');
                    row.addClass('bg-light-success');
                    checkbox.prop('checked', true);
                } else {
                    progressBar.removeClass('bg-success').addClass('bg-primary ' + (current > 0 ? 'pulse' : ''));
                    statusIcon.html('<i class="fas fa-clock text-warning"></i>');
                    row.removeClass('bg-light-success');
                    checkbox.prop('checked', false);
                }

                // Update OR Append to history
                const boxNo = response.variation_key; // Use key for stable tracking per design
                const productName = response.product_name || '';
                const designNumber = response.design_number || '';
                const color = response.color_name || '';

                $('#no_scans_msg').hide();
                const existingHistory = $(`#history_${boxNo}`);

                const historyHTML = `
                    <div class="ds-hist" title="${productName}">
                        <div class="ds-hist-main">
                            <b>${designNumber}</b>
                            <span>${color}</span>
                            <span class="ds-hist-count">${current}/${total}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger undo-btn"
                            data-id="${boxNo}" data-barcode="${response.barcode}" title="Undo one scan">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                `;

                if (existingHistory.length) {
                    existingHistory.html(historyHTML).addClass('animate__animated animate__pulse');
                    setTimeout(() => existingHistory.removeClass('animate__animated animate__pulse'), 1000);
                } else {
                    const historyItem = `<div class="list-group-item animate__animated animate__fadeInDown" id="history_${boxNo}">${historyHTML}</div>`;
                    $('#scan_history').prepend(historyItem);
                }

                // Update totals and button state
                if (response.scanned_total !== undefined) {
                    $('#modal_total_amount').val(response.scanned_total);
                    $('#dispatchBtn').prop('disabled', response.scanned_total <= 0);
                    if (typeof calculateDispatch === 'function') calculateDispatch('default');
                }

                updateHeaderTotalScanned();
                checkMasterCheckboxStatus();
            }

            function revertUI(key, boxNo, response) {
                const countSpan = $(`#count_${key}`);
                const progressBar = $(`#progress_bar_${key}`);
                const statusIcon = $(`#status_icon_${key}`);
                const row = $(`#row_${key}`);
                const checkbox = row.find('.item-checkbox');

                let current = response.scanned;
                const total = parseInt(countSpan.next().text().replace('/ ', '')) || response.required;

                countSpan.text(current);

                const percentage = total > 0 ? (current / total) * 100 : 0;
                progressBar.css('width', percentage + '%');

                if (current < total) {
                    progressBar.removeClass('bg-success').addClass('bg-primary ' + (current > 0 ? 'pulse' : ''));
                    statusIcon.html('<i class="fas fa-clock text-warning"></i>');
                    row.removeClass('bg-light-success');
                    checkbox.prop('checked', false);
                }

                // If scanned becomes 0, we can remove the history item
                if (current === 0) {
                    $(`#history_${CSS.escape(boxNo)}`).addClass('animate__animated animate__fadeOutRight').fadeOut(function () {
                        $(this).remove();
                        if ($('#scan_history .list-group-item').length === 0) {
                            $('#no_scans_msg').show();
                        }
                    });
                } else {
                    const existingHistory = $(`#history_${boxNo}`);
                    if (existingHistory.length) {
                        existingHistory.find('.text-muted:last').text(`${response.color_name || ''} | Count: ${response.scanned} / ${response.required}`);
                        existingHistory.addClass('animate__animated animate__shakeY');
                        setTimeout(() => existingHistory.removeClass('animate__animated animate__shakeY'), 1000);
                    }
                }

                // Update totals and button state
                if (response.scanned_total !== undefined) {
                    $('#modal_total_amount').val(response.scanned_total);
                    $('#dispatchBtn').prop('disabled', response.scanned_total <= 0);
                    if (typeof calculateDispatch === 'function') calculateDispatch('default');
                }

                updateHeaderTotalScanned();
                checkMasterCheckboxStatus();
            }

            function applyBulkVariations(response) {
                if (response.variations) {
                    $.each(response.variations, function(key, vData) {
                        const countSpan = $(`#count_${key}`);
                        const progressBar = $(`#progress_bar_${key}`);
                        const statusIcon = $(`#status_icon_${key}`);
                        const row = $(`#row_${key}`);
                        const checkbox = row.find('.item-checkbox');

                        let current = vData.scanned;
                        let total = vData.required;

                        countSpan.text(current);
                        const percentage = total > 0 ? (current / total) * 100 : 0;
                        progressBar.css('width', percentage + '%');

                        if (vData.is_complete || (current >= total && total > 0)) {
                            progressBar.removeClass('bg-primary pulse').addClass('bg-success');
                            statusIcon.html('<i class="fas fa-check-circle text-success animate__animated animate__bounceIn"></i>');
                            row.addClass('bg-light-success');
                            checkbox.prop('checked', true);
                        } else {
                            progressBar.removeClass('bg-success').addClass('bg-primary ' + (current > 0 ? 'pulse' : ''));
                            statusIcon.html('<i class="fas fa-clock text-warning"></i>');
                            row.removeClass('bg-light-success');
                            checkbox.prop('checked', false);
                        }

                        // Update Recent Scans history
                        const boxNo = key;
                        if (current > 0) {
                            $('#no_scans_msg').hide();
                            const existingHistory = $(`#history_${boxNo}`);
                            const historyHTML = `
                                <div class="ds-hist" title="${vData.product_name}">
                                    <div class="ds-hist-main">
                                        <b>${vData.design_number}</b>
                                        <span>${vData.color_name}</span>
                                        <span class="ds-hist-count">${current}/${total}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger undo-btn"
                                        data-id="${boxNo}" data-barcode="${vData.barcode}" title="Undo one scan">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            `;

                            if (existingHistory.length) {
                                existingHistory.html(historyHTML);
                            } else {
                                const historyItem = `<div class="list-group-item" id="history_${boxNo}">${historyHTML}</div>`;
                                $('#scan_history').prepend(historyItem);
                            }
                        } else {
                            $(`#history_${CSS.escape(boxNo)}`).remove();
                        }
                    });

                    if ($('#scan_history .list-group-item').length === 0) {
                        $('#no_scans_msg').show();
                    }
                }

                if (response.total_scanned_boxes !== undefined) {
                    $('#header_scanned_val').text(response.total_scanned_boxes);
                }
                if (response.total_required_boxes !== undefined) {
                    $('#header_required_val').text(response.total_required_boxes);
                }
                if (response.scanned_total !== undefined) {
                    $('#modal_total_amount').val(response.scanned_total);
                    $('#dispatchBtn').prop('disabled', response.scanned_total <= 0);
                    if (typeof calculateDispatch === 'function') calculateDispatch('default');
                }

                checkMasterCheckboxStatus();
            }

            // Bulk scan AJAX helper
            function performBulkScan(payload, $triggerElement) {
                setLoadingStatus('Processing bulk selection...');
                if ($triggerElement) {
                    $triggerElement.prop('disabled', true);
                }
                $('#selectAllCheckbox, #selectAllBtn, #unselectAllBtn').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.agent-orders.bulk-scan', $order->id) }}",
                    method: 'POST',
                    data: Object.assign({ _token: "{{ csrf_token() }}" }, payload),
                    success: function (response) {
                        $('#selectAllCheckbox, #selectAllBtn, #unselectAllBtn').prop('disabled', false);
                        if ($triggerElement) {
                            $triggerElement.prop('disabled', false);
                        }
                        if (response.success) {
                            setSuccessStatus(response.message);
                            applyBulkVariations(response);
                            playBeep(true);
                        } else {
                            setErrorStatus(response.message);
                            if ($triggerElement && payload.mode === 'toggle_row') {
                                $triggerElement.prop('checked', !payload.checked);
                            }
                            checkMasterCheckboxStatus();
                            playBeep(false);
                        }
                        barcodeInput.focus();
                    },
                    error: function (xhr) {
                        $('#selectAllCheckbox, #selectAllBtn, #unselectAllBtn').prop('disabled', false);
                        if ($triggerElement) {
                            $triggerElement.prop('disabled', false);
                            if (payload.mode === 'toggle_row') {
                                $triggerElement.prop('checked', !payload.checked);
                            }
                        }
                        setErrorStatus('Server error occurred during bulk operation.');
                        barcodeInput.focus();
                        playBeep(false);
                    }
                });
            }

            // Master checkbox change
            $('#selectAllCheckbox').on('change', function() {
                let checked = $(this).is(':checked');
                let mode = checked ? 'select_all' : 'unselect_all';
                performBulkScan({ mode: mode });
            });

            // Select All Button
            $('#selectAllBtn').on('click', function() {
                performBulkScan({ mode: 'select_all' });
            });

            // Clear All Button
            $('#unselectAllBtn').on('click', function() {
                performBulkScan({ mode: 'unselect_all' });
            });

            // Individual row checkbox change
            $(document).on('change', '.item-checkbox', function() {
                let barcode = $(this).data('barcode');
                let checked = $(this).is(':checked');
                performBulkScan({
                    mode: 'toggle_row',
                    barcode: barcode,
                    checked: checked
                }, $(this));
            });

            function setLoadingStatus(barcode) {
                scanStatus.removeClass('alert-secondary alert-success alert-danger').addClass('alert-info');
                scanStatus.html(`
                                                                    <div class="text-center">
                                                                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                                                        <div>Processing: <strong>${barcode}</strong></div>
                                                                    </div>
                                                                `);
            }

            function setSuccessStatus(message) {
                scanStatus.removeClass('alert-info alert-danger alert-secondary').addClass('alert-success border-success');
                scanStatus.html(`
                                                                    <div class="text-center animate__animated animate__fadeIn">
                                                                        <i class="fas fa-check-circle fa-2x mb-2 text-white"></i>
                                                                        <div class="h5 font-weight-bold text-white mb-0">${message}</div>
                                                                    </div>
                                                                `);

                // Reset status to "Ready" after 3 seconds
                setTimeout(() => {
                    if (scanStatus.hasClass('alert-success')) {
                        resetToReady();
                    }
                }, 3000);
            }

            function setErrorStatus(message) {
                scanStatus.removeClass('alert-info alert-success alert-secondary').addClass('alert-danger border-danger');
                scanStatus.html(`
                                                                    <div class="text-center animate__animated animate__shakeX">
                                                                        <i class="fas fa-exclamation-circle fa-2x mb-2 text-white"></i>
                                                                        <div class="h5 font-weight-bold text-white mb-0">${message}</div>
                                                                    </div>
                                                                `);
            }

            function resetToReady() {
                scanStatus.removeClass('alert-success alert-danger alert-info').addClass('alert-secondary border-secondary');
                scanStatus.html(`
                                                                    <div class="text-center">
                                                                        <i class="fas fa-qrcode fa-2x mb-2 text-white opacity-50"></i>
                                                                        <div class="font-weight-bold text-white">Ready to Scan</div>
                                                                    </div>
                                                                `);
            }

            function playBeep(success) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(success ? 880 : 220, ctx.currentTime);

                    gain.gain.setValueAtTime(0.1, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start();
                    osc.stop(ctx.currentTime + 0.1);
                } catch (e) {
                    console.log('Audio feedback failed', e);
                }
            }

            // Modal Calculation Logic
            function calculateDispatch(source) {
                const totalAmount = parseFloat($('#modal_total_amount').val()) || 0;
                let discountAmount = parseFloat($('#modal_discount_amount').val()) || 0;
                let discountPercentage = parseFloat($('#modal_discount_percentage').val()) || 0;

                if (source === 'discount_percentage') {
                    discountAmount = totalAmount * (discountPercentage / 100);
                    $('#modal_discount_amount').val(discountAmount.toFixed(2));
                } else if (source === 'discount_amount' || source === 'total_amount' || source === 'default') {
                    if (totalAmount > 0) {
                        discountPercentage = (discountAmount / totalAmount) * 100;
                        $('#modal_discount_percentage').val(discountPercentage.toFixed(6));
                    } else {
                        $('#modal_discount_percentage').val(0);
                    }
                }
                
                // Refresh discountAmount in case it was modified
                discountAmount = parseFloat($('#modal_discount_amount').val()) || 0;

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

                const grandTotal = Math.ceil(taxableAmount + gstAmount + otherCharges);
                $('#modal_grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('#modal_discount_percentage').on('input', function() { calculateDispatch('discount_percentage'); });
            $('#modal_discount_amount').on('input', function() { calculateDispatch('discount_amount'); });
            $('#modal_gst_percentage').on('input', function() { calculateDispatch('percentage'); });
            $('#modal_gst_amount_input').on('input', function() { calculateDispatch('amount'); });
            $('#modal_total_amount, #modal_other_charges').on('input', function() { calculateDispatch('default'); });

            $('#dispatchModal').on('show.bs.modal', function() {
                calculateDispatch('default');
            });
        });
    </script>
@endpush