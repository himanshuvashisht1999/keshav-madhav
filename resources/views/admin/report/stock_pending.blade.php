@extends('admin.layouts.app')

@section('content')
    <style>
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .report-header h3 {
            font-weight: 600;
            margin: 0;
        }

        .report-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .table-report thead th {
            background: #343a40;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
        }
    </style>

    <div class="content-wrapper">

        {{-- HEADER --}}
        <section class="content-header">
            <div class="container-fluid">
                <div class="report-header">
                    <div>
                    </div>
                    <div>
                        <h3>Stage Wise Pending Stock</h3>
                    </div>
                    <div class="report-meta">
                        Date : {{ now()->format('d M Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- FILTERS --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.stock-pending') }}">
                            <div class="row">

                                <div class="col-md-3">
                                    <label class="fw-bold">Stage</label>
                                    <select name="stage_id" id="stage_id" class="form-control select2">
                                        <option value="">All Stages</option>
                                        @foreach($stages as $stage)
                                            @if(!in_array(strtolower(trim($stage->name)), ['cutting', 'printing & embroidery', 'printing', 'embroidery']))
                                            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="fw-bold">Unit Person</label>
                                    <select name="unit_id" id="unit_id" class="form-control select2">
                                        <option value="">All Unit Persons</option>
                                        @foreach($allUnits as $unit)
                                            <option value="{{ $unit->id }}" 
                                                data-stage-id="{{ $unit->master_stage_id }}"
                                                data-unit-name="{{ $unit->name }}"
                                                data-stage-name="{{ $unit->masterStage->name ?? '' }}"
                                                {{ (string)request('unit_id') === (string)$unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}{{ $unit->masterStage ? ' (' . $unit->masterStage->name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="fw-bold">Lot No</label>
                                    <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control"
                                        placeholder="Search Lot No">
                                </div>

                                <div class="col-md-12 mt-3 d-flex justify-content-between">
                                    <div class="d-flex gap-2" style="width: 300px;">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search mr-1"></i>Search
                                        </button>
                                        <a href="{{ route('admin.reports.stock-pending') }}" class="btn btn-secondary w-100">
                                            Reset
                                        </a>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.reports.stock-pending.export', request()->except('is_pagination')) }}" class="btn btn-success" title="Export Excel">
                                            <i class="fas fa-file-excel mr-1"></i>Export Excel
                                        </a>
                                        <a href="{{ route('admin.reports.stock-pending.pdf', request()->except('is_pagination')) }}" class="btn btn-danger" title="Export PDF">
                                            <i class="fas fa-file-pdf mr-1"></i>Export PDF
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="card report-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end align-items-center mb-3">
                            <h4 class="mb-0 text-primary font-weight-bold">Grand Total: {{ number_format($totalPending) }} Pcs</h4>
                        </div>
                        <div class="table-responsive">

                            @if($assignments->isEmpty())
                                <div class="text-center p-4">
                                    <h4 class="text-muted">No Data Found</h4>
                                </div>
                            @else

                                <table class="table table-bordered table-report">
                                    <thead>
                                        <tr>
                                            <th>Order Type</th>
                                            <th>Stage</th>
                                            <th>Unit Person Name</th>
                                            <th>Lot No</th>
                                            <th>Design No</th>
                                            <th>Size Set</th>
                                            <th>Pending Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        @include('admin.report.partials.stock_pending_rows')
                                    </tbody>
                                </table>
                                
                                <div id="scroll-sentry" style="height: 10px;"></div>

                                <div id="loading-spinner" class="text-center p-3" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="mt-2 text-muted small">Loading more records...</div>
                                </div>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Cache all unit options for instant client-side filtering
        let allUnitOptions = [];
        $('#unit_id option').each(function() {
            if ($(this).val()) {
                let unitName = $(this).data('unit-name') || $(this).text();
                let stageName = $(this).data('stage-name') || '';
                allUnitOptions.push({
                    id: $(this).val(),
                    unitName: unitName,
                    stageName: stageName,
                    fullText: unitName + (stageName ? ' (' + stageName + ')' : ''),
                    stageId: $(this).data('stage-id')
                });
            }
        });

        function filterUnits(stageId, preselectedUnitId) {
            let $unitSelect = $('#unit_id');
            let currentVal = preselectedUnitId !== undefined ? preselectedUnitId : $unitSelect.val();
            
            $unitSelect.empty();
            $unitSelect.append(new Option('All Unit Persons', ''));

            let hasSelected = false;
            allUnitOptions.forEach(function(opt) {
                if (!stageId || opt.stageId == stageId) {
                    let isSelected = (currentVal && currentVal == opt.id);
                    if (isSelected) hasSelected = true;
                    // If a specific stage is active, display just the unit person's name
                    let displayText = stageId ? opt.unitName : opt.fullText;
                    let newOpt = new Option(displayText, opt.id, false, isSelected);
                    $(newOpt).attr('data-stage-id', opt.stageId);
                    $(newOpt).attr('data-unit-name', opt.unitName);
                    $(newOpt).attr('data-stage-name', opt.stageName);
                    $unitSelect.append(newOpt);
                }
            });

            if (!hasSelected && currentVal) {
                $unitSelect.val('');
            }

            $unitSelect.trigger('change.select2');
        }

        $('#stage_id').on('change', function() {
            filterUnits($(this).val());
        });

        let initialStage = $('#stage_id').val();
        let initialUnit = "{{ request('unit_id') }}";
        if (initialStage) {
            filterUnits(initialStage, initialUnit);
        }

        let page = 1;
        let isLoading = false;
        let hasMore = {{ $assignments->hasMorePages() ? 'true' : 'false' }};

        // Use IntersectionObserver which works regardless of which container is scrolling
        let observer = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) {
                if (!isLoading && hasMore) {
                    loadMoreData();
                }
            }
        }, {
            rootMargin: '100px'
        });

        const sentry = document.getElementById('scroll-sentry');
        if (sentry) {
            observer.observe(sentry);
        }

        function loadMoreData() {
            isLoading = true;
            page++;
            $('#loading-spinner').show();
            
            let url = new URL(window.location.href);
            url.searchParams.set('page', page);

            $.ajax({
                url: url.href,
                type: 'GET',
                success: function(response) {
                    $('#loading-spinner').hide();
                    if (response.trim() === '') {
                        hasMore = false;
                    } else {
                        $('#table-body').append(response);
                    }
                    isLoading = false;
                },
                error: function(xhr) {
                    $('#loading-spinner').hide();
                    isLoading = false;
                }
            });
        }
    });
</script>
@endpush