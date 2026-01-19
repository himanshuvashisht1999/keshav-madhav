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
                
                <form method="POST" id="timeAllocationForm" action="{{ route('admin.time_allocation.store') }}">
                    @csrf

                    <div class="row">

                        <!-- LEFT PANEL: DETAILS (4 Columns) -->
                        <div class="col-md-4">
                            <div class="card p-3 mb-3 border shadow-sm">
                                <h5 class="text-primary mb-3">Basic Information</h5>

                                <div class="form-group mb-3">
                                    <label>Start Date & Time</label>
                                    <input type="datetime-local" name="start_date_time" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                                </div>

                                <!-- LOT NO -->
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Lot No. *</label>
                                    <select name="lot_no" id="lot_no" class="form-control select2" required style="width: 100%;">
                                        <option value="">Select Lot No</option>
                                        @foreach($available_lots as $lot)
                                            <option value="{{ $lot }}">{{ $lot }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger" id="err_lot_no"></small>
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
                                    <div class="row">
                                        @foreach($production_stages as $stage)
                                            <div class="col-md-6 mb-3">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white" style="width: 180px; text-align: left; display: inline-block;">
                                                            {{ $stage->name }}
                                                        </span>
                                                    </div>
                                                    <input type="number" class="form-control bg-light" 
                                                        placeholder="Days"
                                                        name="stages[{{ $stage->id }}]" 
                                                        min="0" step="0.5" required>
                                                </div>
                                            </div>
                                        @endforeach
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

    // Fetch Lot Details on Change
    $('#lot_no').on('change', function(){
        let lotNo = $(this).val();
        
        if(!lotNo) {
            $('#lotDetailsCard').addClass('d-none');
            return;
        }

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
                alert('Error fetching lot details');
            }
        });
    });
});
</script>
@endsection
