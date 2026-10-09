@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="erp-page p-2">

        <form method="POST" id="timeAllocationForm" action="{{ route('admin.time_allocation.update', $allocation->id) }}">
            @csrf

            <!-- HEADER BAR -->
            <div class="erp-header-bar mb-2">
                <div class="erp-header-title d-flex align-items-center">
                    <i class="fas fa-business-time mr-2 text-success"></i>
                    <span>Stage Wise Time Allocation</span>
                    <span class="badge ml-2 px-2 py-1" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb; font-size:13px; font-weight:700;">
                        Lot #{{ $allocation->lot_no }}
                    </span>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.time_allocation.index') }}" class="btn-erp btn-erp-outline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                    <button type="submit" id="submit" class="btn-erp btn-erp-primary ml-2">
                        <i class="fas fa-save mr-1"></i> Save Allocation
                    </button>
                </div>
            </div>

            <div class="row">

                <!-- LEFT PANEL: DETAILS (4 Columns) -->
                <div class="col-lg-4 col-md-5 mb-3">
                    
                    <!-- BASIC INFO CARD -->
                    <div class="erp-card bg-white p-3 mb-3">
                        <div class="d-flex align-items-center pb-2 mb-3 border-bottom">
                            <i class="fas fa-info-circle mr-2" style="color: #05421c;"></i>
                            <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Basic Information</h6>
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Production Start Date</label>
                            @php
                                $startDate = ($allocation->orderLot && $allocation->orderLot->production_datetime) 
                                    ? $allocation->orderLot->production_datetime 
                                    : $allocation->start_date_time;
                            @endphp
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="far fa-calendar-alt text-muted"></i></span>
                                </div>
                                <input type="text"
                                       class="form-control erp-input bg-light border-left-0"
                                       value="{{ date('d M Y', strtotime($startDate)) }} ({{ date('Y-m-d', strtotime($startDate)) }})"
                                       readonly>
                            </div>
                        </div>

                        <!-- LOT NO -->
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Lot Number</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-layer-group text-muted"></i></span>
                                </div>
                                <input type="text" class="form-control erp-input bg-light border-left-0 font-weight-bold text-dark" id="lot_no" value="{{ $allocation->lot_no }}" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- LOT DETAILS CONTAINER (Populated via AJAX) -->
                    <div id="lotDetailsCard" class="erp-card bg-white p-3 d-none">
                        <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-boxes mr-2" style="color: #05421c;"></i>
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Selected Lot Details</h6>
                            </div>
                            <span class="badge badge-success px-2 py-1 text-xs" style="background:#05421c;">Verified</span>
                        </div>

                        <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="row text-xs">
                                <div class="col-6 mb-2">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Fabric</span>
                                    <strong id="dtl_fabric" class="text-dark">-</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Color</span>
                                    <strong id="dtl_color" class="text-dark">-</strong>
                                </div>
                                <div class="col-12 mb-2">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Orders</span>
                                    <strong id="dtl_orders" class="text-dark">-</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Total Meters</span>
                                    <strong id="dtl_meter" class="text-success font-weight-bold">-</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Roll Count</span>
                                    <strong id="dtl_roll_count" class="text-dark">-</strong>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Cutting Master</span>
                                    <strong id="dtl_master" class="text-dark">-</strong>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 10px;">Total Pieces</span>
                                    <span id="dtl_total_pcs" class="badge px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb;">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- ROLL BREAKDOWN -->
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-xs font-weight-bold text-uppercase text-muted">Roll Breakdown</span>
                            <span class="text-xs text-muted"><i class="fas fa-scroll mr-1"></i>List</span>
                        </div>
                        <div style="max-height: 220px; overflow-y: auto;" class="border rounded">
                            <table class="table erp-table table-sm mb-0 text-xs text-center">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Roll #</th>
                                        <th style="width: 50%;">Meters</th>
                                    </tr>
                                </thead>
                                <tbody id="dtl_rolls_body">
                                    <!-- Rolls populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- RIGHT PANEL: ALLOCATION (8 Columns) -->
                <div class="col-lg-8 col-md-7 mb-3">
                    <div class="erp-card bg-white p-3" style="min-height: 100%;">
                        <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tasks mr-2" style="color: #05421c;"></i>
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Stage Allocation Schedule (Days & Dates)</h6>
                            </div>
                            <span class="badge badge-light border text-xs text-muted">Excludes Cutting (Lot-based)</span>
                        </div>

                        @if(isset($production_stages) && $production_stages->count() > 0)
                            <div class="table-responsive">
                                <table class="table erp-table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Stage Name</th>
                                            <th style="width: 100px;" class="text-center">Days</th>
                                            <th style="width: 170px;">Start Date</th>
                                            <th style="width: 170px;">Expected End Date</th>
                                            <th style="width: 170px;">Actual Complete Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($production_stages as $stage)
                                            @php
                                                if($stage->id == 3) continue; // Skip Cutting stage as it's lot-based
                                                $tx = $transactions[$stage->id] ?? null;
                                            @endphp
                                            <tr>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-light border mr-2 text-muted" style="font-size:11px;">#{{ $stage->id }}</span>
                                                        <strong style="color: #1f2937; font-size: 13px;">{{ $stage->name }}</strong>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" 
                                                           class="form-control form-control-sm text-center font-weight-bold" 
                                                           name="stages[{{ $stage->id }}]" 
                                                           value="{{ ($tx['days_allocated'] ?? 0) > 0 ? $tx['days_allocated'] : ($allocation->{'stage_id_'.$stage->id} ?? '') }}"
                                                           min="0" 
                                                           step="0.5" 
                                                           style="border:1px solid #c3e6cb; background:#fafdf8;"
                                                           required>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="date" 
                                                           class="form-control form-control-sm" 
                                                           name="start_dates[{{ $stage->id }}]" 
                                                           value="{{ isset($tx['start_date']) && !empty($tx['start_date']) ? date('Y-m-d', strtotime($tx['start_date'])) : '' }}">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="date" 
                                                           class="form-control form-control-sm" 
                                                           name="end_dates[{{ $stage->id }}]" 
                                                           value="{{ isset($tx['end_date']) && !empty($tx['end_date']) ? date('Y-m-d', strtotime($tx['end_date'])) : '' }}">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="date" 
                                                           class="form-control form-control-sm" 
                                                           name="complete_dates[{{ $stage->id }}]" 
                                                           value="{{ isset($tx['complete_date']) && !empty($tx['complete_date']) ? date('Y-m-d', strtotime($tx['complete_date'])) : '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i> No Production Stages Found.
                            </div>
                        @endif

                    </div>
                </div>

            </div>

        </form>

    </div>
</div>

<script>
$(function(){
    // Initialize Select2 if needed
    if($.fn.select2) {
        $('.select2').select2();
    }

    let lotNo = $('#lot_no').val();
    if(lotNo) {
        $.ajax({
            url: "{{ route('admin.time_allocation.get-lot-details') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                lot_no: lotNo
            },
            beforeSend: function(){
                // Loader if necessary
            },
            success: function(res){
                if(res) {
                    $('#dtl_fabric').text(res.fabric_names || '-');
                    $('#dtl_color').text(res.color_names || '-');
                    $('#dtl_orders').text(res.order_numbers || '-');
                    $('#dtl_meter').text(res.total_meter || '0');
                    $('#dtl_master').text(res.cutting_master || '-');
                    $('#dtl_roll_count').text(res.roll_count || '0');
                    $('#dtl_total_pcs').text((res.total_quantity ? res.total_quantity + ' pcs' : '0 pcs'));

                    let rows = '';
                    if(res.roll_details && res.roll_details.length > 0) {
                        res.roll_details.forEach(function(r){
                            rows += `<tr>
                                <td class="font-weight-bold">Roll #${r.roll_no}</td>
                                <td class="text-success font-weight-bold">${r.meter} m</td>
                            </tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="2" class="text-center text-muted">No Rolls Found</td></tr>';
                    }
                    $('#dtl_rolls_body').html(rows);

                    $('#lotDetailsCard').removeClass('d-none');
                } else {
                    $('#lotDetailsCard').addClass('d-none');
                }
            },
            error: function(){
                console.log('Error fetching lot details');
            }
        });
    }
});
</script>
@endsection
