@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

        {{-- HEADER BAR --}}
        <div class="erp-header-bar mb-3">
            <div>
                <h1 class="erp-header-title">Stage Wise Pending Stock</h1>
            </div>
            <div class="erp-report-meta">
                Date : {{ now()->format('d M Y h:i A') }}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- FILTERS --}}
        <div class="erp-card mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.reports.stock-pending') }}">
                    <div class="row align-items-end">

                        <div class="col-md-3">
                            <label class="erp-form-label">Stage</label>
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
                            <label class="erp-form-label">Unit Person</label>
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
                            <label class="erp-form-label">Lot Number</label>
                            <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control"
                                placeholder="Search Lot Number">
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex" style="gap: 8px;">
                                <button type="submit" class="btn-erp btn-erp-primary">
                                    <i class="fas fa-search mr-1"></i> Search
                                </button>
                                <a href="{{ route('admin.reports.stock-pending') }}" class="btn-erp btn-erp-secondary">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                            <div class="d-flex" style="gap: 8px;">
                                <a href="{{ route('admin.reports.stock-pending.export', request()->except('is_pagination')) }}" class="btn-erp btn-erp-success" title="Export Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                                </a>
                                <a href="{{ route('admin.reports.stock-pending.pdf', request()->except('is_pagination')) }}" class="btn-erp btn-erp-danger" title="Export PDF">
                                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="erp-card">
            <div class="card-body p-0">
                <div class="d-flex justify-content-end align-items-center p-3 border-bottom">
                    <span class="erp-stat-pill">Grand Total: {{ number_format($totalPending) }} Pcs</span>
                </div>
                <div class="table-responsive">

                    @if($assignments->isEmpty())
                        <div class="text-center p-4">
                            <h5 class="text-muted mb-0">No records found</h5>
                        </div>
                    @else

                        <table class="erp-table mb-0">
                            <thead>
                                <tr>
                                    <th>Order Type</th>
                                    <th>Stage</th>
                                    <th>Unit Person Name</th>
                                    <th>Lot Number</th>
                                    <th>Design Number</th>
                                    <th>Size Set</th>
                                    <th class="text-right">Pending Quantity</th>
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