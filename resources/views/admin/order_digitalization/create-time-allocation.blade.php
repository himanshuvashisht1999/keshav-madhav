@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="erp-page p-2">

        <!-- HEADER BAR -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title d-flex align-items-center flex-wrap">
                <i class="fas fa-business-time mr-2 text-success"></i>
                <span>Stage Wise Time Allocation</span>
                @if(!empty($slip_data))
                    <span class="badge ml-2 px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb; font-size:12px;">
                        Slip #{{ $slip_data['id'] }}
                    </span>
                @endif
            </div>
            <div class="erp-header-actions d-flex align-items-center flex-wrap" style="gap: 6px;">
                @if(!empty($skip_slip_data))
                    <form action="{{ route('admin.order_digitalization.add-skip-slip') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn-erp btn-erp-outline btn-sm">
                            <i class="fas fa-redo mr-1"></i> Add Skipped Slips ({{$skip_slip_data}})
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.order_digitalization.create-slips-production') }}" class="btn-erp btn-erp-outline btn-sm">
                    <i class="fas fa-file-signature mr-1"></i> Slips Digitalization
                </a>
                @if(!empty($slip_data))
                    <form action="{{ route('admin.order_digitalization.skip') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn-erp btn-erp-outline btn-sm">
                            <i class="fas fa-forward mr-1"></i> Skip
                        </button>
                    </form>
                    <form action="{{ route('admin.order_digitalization.delete-slip') }}" method="POST" class="d-inline m-0" onsubmit="return confirmDeleteSlip();">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn-erp btn-erp-danger btn-sm">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(!empty($slip_data))
            <form method="POST" id="rollAssignForm" action="{{ route('admin.order_digitalization.store-time-allocation') }}">
                @csrf

                <div class="row">
                    <!-- LEFT PANEL -->
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="erp-card bg-white p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle mr-2 text-success"></i>
                                    <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Basic Information</h6>
                                </div>
                                <span class="badge badge-light border text-xs">
                                    Date: {{ getformatDateTime($slip_data['date_time']) }}
                                </span>
                            </div>

                            <input type="hidden" name="slip_create_date_time" value="{{ $slip_data['date_time'] }}">

                            <div class="form-group mb-3">
                                <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Production Start Date & Time</label>
                                <input type="datetime-local" name="start_date_time" class="form-control erp-input">
                            </div>

                            <!-- LOT NO -->
                            <div class="form-group mb-0">
                                <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Lot Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-layer-group text-muted"></i></span>
                                    </div>
                                    <input type="text" id="lot_no" name="lot_no" class="form-control erp-input border-left-0 font-weight-bold"
                                           placeholder="Enter Lot Number"
                                           inputmode="numeric"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
                                </div>
                                <small class="text-danger" id="err_lot_no"></small>
                            </div>
                        </div>

                        <!-- STAGE TIME SCHEDULE CARD -->
                        <div class="erp-card bg-white p-3">
                            <div class="d-flex align-items-center pb-2 mb-3 border-bottom">
                                <i class="fas fa-clock mr-2 text-success"></i>
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Stage Allocation (In Days)</h6>
                            </div>

                            <div class="table-responsive">
                                <table class="table erp-table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Stage Name</th>
                                            <th style="width: 140px;" class="text-center">Time (Days)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($slip_data['unit_master_data'] as $stage_data)
                                            <tr>
                                                <td class="align-middle font-weight-bold" style="color: #1f2937;">
                                                    {{ $stage_data['master_stage_name'] }}
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" 
                                                           class="form-control form-control-sm text-center font-weight-bold" 
                                                           placeholder="Days"
                                                           name="stages[{{ $stage_data['master_stage_id'] }}]" 
                                                           min="0.5" 
                                                           step="0.5" 
                                                           style="border: 1px solid #c3e6cb;"
                                                           required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                                <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                                <input type="hidden" id="from_stage_id" value="{{ $slip_data['from_stage']['master_stage_id'] }}">

                                <button type="submit" id="submit" class="btn-erp btn-erp-primary px-4 py-2">
                                    <i class="fas fa-save mr-1"></i> Save Time Allocation
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: SLIP IMAGE -->
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="erp-card bg-white p-3 text-center" style="min-height: 100%;">
                            <div class="d-flex align-items-center pb-2 mb-3 border-bottom">
                                <i class="fas fa-image mr-2 text-success"></i>
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Attached Slip Document</h6>
                            </div>
                            <div class="p-2 bg-light rounded border text-center">
                                <img src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}"
                                     class="img-fluid rounded" style="max-height: 480px; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        @else
            <div class="erp-card bg-white p-5 text-center">
                <i class="fas fa-file-invoice text-muted mb-3" style="font-size: 40px;"></i>
                <h5 class="font-weight-bold text-dark">No Production Slips Available</h5>
                <p class="text-muted">There are currently no slips available for time allocation.</p>
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Return to Slips List
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
