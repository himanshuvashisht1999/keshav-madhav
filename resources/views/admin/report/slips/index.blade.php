@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-file-invoice"></i> Slip-wise production report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-layer-group text-muted"></i> Multi-entry production slips, lots, and stages</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.reports.slips.export', request()->query()) }}" class="btn-erp btn-erp-outline mr-1" style="border-color: #22c55e; color: #166534;" title="Export to Excel">
                <i class="fas fa-file-excel mr-1 text-success"></i> Export Excel
            </a>
            @if(Route::has('admin.uploaded-slips.index'))
            <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-cloud-upload-alt mr-1"></i> Uploaded slips
            </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row mb-2">
        <!-- Total Slips -->
        <div class="col-xl-3 col-md-6 col-12 mb-1">
            <div class="erp-card p-2 mb-0 d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--brand-dark-green, #05421c);">
                <div>
                    <span class="small text-muted font-weight-bold text-uppercase" style="font-size: 11px;">Total slips</span>
                    <h4 class="m-0 font-weight-bold text-dark" style="font-size: 18px;">{{ number_format($kpis['total_slips']) }}</h4>
                    <div class="small mt-1 text-muted" style="font-size: 11px;">
                        <span class="text-success font-weight-bold">{{ $kpis['digitized_slips'] }}</span> digitized &bull; 
                        <span class="text-warning font-weight-bold">{{ $kpis['pending_slips'] }}</span> pending
                    </div>
                </div>
                <div class="rounded-circle p-2 bg-light text-success">
                    <i class="fas fa-receipt fa-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Processed Pieces -->
        <div class="col-xl-3 col-md-6 col-12 mb-1">
            <div class="erp-card p-2 mb-0 d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--brand-yellow, #fcee21);">
                <div>
                    <span class="small text-muted font-weight-bold text-uppercase" style="font-size: 11px;">Total pieces</span>
                    <h4 class="m-0 font-weight-bold text-dark" style="font-size: 18px;">{{ number_format($kpis['total_pieces']) }} <span class="small font-weight-normal text-muted">pcs</span></h4>
                    <div class="small mt-1 text-muted" style="font-size: 11px;">Across all digitized entries</div>
                </div>
                <div class="rounded-circle p-2 bg-light text-warning">
                    <i class="fas fa-tshirt fa-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Unique Lots -->
        <div class="col-xl-3 col-md-6 col-12 mb-1">
            <div class="erp-card p-2 mb-0 d-flex align-items-center justify-content-between" style="border-left: 4px solid #0284c7;">
                <div>
                    <span class="small text-muted font-weight-bold text-uppercase" style="font-size: 11px;">Distinct lots</span>
                    <h4 class="m-0 font-weight-bold text-dark" style="font-size: 18px;">{{ number_format($kpis['total_unique_lots']) }}</h4>
                    <div class="small mt-1 text-muted" style="font-size: 11px;">Production lots processed</div>
                </div>
                <div class="rounded-circle p-2 bg-light text-primary">
                    <i class="fas fa-layer-group fa-lg"></i>
                </div>
            </div>
        </div>

        <!-- Completion Status Breakdown -->
        <div class="col-xl-3 col-md-6 col-12 mb-1">
            <div class="erp-card p-2 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small font-weight-bold text-muted text-uppercase" style="font-size: 11px;">Digitization status</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small font-weight-bold text-success"><i class="fas fa-check-circle mr-1"></i> Digitized</span>
                    <span class="erp-badge-status erp-badge-status-closed">{{ $kpis['digitized_slips'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small font-weight-bold text-warning"><i class="fas fa-clock mr-1"></i> Pending</span>
                    <span class="erp-badge-status erp-badge-status-open">{{ $kpis['pending_slips'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-danger"><i class="fas fa-times-circle mr-1"></i> Skipped</span>
                    <span class="erp-badge-status erp-badge-status-delayed">{{ $kpis['skipped_slips'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.reports.slips') }}" class="mb-0">
            <div class="form-row align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Start date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm erp-filter-input" value="{{ request('start_date') }}">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">End date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm erp-filter-input" value="{{ request('end_date') }}">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Bill / slip no</label>
                    <input type="text" name="bill_number" class="form-control form-control-sm erp-filter-input" placeholder="e.g. 101 or #24" value="{{ request('bill_number') }}">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Lot number</label>
                    <input type="text" name="lot_no" class="form-control form-control-sm erp-filter-input" placeholder="Search lot..." value="{{ request('lot_no') }}">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Design number</label>
                    <select name="design_no" class="form-control select2 form-control-sm erp-filter-select">
                        <option value="">All designs</option>
                        @if(isset($designs))
                            @foreach($designs as $dsn)
                                <option value="{{ $dsn }}" {{ request('design_no') == $dsn ? 'selected' : '' }}>
                                    {{ $dsn }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From stage</label>
                    <select name="from_stage_id" class="form-control select2 form-control-sm erp-filter-select">
                        <option value="">All stages</option>
                        @foreach($stages as $stg)
                            <option value="{{ $stg->id }}" {{ request('from_stage_id') == $stg->id ? 'selected' : '' }}>
                                {{ $stg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To stage</label>
                    <select name="to_stage_id" class="form-control select2 form-control-sm erp-filter-select">
                        <option value="">All stages</option>
                        @foreach($stages as $stg)
                            <option value="{{ $stg->id }}" {{ request('to_stage_id') == $stg->id ? 'selected' : '' }}>
                                {{ $stg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From unit</label>
                    <select name="from_unit_id" class="form-control select2 form-control-sm erp-filter-select">
                        <option value="">All from units</option>
                        @foreach($units as $un)
                            <option value="{{ $un->id }}" {{ request('from_unit_id') == $un->id || request('stage_master_unit_id') == $un->id ? 'selected' : '' }}>
                                {{ $un->name }} ({{ $un->masterStage->name ?? 'Unit' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To unit</label>
                    <select name="to_unit_id" class="form-control select2 form-control-sm erp-filter-select">
                        <option value="">All to units</option>
                        @foreach($units as $un)
                            <option value="{{ $un->id }}" {{ request('to_unit_id') == $un->id ? 'selected' : '' }}>
                                {{ $un->name }} ({{ $un->masterStage->name ?? 'Unit' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Status</label>
                    <select name="status" class="form-control select2 form-control-sm erp-filter-select">
                        <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>All statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Digitized</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Skipped</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.reports.slips') }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                <i class="fas fa-list text-success mr-1"></i> Slip records 
                <span class="badge badge-light border ml-1 font-weight-normal text-muted">{{ $slips->total() }} slips</span>
            </h6>
            <div class="small text-muted">Showing {{ $slips->firstItem() ?? 0 }} - {{ $slips->lastItem() ?? 0 }} of {{ $slips->total() }}</div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 110px;">Slip ID / bill</th>
                        <th>Date</th>
                        <th>From stage & unit</th>
                        <th>To stage(s)</th>
                        <th>Lots involved</th>
                        <th>Designs</th>
                        <th class="text-center">Entries</th>
                        <th class="text-right">Total pieces</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slips as $slip)
                        @php
                            $computed = $slip->computed_data;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.slips.show', $slip->id) }}" class="font-weight-bold text-dark">
                                    #{{ $slip->id }}
                                </a>
                                @if($slip->bill_number)
                                    <div class="small text-muted">
                                        Bill: <strong>{{ $slip->bill_number }}</strong>
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $slip->created_at->format('d M Y') }}
                            </td>

                            <td>
                                <span class="badge badge-light border text-dark font-weight-bold">
                                    <i class="fas fa-layer-group text-success mr-1"></i>{{ $slip->fromStage->name ?? '-' }}
                                </span>
                                @if($slip->getUnitMaster)
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-user-tie text-muted mr-1"></i>{{ $slip->getUnitMaster->name }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if(!empty($computed['destinations']))
                                    @foreach($computed['destinations'] as $dest)
                                        <span class="badge badge-light border text-dark mb-1">
                                            <i class="fas fa-arrow-right text-success mr-1"></i>{{ $dest }}
                                        </span><br>
                                    @endforeach
                                @elseif($slip->toStage)
                                    <span class="badge badge-light border text-dark">
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

                            <td>
                                @if(!empty($computed['lots_with_qty']))
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                        @foreach($computed['lots_with_qty'] as $lot => $lqty)
                                            <span class="badge badge-light border mb-1 mr-1 px-2 py-1">
                                                #{{ $lot }} @if($lqty > 0) <span class="font-weight-bold text-success ml-1">({{ number_format($lqty) }})</span> @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                @if(!empty($computed['designs_with_qty']))
                                    <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 240px;">
                                        @foreach($computed['designs_with_qty'] as $dsn => $dqty)
                                            <span class="badge badge-light border mb-1 mr-1 px-2 py-1 font-weight-bold">
                                                {{ $dsn }} @if($dqty > 0) <span class="text-success ml-1">({{ number_format($dqty) }})</span> @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif(!empty($computed['designs']))
                                    <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 200px;">
                                        @foreach($computed['designs'] as $dsn)
                                            <span class="badge badge-light border mb-1 mr-1 px-2 py-1 font-weight-bold">
                                                {{ $dsn }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center text-nowrap">
                                <span class="badge badge-light border px-2 py-1">
                                    {{ $computed['entries_count'] }} {{ Str::plural('entry', $computed['entries_count']) }}
                                </span>
                            </td>

                            <td class="text-right text-nowrap font-weight-bold text-success">
                                {{ number_format($computed['total_quantity']) }} <small class="text-muted font-weight-normal">pcs</small>
                            </td>

                            <td class="text-center text-nowrap">
                                @if($slip->status == 1)
                                    <span class="erp-badge-status erp-badge-status-closed">
                                        <i class="fas fa-check-circle mr-1"></i>Digitized
                                    </span>
                                @elseif($slip->status == 0)
                                    <span class="erp-badge-status erp-badge-status-open">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                @else
                                    <span class="erp-badge-status erp-badge-status-delayed">
                                        <i class="fas fa-times-circle mr-1"></i>Skipped
                                    </span>
                                @endif
                            </td>

                            <td class="text-center text-nowrap">
                                @php
                                    $slipFile = $slip->slip_file ?? $slip->image ?? null;
                                    $slipUrl = null;
                                    $isPdf = false;
                                    if ($slipFile) {
                                        if (file_exists(public_path('assets/production_slips/' . $slipFile))) {
                                            $slipUrl = asset('assets/production_slips/' . $slipFile);
                                        } elseif (file_exists(public_path('storage/' . $slipFile))) {
                                            $slipUrl = asset('storage/' . $slipFile);
                                        } elseif (file_exists(storage_path('app/public/' . $slipFile))) {
                                            $slipUrl = asset('storage/' . $slipFile);
                                        } else {
                                            $slipUrl = asset('assets/production_slips/' . $slipFile);
                                        }
                                        $isPdf = strtolower(pathinfo($slipFile, PATHINFO_EXTENSION)) === 'pdf';
                                    }
                                @endphp
                                <a href="{{ route('admin.reports.slips.show', $slip->id) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2 mr-1" style="font-size: 11px;" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($slipUrl)
                                    <button type="button" class="btn-erp btn-erp-outline btn-sm py-0 px-2 mr-1" style="font-size: 11px;" data-toggle="modal" data-target="#slipImageModal{{ $slip->id }}" title="View physical slip photo">
                                        <i class="fas fa-image"></i>
                                    </button>
                                @endif
                                <a href="{{ route('admin.reports.slips.pdf', $slip->id) }}" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;" title="Download PDF report">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- Slip Image Modal -->
                                @if($slipUrl)
                                    <div class="modal fade text-left" id="slipImageModal{{ $slip->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius: 8px;">
                                                <div class="modal-header py-2 bg-light">
                                                    <h6 class="modal-title font-weight-bold mb-0" style="font-size: 13px;">
                                                        <i class="fas fa-file-image mr-1 text-success"></i> Physical slip #{{ $slip->id }}
                                                        @if($slip->bill_number)
                                                            <span class="badge badge-light border ml-1 font-weight-normal">Bill: {{ $slip->bill_number }}</span>
                                                        @endif
                                                    </h6>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body text-center p-2 bg-light">
                                                    @if($isPdf)
                                                        <iframe src="{{ $slipUrl }}" style="width: 100%; height: 75vh; border: none;" class="rounded"></iframe>
                                                    @else
                                                        <img src="{{ $slipUrl }}" class="img-fluid rounded shadow-sm" style="max-height: 75vh; max-width: 100%; object-fit: contain; cursor: zoom-in;" onclick="window.open('{{ $slipUrl }}', '_blank')" alt="Slip #{{ $slip->id }}" title="Click to view full size">
                                                    @endif
                                                </div>
                                                <div class="modal-footer py-2 justify-content-between bg-white">
                                                    <div class="small text-muted font-weight-bold">
                                                        Uploaded: {{ $slip->created_at->format('d M Y, h:i A') }}
                                                    </div>
                                                    <div>
                                                        <a href="{{ $slipUrl }}" target="_blank" class="btn-erp btn-erp-primary btn-sm mr-1">
                                                            <i class="fas fa-external-link-alt mr-1"></i> Open original
                                                        </a>
                                                        <a href="{{ $slipUrl }}" download class="btn-erp btn-erp-outline btn-sm mr-1">
                                                            <i class="fas fa-download mr-1"></i> Download
                                                        </a>
                                                        <button type="button" class="btn-erp btn-erp-outline btn-sm" data-dismiss="modal">Close</button>
                                                    </div>
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

        @if($slips->hasPages())
            <div class="p-2 border-top bg-light">
                {{ $slips->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
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
