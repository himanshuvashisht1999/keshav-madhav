@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- HEADER --}}

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">Production Slip Details</h2>
            <!-- <small class="text-muted">Read-only audit view</small> -->
        </div>

        <div>
            <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-outline-secondary">
                ← Back
            </a>
            <a href="{{ route('admin.uploaded-slips.download', $slip->id) }}"
            class="btn btn-outline-primary ms-2">
                ⬇ Download PDF
            </a>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- ================= SLIP SUMMARY ================= --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-muted small">Slip ID</div>
                            <div class="fw-bold">{{ $slip->id }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">From Stage</div>
                            <div class="fw-semibold">{{ $slip->fromStage?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Unit / Warehouse</div>
                            <div class="fw-semibold">
                                {{ $slip->getUnitMaster?->name }}
                                <div class="text-muted small">
                                    {{ $slip->getUnitMaster?->masterFabricWarehouse?->cutting_master_name }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ================= TYPE 1 : LOT / ROLLS ================= --}}
            @if($slip->save_type == 1 && $lot)

            <div class="mb-4">
                <h5 class="fw-bold mb-2 text-success">Lot & Design</h5>
                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <span class="text-muted small">Lot No</span>
                                <div class="fw-bold">{{ $lot->lot_no }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Production Date</span>
                                <div>{{ getformatDateTime($lot->production_datetime) }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Order No</span>
                                <div>{{ $lot->orderMain?->sku }}</div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-3"><strong>Design</strong><br>{{ $lot->orderProductSet?->design_number }}</div>
                            <div class="col-md-3"><strong>Fabric</strong><br>{{ $lot->orderProductSet?->fabric?->name }}</div>
                            <div class="col-md-3"><strong>Color</strong><br>{{ $lot->orderProductSet?->colors?->name }}</div>
                            <div class="col-md-3"><strong>Pattern</strong><br>{{ $lot->orderProductSet?->master_design_pattern?->name }}</div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3">
                                <strong>Fitting</strong><br>
                                {{ $lot->orderProductSet?->master_product_fitting?->name }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ROLLS --}}
            <div class="mb-4">
                <h5 class="fw-bold mb-2 text-info">Rolls Allocation</h5>

                @foreach($rolls as $roll)
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">

                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong>Roll:</strong> {{ $roll->roll_no }} |
                                    <strong>Meter:</strong> {{ $roll->meter }} m
                                </div>
                                <span class="badge badge-light">Status {{ $roll->status }}</span>
                            </div>

                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Size</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roll->fabricRollAssigningsDetail as $size)
                                        <tr>
                                            <td>{{ $size->size }}</td>
                                            <td>{{ $size->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- ================= TYPE 2 : PRINTING ================= --}}
            @if($slip->save_type == 2 && $printing)

            <div class="mb-4">
                <h5 class="fw-bold mb-2 text-primary">Printing Details</h5>
                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Lot:</strong> {{ $printing->lot_no }}</div>
                            <div class="col-md-4"><strong>Date:</strong> {{ getformatDateTime($printing->production_datetime) }}</div>
                            <div class="col-md-4"><strong>Total Qty:</strong> {{ $printing->quantity }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6"><strong>From:</strong> {{ $printing->from_stage?->name }}</div>
                            <div class="col-md-6"><strong>To:</strong> {{ $printing->to_stage?->name }}</div>
                        </div>

                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Size</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($printing_sizes as $row)
                                    <tr>
                                        <td>{{ $row->size }}</td>
                                        <td>{{ $row->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            @endif

            {{-- ================= TYPE 3 : OTHER ================= --}}
            @if($slip->save_type == 3 && isset($stage_transaction))

            <div class="mb-4">
                <h5 class="fw-bold mb-2 text-warning">Stage Movement</h5>
                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Lot:</strong> {{ $stage_transaction->lot_no }}</div>
                            <div class="col-md-4"><strong>Date:</strong> {{ getformatDateTime($stage_transaction->production_datetime) }}</div>
                            <div class="col-md-4"><strong>Total Qty:</strong> {{ $stage_transaction->quantity }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6"><strong>From:</strong> {{ $stage_transaction->from_stage?->name }}</div>
                            <div class="col-md-6"><strong>To:</strong> {{ $stage_transaction->to_stage?->name }}</div>
                        </div>

                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Size</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stage_sizes as $row)
                                    <tr>
                                        <td>{{ $row->size }}</td>
                                        <td>{{ $row->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            @endif

            {{-- ================= SLIP IMAGE (LAST) ================= --}}
            @if($slip->slip_file)
            <div class="card shadow-sm mt-1">
                <div class="card-header bg-light">
                    <strong>Original Slip Image</strong>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset('assets/production_slips/'.$slip->slip_file) }}"
                         class="img-fluid rounded border"
                         style="max-height: 700px;">
                </div>
            </div>
            @endif

        </div>
    </section>
</div>
@endsection
