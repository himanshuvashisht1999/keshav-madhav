@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="mb-3">Stage Wise Time Allocation</h1>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card p-3 shadow-sm">
                
                <form method="POST" id="timeAllocationForm" action="{{ route('admin.time_allocation.update', $allocation->id) }}">
                    @csrf

                    <div class="row">

                        <!-- LEFT PANEL: DETAILS (4 Columns) -->
                        <div class="col-md-4">
                            <div class="card p-3 mb-3 border shadow-sm">
                                <h5 class="text-primary mb-3">Basic Information</h5>

                                <div class="form-group mb-3">
                                    <label>Start Date & Time</label>
                                    <!-- <input type="datetime-local" name="start_date_time" class="form-control datetime-picker" value="{{ date('Y-m-d\TH:i') }}" required> -->
                                    <input type="text"
                                            class="form-control"
                                            value="{{ date('Y-m-d H:i:s', strtotime($allocation->start_date_time)) }}"
                                            readonly>
                                </div>

                                <!-- LOT NO -->
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Lot No. *</label>
                                    <input type="text" class="form-control" id="lot_no" value="{{ $allocation->lot_no }}" readonly>
                                </div>
                            </div>

                            <!-- LOT DETAILS CONTAINER -->
                            <div id="lotDetailsCard" class="card p-3 border bg-light d-none shadow-sm">
                                <h6 class="text-primary font-weight-bold mb-3">Selected Lot Details</h6>
                                <div class="row text-sm">
                                    <div class="col-12 mb-1"><strong>Fabric:</strong> <span id="dtl_fabric">-</span></div>
                                    <div class="col-12 mb-1"><strong>Color:</strong> <span id="dtl_color">-</span></div>
                                    <div class="col-12 mb-1"><strong>Orders:</strong> <span id="dtl_orders">-</span></div>
                                    <div class="col-6 mb-1"><strong>Meters:</strong> <span id="dtl_meter">-</span></div>
                                    <div class="col-6 mb-1"><strong>Rolls:</strong> <span id="dtl_roll_count">-</span></div>
                                    <div class="col-12 mb-1"><strong>Master:</strong> <span id="dtl_master">-</span></div>
                                    <div class="col-12 mb-1"><strong>Total Pcs:</strong> <span id="dtl_total_pcs">-</span></div>
                                </div>
                                <hr class="my-2">
                                <h6 class="font-weight-bold text-xs">Roll Breakdown</h6>
                                <div style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered bg-white mb-0 text-xs text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Roll #</th>
                                                <th>Meters</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dtl_rolls_body">
                                            <!-- Rolls -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT PANEL: ALLOCATION (8 Columns) -->
                        <div class="col-md-8">
                            <div class="card p-3 border shadow-sm" style="min-height: 100%;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="m-0">Stage Allocation (Days)</h5>
                                    <div>
                                         <button type="submit" id="submit" class="btn btn-success shadow-sm">
                                            <i class="fas fa-save mr-1"></i> Save Allocation
                                        </button>
                                    </div>
                                </div>
                                <hr class="mt-0">

                                @if(isset($production_stages) && $production_stages->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Stage</th>
                                                    <th style="width: 80px;">Days</th>
                                                    <th>Start Date</th>
                                                    <th>End Date (Expected)</th>
                                                    <th>Complete Date (Actual)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($production_stages as $stage)
                                                    @php
                                                        if($stage->id == 3) continue; // Skip Cutting stage as it's lot-based
                                                        $tx = $transactions[$stage->id] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td class="align-middle fw-bold">{{ $stage->name }}</td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                name="stages[{{ $stage->id }}]" 
                                                                value="{{ ($tx['days_allocated'] ?? 0) > 0 ? $tx['days_allocated'] : ($allocation->{'stage_id_'.$stage->id} ?? '') }}"
                                                                min="0" step="0.5" required>
                                                        </td>
                                                        <td>
                                                            <input type="datetime-local" class="form-control form-control-sm" 
                                                                name="start_dates[{{ $stage->id }}]" 
                                                                value="{{ $tx['start_date'] ?? '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="datetime-local" class="form-control form-control-sm" 
                                                                name="end_dates[{{ $stage->id }}]" 
                                                                value="{{ $tx['end_date'] ?? '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="datetime-local" class="form-control form-control-sm" 
                                                                name="complete_dates[{{ $stage->id }}]" 
                                                                value="{{ $tx['complete_date'] ?? '' }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-warning">No Stages Found</div>
                                @endif
                                
                            </div>
                        </div>

                    </div>

                </form>
               
            </div>

        </div>
    </section>
</div>

<script>
$(function(){
    // Initialize Select2
    $('.select2').select2();

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
                // Optional: Show loader
            },
            success: function(res){
                if(res) {
                    $('#dtl_fabric').text(res.fabric_names || '-');
                    $('#dtl_color').text(res.color_names || '-');
                    $('#dtl_orders').text(res.order_numbers || '-');
                    $('#dtl_meter').text(res.total_meter || '0');
                    $('#dtl_master').text(res.cutting_master || '-');
                    $('#dtl_roll_count').text(res.roll_count || '0');
                    $('#dtl_total_pcs').text((res.total_quantity + ' pcs') || '0 pcs');

                    let rows = '';
                    if(res.roll_details && res.roll_details.length > 0) {
                        res.roll_details.forEach(function(r){
                            rows += `<tr>
                                <td>${r.roll_no}</td>
                                <td>${r.meter}</td>
                            </tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="2" class="text-center">No Rolls Found</td></tr>';
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
