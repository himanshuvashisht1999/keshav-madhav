@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-layer-group"></i> Lot wise report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-hashtag text-muted"></i> Report no: RJ 4</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> Generated: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <button type="submit" form="delete-multiple-form" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete the selected lots? A lot whose pieces have already gone to the next stage will not be deleted.')">
                <i class="fas fa-trash mr-1"></i> Delete selected
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ url()->current() }}" class="mb-0">
            <div class="form-row align-items-end">
                <div class="col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Order no</label>
                    <select name="order_id" id="order_no" class="form-control form-control-sm select2 erp-filter-select"
                        onchange="changeOrderId(this.value)">
                        <option value="">All orders</option>
                        @foreach(collect($lotNos)->unique('order_id') as $row)
                            <option value="{{ $row['order_id'] }}" {{ request('order_id') == $row['order_id'] ? 'selected' : '' }}>
                                {{ $row['order_no'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Lot no</label>
                    <select name="lot_no[]" id="lot_no" class="form-control form-control-sm select2 erp-filter-select" multiple data-placeholder="Select lot(s)">
                        @php
                            $selectedLots = (array) request('lot_no', []);
                        @endphp
                        @forelse($lotNos as $index => $row)
                        <option value="{{ $row['lot_no'] }}" {{ in_array($row['lot_no'], $selectedLots) ? 'selected' : '' }}>
                            {{ $row['lot_no'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-12 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ url()->current() }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <form id="delete-multiple-form" action="{{ route('admin.report.lots.delete-multiple') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-sm table-hover erp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Lot no</th>
                            <th>Order no</th>
                            <th>Customer name</th>
                            <th>Current stage</th>
                            <th class="text-right">Lot quantity</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $index => $row)
                            @isset($row['lot_no'])
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="lot_ids[]" value="{{ $row['id'] ?? '' }}" class="lot-checkbox">
                                    </td>
                                    <td class="text-center text-muted">{{ $data->firstItem() + $index }}</td>
                                    <td><strong>{{ $row['lot_no'] }}</strong></td>
                                    <td><code>{{ $row['order_no'] }}</code></td>
                                    <td>{{ $row['customer_name'] }}</td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-weight-bold px-2">
                                            <i class="fas fa-layer-group text-success mr-1"></i>{{ $row['last_current_stage'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        {{ number_format($row['lot_quantity'] ?? 0) }} pcs
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('admin.report.lots.lot-details', ['lot_no' => $row['lot_no']]) }}"
                                            class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endisset
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No data found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if($data->hasPages())
            <div class="p-2 border-top bg-light">
                {{ $data->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    const lotData = @json($lotNos);
    const orderSelect = $('#order_no');
    const lotSelect = $('#lot_no');

    function unique(arr) {
        return [...new Set(arr)];
    }

    function fillLotDropdown(lots) {
        lotSelect.empty();
        lots.forEach(lot => {
            lotSelect.append(`<option value="${lot}">${lot}</option>`);
        });
        lotSelect.trigger('change');
    }

    function changeOrderId(selectedOrderId) {
        if (!selectedOrderId) {
            const allLots = unique(lotData.map(i => i.lot_no));
            fillLotDropdown(allLots);
            return;
        }

        const filteredLots = lotData
            .filter(i => String(i.order_id) === String(selectedOrderId))
            .map(i => i.lot_no);

        fillLotDropdown(unique(filteredLots));
    }

    $(document).ready(function() {
        $('#selectAll').on('click', function() {
            $('.lot-checkbox').prop('checked', this.checked);
        });

        $('.lot-checkbox').on('change', function() {
            if ($('.lot-checkbox:checked').length === $('.lot-checkbox').length) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
        });
    });
</script>
@endsection