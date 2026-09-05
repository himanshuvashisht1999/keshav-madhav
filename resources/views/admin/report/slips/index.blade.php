@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header pb-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="font-weight-bold text-dark mb-1" style="font-size: 24px;">
                        <i class="fas fa-file-invoice text-primary mr-2"></i>Slip-wise Production Report
                    </h1>
                    <p class="text-muted small mb-0">Overview and multi-entry analysis of all production slips, lots, and stages</p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('admin.reports.slips.export', request()->query()) }}" class="btn btn-sm btn-success shadow-sm font-weight-bold px-3 mr-2">
                        <i class="fas fa-file-excel mr-1"></i> Export to Excel
                    </a>
                    <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                        <i class="fas fa-cloud-upload-alt mr-1"></i> Uploaded Slips Module
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <!-- KPI SUMMARY CARDS -->
            <div class="row mb-3">
                <!-- Total Slips -->
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                        <div class="card-body p-3 text-white d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-white-50 small text-uppercase font-weight-bold tracking-wider mb-1">Total Slips</div>
                                <div class="h2 font-weight-bold mb-0">{{ number_format($kpis['total_slips']) }}</div>
                                <div class="small mt-1 text-white-50">
                                    <span class="text-white font-weight-bold">{{ $kpis['digitized_slips'] }}</span> Digitized &bull; 
                                    <span class="text-white font-weight-bold">{{ $kpis['pending_slips'] }}</span> Pending
                                </div>
                            </div>
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; opacity: 0.2;">
                                <i class="fas fa-receipt fa-2x text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Processed Pieces -->
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                        <div class="card-body p-3 text-white d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-white-50 small text-uppercase font-weight-bold tracking-wider mb-1">Total Pieces</div>
                                <div class="h2 font-weight-bold mb-0">{{ number_format($kpis['total_pieces']) }}</div>
                                <div class="small mt-1 text-white-50">Across all digitized entries</div>
                            </div>
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; opacity: 0.2;">
                                <i class="fas fa-tshirt fa-2x text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Unique Lots -->
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <div class="card-body p-3 text-white d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-white-50 small text-uppercase font-weight-bold tracking-wider mb-1">Distinct Lots</div>
                                <div class="h2 font-weight-bold mb-0">{{ number_format($kpis['total_unique_lots']) }}</div>
                                <div class="small mt-1 text-white-50">Unique production lots processed</div>
                            </div>
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; opacity: 0.2;">
                                <i class="fas fa-layer-group fa-2x text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completion Status Breakdown -->
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg h-100 bg-white border">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase font-weight-bold mb-2">Digitization Status</div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-success"><i class="fas fa-check-circle mr-1"></i> Digitized</span>
                                <span class="badge badge-success font-weight-bold">{{ $kpis['digitized_slips'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-warning"><i class="fas fa-clock mr-1"></i> Pending</span>
                                <span class="badge badge-warning text-dark font-weight-bold">{{ $kpis['pending_slips'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small font-weight-bold text-danger"><i class="fas fa-times-circle mr-1"></i> Skipped</span>
                                <span class="badge badge-danger font-weight-bold">{{ $kpis['skipped_slips'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER CARD -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-filter text-primary mr-2"></i>Filter Slips & Production Entries</h6>
                </div>
                <div class="card-body bg-light p-3">
                    <form method="GET" action="{{ route('admin.reports.slips') }}">
                        <div class="row align-items-end">
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Bill / Slip No</label>
                                <input type="text" name="bill_number" class="form-control form-control-sm" placeholder="e.g. 101 or #24" value="{{ request('bill_number') }}">
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Lot Number</label>
                                <input type="text" name="lot_no" class="form-control form-control-sm" placeholder="Search Lot..." value="{{ request('lot_no') }}">
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Design Number</label>
                                <select name="design_no" class="form-control select2 form-control-sm">
                                    <option value="">-- All Designs --</option>
                                    @if(isset($designs))
                                        @foreach($designs as $dsn)
                                            <option value="{{ $dsn }}" {{ request('design_no') == $dsn ? 'selected' : '' }}>
                                                {{ $dsn }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">From Stage</label>
                                <select name="from_stage_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All Stages --</option>
                                    @foreach($stages as $stg)
                                        <option value="{{ $stg->id }}" {{ request('from_stage_id') == $stg->id ? 'selected' : '' }}>
                                            {{ $stg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">To Stage</label>
                                <select name="to_stage_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All Stages --</option>
                                    @foreach($stages as $stg)
                                        <option value="{{ $stg->id }}" {{ request('to_stage_id') == $stg->id ? 'selected' : '' }}>
                                            {{ $stg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">From Unit</label>
                                <select name="from_unit_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All From Units --</option>
                                    @foreach($units as $un)
                                        <option value="{{ $un->id }}" {{ request('from_unit_id') == $un->id || request('stage_master_unit_id') == $un->id ? 'selected' : '' }}>
                                            {{ $un->name }} ({{ $un->masterStage->name ?? 'Unit' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">To Unit</label>
                                <select name="to_unit_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All To Units --</option>
                                    @foreach($units as $un)
                                        <option value="{{ $un->id }}" {{ request('to_unit_id') == $un->id ? 'selected' : '' }}>
                                            {{ $un->name }} ({{ $un->masterStage->name ?? 'Unit' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Status</label>
                                <select name="status" class="form-control select2 form-control-sm">
                                    <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>-- All Statuses --</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Digitized</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Skipped</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-2 d-flex align-items-center">
                                <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm mr-2 font-weight-bold">
                                    <i class="fas fa-search mr-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('admin.reports.slips') }}" class="btn btn-sm btn-outline-secondary px-3 shadow-sm mr-2">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SLIPS TABLE CARD -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-list text-primary mr-2"></i>Slip Records 
                        <span class="badge badge-light border ml-2 text-muted">{{ $slips->total() }} total slips</span>
                    </h6>
                    <div class="small text-muted">Showing {{ $slips->firstItem() ?? 0 }} - {{ $slips->lastItem() ?? 0 }} of {{ $slips->total() }}</div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead style="background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="py-3 px-3 text-nowrap">Slip ID / Bill</th>
                                    <th class="py-3 text-nowrap">Date</th>
                                    <th class="py-3 text-nowrap">From Stage & Unit</th>
                                    <th class="py-3 text-nowrap">To Stage(s)</th>
                                    <th class="py-3 text-nowrap">Lots Involved</th>
                                    <th class="py-3 text-nowrap">Designs</th>
                                    <th class="py-3 text-center text-nowrap">Entries</th>
                                    <th class="py-3 text-right text-nowrap">Total Pieces</th>
                                    <th class="py-3 text-center text-nowrap">Status</th>
                                    <th class="py-3 text-right px-3 text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 13px;">
                                @forelse($slips as $slip)
                                    @php
                                        $computed = $slip->computed_data;
                                    @endphp
                                    <tr>
                                        <!-- Slip ID & Bill -->
                                        <td class="px-3 text-nowrap">
                                            <a href="{{ route('admin.reports.slips.show', $slip->id) }}" class="font-weight-bold text-primary" style="font-size: 14px;">
                                                #{{ $slip->id }}
                                            </a>
                                            @if($slip->bill_number)
                                                <div class="text-xs text-muted font-weight-bold">
                                                    Bill: <span class="text-dark">{{ $slip->bill_number }}</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Date -->
                                        <td class="text-nowrap">
                                            <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                                {{ $slip->created_at->format('d M, Y') }}
                                            </div>
                                        </td>

                                        <!-- From Stage & Unit -->
                                        <td>
                                            <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                                <i class="fas fa-layer-group text-primary mr-1"></i>{{ $slip->fromStage->name ?? '-' }}
                                            </span>
                                            @if($slip->getUnitMaster)
                                                <div class="small text-muted mt-1 text-nowrap">
                                                    <i class="fas fa-user-tie mr-1 text-secondary"></i>{{ $slip->getUnitMaster->name }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- To Stages & Destination Units -->
                                        <td>
                                            @if(!empty($computed['destinations']))
                                                @foreach($computed['destinations'] as $dest)
                                                    <span class="badge badge-light border text-dark mb-1 text-nowrap" style="font-size: 11px;">
                                                        <i class="fas fa-arrow-right text-success mr-1"></i>{{ $dest }}
                                                    </span><br>
                                                @endforeach
                                            @elseif($slip->toStage)
                                                <span class="badge badge-light border text-dark text-nowrap" style="font-size: 11px;">
                                                    <i class="fas fa-arrow-right text-success mr-1"></i>{{ $slip->toStage->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif

                                            @if(!empty($computed['destination_units']))
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-users-cog mr-1"></i>{{ implode(', ', $computed['destination_units']) }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Lots Involved -->
                                        <td>
                                            @if(!empty($computed['lots_with_qty']))
                                                <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                                    @foreach($computed['lots_with_qty'] as $lot => $lqty)
                                                        <span class="badge badge-info shadow-xs mb-1 mr-1 px-2 py-1" style="font-size: 11px;">
                                                            #{{ $lot }} @if($lqty > 0) <span class="badge badge-light text-dark ml-1">{{ number_format($lqty) }}</span> @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Designs -->
                                        <td>
                                            @if(!empty($computed['designs_with_qty']))
                                                <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 240px;">
                                                    @foreach($computed['designs_with_qty'] as $dsn => $dqty)
                                                        <span class="badge shadow-xs mb-1 mr-1 px-2 py-1 font-weight-bold" style="font-size: 11px; background-color: #6366f1; color: #ffffff;">
                                                            {{ $dsn }} @if($dqty > 0) <span class="badge badge-light text-dark ml-1 font-weight-bold">{{ number_format($dqty) }}</span> @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @elseif(!empty($computed['designs']))
                                                <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 200px;">
                                                    @foreach($computed['designs'] as $dsn)
                                                        <span class="badge shadow-xs mb-1 mr-1 px-2 py-1 font-weight-bold" style="font-size: 11px; background-color: #6366f1; color: #ffffff;">
                                                            {{ $dsn }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Entries / Sessions Count -->
                                        <td class="text-center text-nowrap">
                                            <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                {{ $computed['entries_count'] }} {{ Str::plural('Entry', $computed['entries_count']) }}
                                            </span>
                                        </td>

                                        <!-- Total Pieces -->
                                        <td class="text-right text-nowrap font-weight-bold" style="font-size: 14px; color: #047857;">
                                            {{ number_format($computed['total_quantity']) }} <small class="text-muted">pcs</small>
                                        </td>

                                        <!-- Status -->
                                        <td class="text-center text-nowrap">
                                            @if($slip->status == 1)
                                                <span class="badge badge-success px-2 py-1 font-weight-bold">
                                                    <i class="fas fa-check-circle mr-1"></i>Digitized
                                                </span>
                                            @elseif($slip->status == 0)
                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i>Pending
                                                </span>
                                            @else
                                                <span class="badge badge-danger px-2 py-1 font-weight-bold">
                                                    <i class="fas fa-times-circle mr-1"></i>Skipped
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="text-right px-3 text-nowrap">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.reports.slips.show', $slip->id) }}" class="btn btn-outline-primary shadow-xs" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($slip->image)
                                                    <button type="button" class="btn btn-outline-dark shadow-xs" data-toggle="modal" data-target="#slipImageModal{{ $slip->id }}" title="View Physical Slip Photo">
                                                        <i class="fas fa-image"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('admin.reports.slips.pdf', $slip->id) }}" class="btn btn-outline-danger shadow-xs" title="Download PDF Report">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>

                                            <!-- Slip Image Modal -->
                                            @if($slip->image)
                                                <div class="modal fade text-left" id="slipImageModal{{ $slip->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                        <div class="modal-content border-0 shadow-lg">
                                                            <div class="modal-header bg-dark text-white py-2 px-3">
                                                                <h6 class="modal-title font-weight-bold mb-0">
                                                                    <i class="fas fa-image mr-2"></i>Physical Slip #{{ $slip->id }}
                                                                </h6>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center p-0 bg-light">
                                                                <img src="{{ asset('storage/' . $slip->image) }}" class="img-fluid rounded" style="max-height: 80vh; width: auto;" alt="Slip #{{ $slip->id }}">
                                                            </div>
                                                            <div class="modal-footer py-2 px-3 bg-white justify-content-between">
                                                                <div class="small text-muted font-weight-bold">
                                                                    Uploaded: {{ $slip->created_at->format('d M, Y h:i A') }}
                                                                </div>
                                                                <a href="{{ asset('storage/' . $slip->image) }}" target="_blank" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-external-link-alt mr-1"></i> Open Original Image
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block" style="opacity: 0.4;"></i>
                                            <h6 class="font-weight-bold">No production slips found matching your filters.</h6>
                                            <p class="small text-muted mb-0">Try changing date range, stages, or clearing filters.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($slips->hasPages())
                    <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing {{ $slips->firstItem() }} to {{ $slips->lastItem() }} of {{ $slips->total() }} entries
                        </div>
                        <div>
                            {{ $slips->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </section>
</div>
@endsection

@section('scripts')
<style>
    .select2-container .select2-selection--single {
        height: 31px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        font-size: 13px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
        padding-left: 8px !important;
        color: #495057 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
    }
</style>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default',
            width: '100%',
            allowClear: true,
            placeholder: function(){
                $(this).data('placeholder');
            }
        });
    });
</script>
@endsection
