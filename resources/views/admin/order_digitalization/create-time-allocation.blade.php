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
        <div class="row">
            <div class="col-12 text-right">
                @if(!empty($skip_slip_data))
                    <form action="{{ route('admin.order_digitalization.add-skip-slip') }}"
                        method="POST"
                        class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info mr-2">
                            Add Skip slips for Digitalization (Available - {{$skip_slip_data}} Slips)
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.order_digitalization.create-slips-production') }}" class="btn btn-success mr-2">
                    Slips Digitalization
                </a>
                @if(!empty($slip_data))

                    <!-- SKIP FORM -->
                    <form action="{{ route('admin.order_digitalization.skip') }}"
                        method="POST"
                        class="d-inline">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn btn-secondary mr-2">
                            Skip
                        </button>
                    </form>

                    <!-- DELETE FORM -->
                    <form action="{{ route('admin.order_digitalization.delete-slip') }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirmDeleteSlip();">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn btn-danger mr-2">
                            Delete
                        </button>
                    </form>

                @endif
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card p-3 shadow-sm">
                @if(!empty($slip_data))
                <form method="POST" id="rollAssignForm" action="{{ route('admin.order_digitalization.store-time-allocation') }}">
                    @csrf

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">
                            <div class="card p-3 mb-3 border">

                                <label>Date - {{ getformatDateTime($slip_data['date_time']) }}</label>
                                <input type="hidden" name="slip_create_date_time"
                                       value="{{ $slip_data['date_time'] }}">
                                <label>Start Date & Time</label>
                                <input type="datetime-local" name="start_date_time" class="form-control">
                                {{-- <label>Order No.</label>
                                <input type="text" id="order_no" class="form-control mb-2"> --}}

                                <!-- LOT NO -->
                                <div class="lot-input-wrapper my-3 lot-inline">
                                    <label class="lot-input-label">Lot No.</label>
                                    <input type="text" id="lot_no" name="lot_no" class="lot-input"
                                           placeholder="Enter Lot Number"
                                           inputmode="numeric"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
                                </div>
                                <small class="text-danger" id="err_lot_no"></small>

                                <!-- TO -->
                                {{-- <label>Cutting Master</label>
                                <select id="to_master_unit" class="form-control select2 mb-1">
                                    <option value="">Select Cutting Master</option>
                                    @foreach($cutting_units as $unit)
                                        <option value="{{ $unit['id'] }}">
                                            {{ $unit['cutting_master_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger" id="err_cutting_unit"></small> --}}
                            </div>
                            <div class="row align-items-center mb-2">
                                <div class="col-md-6">
                                    <label class="fw-bold">Stage Name</label>
                                </div>

                                <!-- RIGHT : Time -->
                                <div class="col-md-6">
                                    <label class="fw-bold">Time (in Days)</label>   
                                </div>
                                @foreach($slip_data['unit_master_data'] as $stage_data)
                                    

                                        <!-- LEFT : Stage Name -->
                                        <div class="col-md-6 mb-1">
                                            <label class="fw-bold">{{$stage_data['master_stage_name']}}</label>
                                        </div>

                                        <!-- RIGHT : Time -->
                                        <div class="col-md-6 mb-1">
                                            <input type="number" class="form-control bg-light" placeholder="Enter allowed days"
                                                name="stages[{{ $stage_data['master_stage_id'] }}]" min="0.5" step="0.5" required>
                                        </div>
                                    <hr>
                                @endforeach
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">
                            <div class="card p-3 border">
                                <img src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}"
                                     class="img-fluid rounded">
                            </div>
                        </div>

                    </div>

                    <!-- BUTTONS -->
                    <div class="row mt-3">
                        <div class="col-12 text-right">
                            <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                            <input type="hidden" id="from_stage_id" value="{{ $slip_data['from_stage']['master_stage_id'] }}">

                            <button type="submit" id="submit" class="btn btn-success">
                                Submit
                            </button>

                        </div>
                    </div>

                </form>
                @else
                    <div class="alert alert-info text-center">
                        No Production Slips Available
                    </div>
                @endif
            </div>

        </div>
    </section>
</div>

<style>
.lot-inline{display:flex;align-items:center;gap:15px}
.lot-input-wrapper{background:#f8f9fa;border:2px solid #28a745;border-radius:10px;padding:10px}
.lot-input-label{font-weight:900;font-size:18px}
.lot-input{flex:1;padding:12px;font-size:20px;font-weight:700;border:2px dashed #28a745;border-radius:6px;text-align:center}
</style>

<script>
$(function(){
  

});
</script>
@endsection
