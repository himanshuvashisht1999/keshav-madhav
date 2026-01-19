@extends('admin.layouts.app')

@section('content')
<style>
/* ===== HEADER ===== */
.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

/* ===== CARD ===== */
.report-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    background: #fff;
}

/* ===== SECTION ===== */
.section-title {
    font-size: 14px;
    font-weight: 600;
    margin: 8px 0 6px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 3px;
}

/* ===== ORDER DETAILS GRID ===== */
.meta-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px 16px;
    font-size: 13px;
}

.meta-label {
    font-weight: 600;
    color: #333;
}

/* ===== TABLE COMMON ===== */
.report-table {
    width: 100%;
    table-layout: fixed;
    margin-bottom: 4px;
}

.report-table th,
.report-table td {
    padding: 6px 8px;
    font-size: 13px;
}

.report-table th {
    background: #2f363d;
    color: #fff;
}

.col-label {
    width: 65%;
}

.col-value {
    width: 35%;
    text-align: right;
}

/* ===== TOTAL ROW ===== */
.total-row td {
    background: #f1f3f5;
    font-weight: 600;
}

/* ===== STAGE DETAILS ===== */
.stage-card {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #ffffff;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    overflow: hidden;
}

.stage-header {
    display: flex;
    justify-content: space-between; /* LEFT & RIGHT */
    align-items: center;
    background: #f1f3f5;
    padding: 6px 10px;
    border-bottom: 1px solid #dee2e6;
}

.stage-title {
    font-size: 15px;
    font-weight: 600;
    color: #2f363d;
}

.stage-body {
    padding: 8px 10px;
    font-size: 14px;
}

.stage-row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    border-bottom: 1px dashed #e9ecef;
}

.stage-row:last-child {
    border-bottom: none;
}

.stage-row .label {
    color: #555;
    font-weight: 500;
}

.stage-row .value {
    font-weight: 600;
    color: #000;
}

.stage-row .value.strong {
    color: #2f363d;
}

.stage-row.remaining .value {
    color: #c0392b; /* remaining qty highlight */
}

</style>

<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>Report No :</div>
                <h5 class="mb-0">Lots Details Report</h5>
                <div>{{ now()->format('d M Y h:i A') }}</div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="report-card p-3">

                {{-- LOT NO --}}
                <strong>Lot No :</strong>
                {{ $data['lots_data'][0]->lot_no ?? '' }}

                {{-- ORDER DETAILS --}}
                <div class="section-title">Order Details</div>

                @foreach ($data['lots_data'] as $lot)
                    <div class="meta-grid mb-2">
                        <div><span class="meta-label">Order:</span> {{ $lot->orderProductSet->orderMain->sku ?? '' }}</div>
                        <div><span class="meta-label">Customer:</span> {{ $lot->orderProductSet->orderMain->customer->name ?? '' }}</div>
                        <div><span class="meta-label">Size:</span> {{ $lot->orderProductSet->size_measurement->size_group ?? '' }}</div>

                        <div><span class="meta-label">Pcs:</span> {{ $lot->orderProductSet->size_measurement->no_of_pcs ?? '' }}</div>
                        <div><span class="meta-label">Color:</span> {{ $lot->orderProductSet->colors->name ?? '' }}</div>
                        <div><span class="meta-label">Fit:</span> {{ $lot->orderProductSet->master_product_fitting->name ?? '' }}</div>

                        <div><span class="meta-label">Pattern:</span> {{ $lot->orderProductSet->master_design_pattern->name ?? '' }}</div>
                        <div><span class="meta-label">Fabric:</span> {{ $lot->orderProductSet->fabric->name ?? '' }}</div>
                        <div><span class="meta-label">Unit:</span> {{ $lot->productionSlipDigitization->getUnitMaster->name ?? '' }}</div>
                    </div>
                @endforeach

                {{-- CUTTING & ROLLS (SAME ROW) --}}
                <div class="row mt-2">

                    {{-- CUTTING --}}
                    <div class="col-md-6">
                        <div class="section-title">Cutting</div>

                        <table class="table table-bordered table-sm report-table">
                            <thead>
                                <tr>
                                    <th class="col-label">Size</th>
                                    <th class="col-value">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total_qty = 0; @endphp

                                @foreach ($data['rolls_data'] as $roll)
                                    @if (!empty($roll->fabricRollAssigningsDetail))
                                        @foreach ($roll->fabricRollAssigningsDetail as $detail)
                                            <tr>
                                                <td>{{ $detail->size ?? '' }}</td>
                                                <td class="col-value">{{ $detail->quantity ?? 0 }}</td>
                                            </tr>
                                            @php $total_qty += $detail->quantity ?? 0; @endphp
                                        @endforeach
                                    @endif
                                @endforeach

                                <tr class="total-row">
                                    <td>Total</td>
                                    <td class="col-value">{{ $total_qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- ROLLS --}}
                    <div class="col-md-6">
                        <div class="section-title">Rolls</div>

                        <table class="table table-bordered table-sm report-table">
                            <thead>
                                <tr>
                                    <th class="col-label">Roll</th>
                                    <th class="col-value">Meter</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['rolls_data'] as $roll)
                                    <tr>
                                        <td>{{ $roll->roll_no ?? '' }}</td>
                                        <td class="col-value">{{ $roll->meter ?? 0 }}</td>
                                    </tr>
                                @endforeach

                                <tr class="total-row">
                                    <td>Total</td>
                                    <td class="col-value">{{ $data['rolls_data']->sum('meter') ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                

                <div class="row mt-3 stage-wrapper">
                    @foreach($master_stages as $stage)
                        @php
                            $lot_details = getLotDetails($data['lot_no'], $stage->id);
                        @endphp

                        @if($lot_details)
                            <div class="col-md-6 mb-3">
                                <div class="stage-card">

                                    {{-- HEADER --}}
                                    <div class="stage-header">
                                        <span class="stage-title">{{ $stage->name }} Stage</span>
                                        <span class="stage-title">Estimated Time : {{ \Carbon\Carbon::parse($lot_details['time_allocation'])->format('d M Y, h:i A') ?? 'N/A' }}</span>
                                    </div>

                                    {{-- BODY --}}
                                    <div class="stage-body">
                                        <div class="stage-row">
                                            <span class="label">Unit</span>
                                            <span class="value">{{ $lot_details['unit_name'] ?? 'N/A' }}</span>
                                        </div>

                                        <div class="stage-row">
                                            <span class="label">Total Quantity</span>
                                            <span class="value strong">{{ $lot_details['quantity'] }}</span>
                                        </div>

                                        <div class="stage-row remaining">
                                            <span class="label">Remaining Quantity</span>
                                            <span class="value">{{ $lot_details['remaining_quantity'] }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>


            </div>
        </div>
    </section>
</div>

@endsection
