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
                                    <select name="stage_id" id="stage_id" class="form-control select2"
                                        onchange="this.form.submit()">
                                        <option value="">All Stages</option>
                                        @foreach($stages as $stage)
                                            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
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
                                        <a href="{{ route('admin.reports.stock-pending.export', request()->query()) }}" class="btn btn-success" title="Export Excel">
                                            <i class="fas fa-file-excel mr-1"></i>Export Excel
                                        </a>
                                        <a href="{{ route('admin.reports.stock-pending.pdf', request()->query()) }}" class="btn btn-danger" title="Export PDF">
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
                        <div class="table-responsive">

                            @if($assignments->isEmpty())
                                <div class="text-center p-4">
                                    <h4 class="text-muted">No Data Found</h4>
                                </div>
                            @else

                                <table class="table table-bordered table-report">
                                    <thead>
                                        <tr>
                                            <th>Stage</th>
                                            <th>Lot No</th>
                                            <th>Design No</th>
                                            <th>Size Set</th>
                                            <th>Pending Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                            $totalPending = 0;
                                        @endphp
                                        @foreach($assignments as $item)
                                            @php 
                                                $qty = $item->pending_qty ?? $item->quantity ?? 0;
                                                $totalPending += $qty;
                                                $stageName = $item->to_stage->name ?? $item->from_stage->name ?? 'Cutting';
                                                $lotNo = $item->lot_no ?? ($item->productSet->lot_no ?? '-');
                                                if($type == 'cutting') {
                                                    $stageName = 'Cutting';
                                                    $lotNo = $item->lot_no ?? '-';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $stageName }}</td>
                                                <td>
                                                    {{ $lotNo }}
                                                </td>
                                                <td>{{ $item->design_number ?? '-' }}</td>
                                                <td>{{ $item->size_set_name ?? '-' }}</td>
                                                <td>{{ number_format($qty) }} Pcs</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="4" class="text-right">Grand Total:</td>
                                            <td>{{ number_format($totalPending) }} Pcs</td>
                                        </tr>
                                    </tfoot>
                                </table>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection