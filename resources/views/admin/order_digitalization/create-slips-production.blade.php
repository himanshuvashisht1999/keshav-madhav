@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="mb-3">Production Slips Digitalization (Hand Slip)</h1>
                </div>
            </div>
          
            {{-- OPTIONAL HEADER ACTIONS (Skip/Limit etc) --}}
            <div class="row">
                <div class="col-12 text-right">
                    @if(!empty($slip_data))
                        <!-- <form action="{{ route('admin.order_digitalization.skip') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                            <button type="submit" class="btn btn-secondary mr-2">Skip</button>
                        </form> -->

                        <!-- <form action="{{ route('admin.order_digitalization.delete-slip') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slip?');">
                            @csrf
                            <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                            <button type="submit" class="btn btn-danger mr-2">Delete</button>
                        </form> -->
                    @endif
                </div>
            </div>
            <hr class="mt-3 mb-0">
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card p-3 shadow-sm">

                @if(!empty($slip_data))
                    <div id="slip_digitalization">
                        <form method="POST" action="{{ route('admin.order_digitalization.store-hand-slip') }}" id="handSlipForm">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">

                        <div class="row">
                            {{-- LEFT PANEL (Inputs) --}}
                            <div class="col-md-7"> <!-- 70% width style -->
                                <div class="card p-3 mb-3 border">
                                    <div class="d-flex justify-content-between">
                                         <label>Date: <strong>{{ getformatDateTime($slip_data['date_time']) }}</strong></label>
                                    </div>
                                   
                                    {{-- LOT INPUT --}}
                                    <div class="my-3">
                                        <label class="font-weight-bold">Select Lot No.</label>
                                        <select name="lot_no" id="lot_no_input" class="form-control select2" style="width: 100%;">
                                            <option value="">Select Lot</option>
                                            @if(isset($available_lots) && count($available_lots) > 0)
                                                @foreach($available_lots as $lot)
                                                    <option value="{{ $lot->lot_no }}">{{ $lot->lot_no }}</option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No available lots found for this stage</option>
                                            @endif
                                        </select>
                                        <!-- <button type="button" class="btn btn-primary mt-2" id="fetchLotBtn">Check</button> -->
                                    </div>

                                    {{-- STAGE INFO --}}
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>From Stage</label>
                                            <input type="text" class="form-control" value="{{ $slip_data['from_stage']['name'] }} ({{ $slip_data['from_stage']['master_stage_name'] }})" readonly>
                                            <input type="hidden" name="from_stage_id" value="{{ $slip_data['from_stage']['id'] }}"> 
                                            <input type="hidden" name="from_stage_id_ajax" value="{{ $slip_data['from_stage']['master_stage_id'] }}"> 
                                        </div>
                                        <div class="col-md-6">
                                            <label>To Stage</label>
                                            <select name="to_stage_id" class="form-control select2" required>
                                                <option value="">Select Stage</option>
                                                @foreach($slip_data['unit_master_data'] as $unit)
                                                    <option value="{{ $unit['id'] }}">{{ $unit['name'] }} ({{ $unit['master_stage_name'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- LOT DETAILS & INVENTORY (Dynamic) --}}
                                <div id="lotDetailsCard" class="card p-3 border d-none">
                                    <h5 class="text-primary">Lot Inventory Details</h5>
                                    
                                    <div id="basicInfo" class="mb-3 p-2 bg-light rounded">
                                        <!-- Basic API info here -->
                                    </div>

                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Size</th>
                                                <th>Available Qty</th>
                                                <th style="width: 150px;">Send Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inventoryTableBody">
                                            <!-- Valid Sizes Loaded Here -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-right font-weight-bold">Total Moving:</td>
                                                <td class="font-weight-bold" id="totalMovingQty">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- RIGHT PANEL (Image) --}}
                            <div class="col-md-5">
                                <div class="card p-3 border text-center">
                                    <img src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}" 
                                         class="img-fluid rounded shadow-sm" style="max-height: 600px;">
                                </div>
                            </div>
                        </div>

                        {{-- SUBMIT --}}
                        <div class="row mt-3">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-success btn-lg">Process Slip</button>
                            </div>
                        </div>

                        </form>
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h4>No Production Slips Available</h4>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<style>
.lot-input-wrapper{background:#f8f9fa;border:2px solid #28a745;border-radius:10px;padding:10px; display:flex; gap:10px; align-items: center;}
.lot-input-label{font-weight:900;font-size:18px; margin:0;}
.lot-input{flex:1;padding:10px;font-size:20px;font-weight:700;border:2px dashed #28a745;border-radius:6px;text-align:center}
</style>

<script>
$(function(){
    $('.select2').select2();

    // Trigger fetch on dropdown change
    $('#lot_no_input').on('change', function(){
        fetchLotDetails();
    });

    // $('#fetchLotBtn').click(function(){
    //     fetchLotDetails();
    // }); 

    function fetchLotDetails() {
        let lotNo = $('#lot_no_input').val();
        let fromStageId = $('input[name="from_stage_id_ajax"]').val();

        if(!lotNo) {
            alert('Please enter a Lot Number');
            return;
        }

        $.ajax({
            url: "{{ route('admin.order_digitalization.get-lot-details-for-hand-slip') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                lot_no: lotNo,
                from_stage_id: fromStageId
            },
            success: function(response){
                if(response.inventory && Object.keys(response.inventory).length > 0) {
                    renderInventory(response.inventory);
                    renderBasicInfo(response.basic_info);
                    $('#lotDetailsCard').removeClass('d-none');
                } else {
                    $('#lotDetailsCard').addClass('d-none');
                    alert('No inventory found for this Lot at current stage.');
                }
            },
            error: function(xhr){
                alert('Error fetching details.');
            }
        });
    }

    function renderBasicInfo(info) {
        let html = '';
        if(info) {
             if(info.fabric_names) html += `<div><strong>Fabric:</strong> ${info.fabric_names}</div>`;
             if(info.order_numbers) html += `<div><strong>Orders:</strong> ${info.order_numbers}</div>`;
        }
        $('#basicInfo').html(html);
    }

    function renderInventory(inventory) {
        let tbody = $('#inventoryTableBody');
        tbody.empty();

        $.each(inventory, function(size, qty){
            if(qty > 0) {
                tbody.append(`
                    <tr>
                        <td class="font-weight-bold">${size}</td>
                        <td>${qty}</td>
                        <td>
                            <input type="number" name="sizes[${size}]" 
                                   class="form-control form-control-sm send-qty" 
                                   min="0" max="${qty}" 
                                   placeholder="0">
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('input', '.send-qty', function(){
        let total = 0;
        $('.send-qty').each(function(){
            total += parseInt($(this).val()) || 0;
        });
        $('#totalMovingQty').text(total);
    });

    $('#handSlipForm').on('submit', function(e){
        let totalRaw = $('#totalMovingQty').text();
        let total = parseInt(totalRaw) || 0;
        if(total <= 0) {
            alert('Please enter quantity to move.');
            e.preventDefault();
        }
    });

});
</script>
@endsection
