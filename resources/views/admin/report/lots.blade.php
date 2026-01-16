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

.fabric-cell {
    background: #f8f9fa;
    font-weight: 600;
    vertical-align: middle;
}

.expand-btn {
    font-size: 13px;
}
</style>

<div class="content-wrapper">

    {{-- HEADER --}}

    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>
                    <div class="report-meta">Report No : RJ 2</div>
                </div>
                <div>
                    <h3>Fabric Stock Report</h3>
                </div>
                <div class="report-meta">
                    Date : {{ now()->format('d M Y h:i A') }}
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            {{-- FILTERS --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ url()->current() }}">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="fw-bold">Order No</label>
                                <select name="order_id" id="order_no" class="form-control select2" onchange="changeOrderId(this.value)">
                                    <option value="">All</option>
                                    @foreach(collect($lotNos)->unique('order_id') as $row)
                                        <option value="{{ $row['order_id'] }}"
                                            {{ request('order_id') == $row['order_id'] ? 'selected' : '' }}>
                                            {{ $row['order_no'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">Lot No</label>
                                <select name="lot_no" id="lot_no" class="form-control select2">
                                    <option value="">All</option>
                                    @forelse($lotNos as $index => $row)
                                        <option value="{{ $row['lot_no'] }}" {{ request('lot_no') == $row['lot_no'] ? 'selected' : '' }}>
                                            {{ $row['lot_no'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{--<div class="col-md-4">
                                <label class="fw-bold">Order No</label>
                                <input type="text"
                                    name="order_no"
                                    value="{{ request('order_no') }}"
                                    class="form-control"
                                    placeholder="Search Order No">
                            </div> --}}

                            <div class="col-md-4 d-flex align-items-end gap-2">
                                <button class="btn btn-primary mr-2">
                                    Search
                                </button>

                                <a href="{{ url()->current() }}" class="btn btn-secondary">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="card report-card">
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-bordered table-report">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lot No</th>
                                    <th>Order No</th>
                                    <th>Customer Name</th>
                                    <th>Lot Quantity</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $index => $row)
                                    <tr>
                                        <td>{{ $data->firstItem() + $index }}</td>
                                        <td>{{ $row['lot_no'] }}</td>
                                        <td>{{ $row['order_no'] }}</td>
                                        <td>{{ $row['customer_name'] }}</td>
                                        <td class="text-end fw-bold">
                                            {{ $row['lot_quantity'] ?? '0' }}
                                        </td>
                                        <td class="text-center">
                        
                                            <a href="{{ route('admin.report.lots.lot-details', ['lot_no' => $row['lot_no']]) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No data found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>


                        </table>
                        <div class="mt-3">
                            {{ $data->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>


{{-- ================= SCRIPT ================= --}}

<script>
    
        const lotData = @json($lotNos);

        const orderSelect = $('#order_no');
        const lotSelect   = $('#lot_no');

        // helper: unique values
        function unique(arr) {
            return [...new Set(arr)];
        }

        // helper: refill lot dropdown
        function fillLotDropdown(lots) {
            lotSelect.empty().append(`<option value="">All</option>`);
            lots.forEach(lot => {
                lotSelect.append(`<option value="${lot}">${lot}</option>`);
            });
            lotSelect.trigger('change');
        }

        // On ORDER change
        function changeOrderId(selectedOrderId) {
            // If All selected → show ALL lots
            if (!selectedOrderId) {
                const allLots = unique(lotData.map(i => i.lot_no));
                fillLotDropdown(allLots);
                return;
            }

            //  FILTER USING order_id (NOT order_no)
            const filteredLots = lotData
                .filter(i => String(i.order_id) === String(selectedOrderId))
                .map(i => i.lot_no);

            fillLotDropdown(unique(filteredLots));
        };
</script>

@endsection