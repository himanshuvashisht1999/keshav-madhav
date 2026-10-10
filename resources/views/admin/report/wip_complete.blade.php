@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">WIP Complete Report</h1>
        </div>
        <div class="erp-report-meta">
            Date : {{ now()->format('d M Y h:i A') }}
        </div>
    </div>

    {{-- FILTERS CARD --}}
    <div class="erp-card mb-3" id="filter-card">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center" style="cursor: pointer;" onclick="$('#filter-form-body').slideToggle();">
            <span class="font-weight-bold text-dark"><i class="fas fa-filter mr-1 text-muted"></i> Report Filters</span>
            <i class="fas fa-chevron-down text-muted small"></i>
        </div>
        <div class="card-body p-3" id="filter-form-body">
            <form action="{{ route('admin.report.wip-complete') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Customer</label>
                        <select name="customer_id" class="form-control select2" onchange="this.form.submit()">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $selectedCustomer == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedCustomer)
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Orders</label>
                            <div id="parent-orders" style="width: 100%;">
                                <select name="order_ids[]" class="form-control select2" multiple data-placeholder="All Orders" style="width: 100%;">
                                    @foreach($availableOrders as $id => $sku)
                                        <option value="{{ $id }}" {{ in_array($id, $selectedOrders) ? 'selected' : '' }}>{{ $sku }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Designs</label>
                            <div id="parent-designs" style="width: 100%;">
                                <select name="design_nos[]" class="form-control select2" multiple data-placeholder="All Designs" style="width: 100%;">
                                    @foreach($availableDesigns as $no => $no2)
                                        <option value="{{ $no }}" {{ in_array($no, $selectedDesigns) ? 'selected' : '' }}>{{ $no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Lots</label>
                            <div id="parent-lots" style="width: 100%;">
                                <select name="lot_nos[]" class="form-control select2" multiple data-placeholder="All Lots" style="width: 100%;">
                                    @foreach($availableLots as $no => $no2)
                                        <option value="{{ $no }}" {{ in_array($no, $selectedLots) ? 'selected' : '' }}>{{ $no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 mb-2 d-flex align-items-center" style="gap: 8px;">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                            
                            @if(!empty($data))
                                @php
                                    $exportParams = request()->all();
                                    $exportParams['export'] = 1;
                                @endphp
                                <a href="{{ route('admin.report.wip-complete', $exportParams) }}" class="btn-erp btn-erp-success" title="Export Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Export
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($selectedCustomer)
        <div class="erp-card">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-dark">Customer Data (Orders, Designs, Lots)</span>
                <span class="erp-stat-pill">Total Lots: <strong>{{ count($data ?? []) }}</strong></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                    @if(empty($data))
                        <div class="p-4 text-center text-muted">
                            <h5 class="mb-0 font-weight-normal">No lots found for this customer</h5>
                        </div>
                    @else
                        <table class="erp-table mb-0">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Order Date</th>
                                    <th>Design Number</th>
                                    <th>Lot Number</th>
                                    <th class="text-right">Lot Quantity</th>
                                    <th class="text-center">Lot Status</th>
                                    <th>Current Stage</th>
                                    @foreach($master_stages as $stage)
                                        <th>{{ $stage->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $row)
                                    <tr>
                                        <td>{{ $row['order']->sku ?? '-' }}</td>
                                        <td>{{ $row['order']->created_at ? $row['order']->created_at->format('d M, Y') : '-' }}</td>
                                        <td>{{ $row['set']->design_number ?? '-' }}</td>
                                        <td>
                                            <strong>{{ $row['lot']->lot_no }}</strong>
                                            <a href="{{ route('admin.report.lots.lot-details', $row['lot']->lot_no) }}" target="_blank" class="erp-action-btn erp-btn-view" title="View Lot Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                        <td class="text-right font-weight-bold">{{ number_format($row['lot_quantity'] ?? 0) }}</td>
                                        <td class="text-center">
                                            @if($row['lot']->status == 0) <span class="erp-badge-status erp-badge-status-warning">Pending</span>
                                            @elseif($row['lot']->status == 1) <span class="erp-badge-status erp-badge-status-info">Processing</span>
                                            @elseif($row['lot']->status == 2) <span class="erp-badge-status erp-badge-status-success">Completed</span>
                                            @else <span class="erp-badge-status erp-badge-status-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>{{ getLastCurrentStage($row['lot']->lot_no) }}</td>

                                        <!-- STAGE WISE STATUS -->
                                        @foreach($master_stages as $stage)
                                            @php
                                                $d = getLotDetails($row['lot']->lot_no, $stage->id);
                                            @endphp
                                            <td style="font-size: 11px; min-width: 160px; vertical-align: top;">
                                                @if($d && $d['time_allocation'])
                                                    <div style="font-weight: 600; color: #111827; margin-bottom: 2px;">
                                                        In: <span style="color: var(--brand-dark-green, #05421c); font-weight: 700;">{{ $d['quantity'] }}</span> | 
                                                        Out: <span style="color: #16a34a; font-weight: 700;">{{ $d['quantity'] - $d['remaining_quantity'] }}</span>
                                                    </div>
                                                    <div style="font-size: 10px; color: #d97706; font-weight: 600; margin-bottom: 4px;">WIP: {{ $d['remaining_quantity'] }}</div>
                                                    <div style="color: #64748b; font-size: 10px;">Unit: <span style="font-weight: 600; color: #374151;">{{ $d['unit_name'] ?? '-' }}</span></div>
                                                    <div style="color: #64748b; font-size: 10px;">Start: {{ $d['start_date'] ? \Carbon\Carbon::parse($d['start_date'])->format('d M y') : '-' }}</div>
                                                    @php
                                                        $rem = (int)$d['remaining_quantity'];
                                                        $compDate = $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time']) : null;
                                                    @endphp
                                                    <div style="color: #64748b; font-size: 10px;">Comp: {{ ($rem == 0 && $compDate) ? $compDate->format('d M y') : '-' }}</div>
                                                @else
                                                    <span style="color: #9ca3af;">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                allowClear: true,
                width: '100%'
            });
            
            // Fix focus jump bug by destroying generic select2 and re-initializing with dropdownParent
            let configs = [
                { selector: 'select[name="order_ids[]"]', parent: '#parent-orders' },
                { selector: 'select[name="design_nos[]"]', parent: '#parent-designs' },
                { selector: 'select[name="lot_nos[]"]', parent: '#parent-lots' }
            ];
            
            configs.forEach(function(item) {
                let $el = $(item.selector);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    dropdownParent: $(item.parent),
                    width: '100%',
                    allowClear: true,
                    theme: 'bootstrap4'
                }).on('select2:open', function() {
                    // Force focus back to this specific search field after DOM insertion
                    setTimeout(function() {
                        let searchField = $(item.parent).find('.select2-search__field');
                        if (searchField.length) {
                            searchField.focus();
                        }
                    }, 50);
                });
            });
        }

        // Auto-hide filters on table scroll down
        let isScrollingDown = false;
        $('.auto-scroll-table').on('scroll', function() {
            let scrollTop = $(this).scrollTop();
            if (scrollTop > 80 && !isScrollingDown) {
                $('#filter-form-body').slideUp(200);
                isScrollingDown = true;
            } else if (scrollTop < 20 && isScrollingDown) {
                $('#filter-form-body').slideDown(200);
                isScrollingDown = false;
            }
        });
    });
</script>
@endsection
