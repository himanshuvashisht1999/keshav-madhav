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
                        {{-- <div class="report-meta">Report No : UA-1</div> --}}
                    </div>
                    <div>
                        <h3>Unit Assignments Report</h3>
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
                        <form method="GET" action="{{ route('admin.reports.unit-assignments') }}">
                            <div class="row">

                                <div class="col-md-2">
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

                                <div class="col-md-2">
                                    <label class="fw-bold">Unit Person</label>
                                    <select name="unit_id[]" id="unit_id" class="form-control" multiple>
                                        @php 
                                            $reqUnits = request('unit_id', []); 
                                            if(!is_array($reqUnits)) $reqUnits = [$reqUnits]; 
                                        @endphp
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ in_array($unit->id, $reqUnits) ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Status View</label>
                                    <select name="view" class="form-control select2" onchange="this.form.submit()">
                                        <option value="all" {{ request('view', 'open') == 'all' ? 'selected' : '' }}>All Tasks</option>
                                        <option value="open" {{ request('view', 'open') == 'open' ? 'selected' : '' }}>Pending Tasks</option>
                                        <option value="closed" {{ request('view', 'open') == 'closed' ? 'selected' : '' }}>Done Tasks</option>
                                        <option value="delayed" {{ request('view', 'open') == 'delayed' ? 'selected' : '' }}>Delayed Tasks</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Order No</label>
                                    <input type="text" name="order_no" value="{{ request('order_no') }}" class="form-control"
                                        placeholder="Search Order No">
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Lot No</label>
                                    <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control"
                                        placeholder="Search Lot No">
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Design No</label>
                                    <select name="design_no[]" id="design_no" class="form-control" multiple>
                                        @php 
                                            $reqDesigns = request('design_no', []); 
                                            if(!is_array($reqDesigns)) $reqDesigns = [$reqDesigns]; 
                                        @endphp
                                        @foreach($designs as $design)
                                            <option value="{{ $design }}" {{ in_array($design, $reqDesigns) ? 'selected' : '' }}>
                                                {{ $design }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Start Date</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">End Date</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-bold">Production Status</label>
                                    <select name="production_status" class="form-control select2" onchange="this.form.submit()">
                                        <option value="">Select</option>
                                        <option value="not_printing" {{ request('production_status') == 'not_printing' ? 'selected' : '' }}>Not sent to Printing</option>
                                        <option value="not_stitching" {{ request('production_status') == 'not_stitching' ? 'selected' : '' }}>Not sent to Stitching</option>
                                        <option value="not_both" {{ request('production_status') == 'not_both' ? 'selected' : '' }}>Both not sent</option>
                                        <option value="printing_sent_stitching_pending" {{ request('production_status') == 'printing_sent_stitching_pending' ? 'selected' : '' }}>Printing Sent But Stiching Pending</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="fw-bold">Pending Qty Range</label>
                                    <div class="input-group">
                                        <input type="number" step="any" name="min_pending_qty" value="{{ request()->has('min_pending_qty') ? request('min_pending_qty') : ($min_pending_qty ?? 1) }}" class="form-control" placeholder="Min">
                                        <span class="input-group-text bg-light border-x-0">-</span>
                                        <input type="number" step="any" name="max_pending_qty" value="{{ request('max_pending_qty') }}" class="form-control" placeholder="Max">
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3 d-flex justify-content-between">
                                    <div class="d-flex gap-2" style="width: 300px;">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search mr-1"></i>Search
                                        </button>
                                        <a href="{{ route('admin.reports.unit-assignments') }}" class="btn btn-secondary w-100">
                                            Reset
                                        </a>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.reports.unit-assignments.export', request()->query()) }}" class="btn btn-success" title="Export Excel">
                                            <i class="fas fa-file-excel mr-1"></i>Export Excel
                                        </a>
                                        <a href="{{ route('admin.reports.unit-assignments.pdf', request()->query()) }}" class="btn btn-danger" title="Export PDF">
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
                                    <h4 class="text-muted">No Assignments Found</h4>
                                </div>
                            @else

                                @if($type == 'cutting')
                                    <table class="table table-bordered table-report">
                                        <thead>
                                            <tr>
                                                <!-- <th>Date</th> -->
                                                <th>Unit Person</th>
                                                <th>Order No</th>
                                                <th>Design No</th>
                                                {{-- <th>Fabric</th>
                                                <th>Color</th> --}}
                                                <th>Assigned Qty</th>
                                                <th>Receive Qty</th>
                                                <th>Pending Qty</th>
                                                <th>Start Date</th>
                                                <th>Completed Date</th>
                                                <th>Estimated Date</th>
                                                <th>Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php 
                                                $totalAssigned = 0;
                                                $totalReceived = 0;
                                                $totalPending = 0;
                                            @endphp
                                            @foreach($assignments as $item)
                                                @php 
                                                    $assignedVal = $item->assigned_qty ?? 0;
                                                    $pendingVal = $item->pending_qty ?? 0;
                                                    $receivedVal = max(0, $assignedVal - $pendingVal);
                                                    $totalAssigned += $assignedVal;
                                                    $totalReceived += $receivedVal;
                                                    $totalPending += $pendingVal;
                                                @endphp
                                                <tr>
                                                    <!-- <td>{{ $item->created_at->format('d M Y') }}</td> -->
                                                    <td>
                                                        {{ $item->stage_master_unit->name ?? '-' }}
                                                        @if(isset($item->stage_master_unit->phone) && $item->stage_master_unit->phone)
                                                            <a href="https://wa.me/{{ $item->stage_master_unit->phone }}" target="_blank" class="ml-1 text-success" title="WhatsApp">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->orderMain->sku ?? '-' }}</td>
                                                    <td>{{ $item->design_number ?? '-' }}</td>
                                                    {{-- <td>{{ $item->fabric->name ?? '-' }}</td>
                                                    <td>{{ $item->colors->name ?? '-' }}</td> --}}
                                                    <td>{{ $assignedVal }} Pcs</td>
                                                    <td>{{ $receivedVal }} Pcs</td>
                                                    <td>{{ $pendingVal }} Pcs</td>
                                                    <td>{{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</td>
                                                    <td>{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $item->status_class ?? 'secondary' }}">
                                                            {{ $item->status_text ?? 'Unknown' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if(request('stage_id') == 3 && empty(request('production_status')))
                                                            {{-- <a href="#" class="btn btn-sm btn-info" title="View Lot"><i class="fas fa-eye"></i> View</a> --}}
                                                        @elseif(!empty($item->lot_no))
                                                            <a href="{{ route('admin.report.lots.lot-details', $item->lot_no) }}" class="btn btn-sm btn-info" title="View Lot">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        @endif
                                                        @if($canCloseTasks)
                                                            <!-- <form method="POST"
                                                                action="{{ $item->is_closed_for_unit == 1 ? route('admin.reports.unit-assignments.reopen', ['type' => 'cutting', 'id' => $item->id] + request()->query()) : route('admin.reports.unit-assignments.close', ['type' => 'cutting', 'id' => $item->id] + request()->query()) }}"
                                                                class="d-inline ml-1">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-sm {{ $item->is_closed_for_unit == 1 ? 'btn-secondary' : 'btn-outline-danger' }}"
                                                                    onclick="return confirm('{{ $item->is_closed_for_unit == 1 ? "Are you sure you want to Re-open?" : "Are you sure you want to Close?" }}')">
                                                                    <i
                                                                        class="fas {{ $item->is_closed_for_unit == 1 ? 'fa-undo' : 'fa-times' }}"></i>
                                                                    {{ $item->is_closed_for_unit == 1 ? 'Re-open' : 'Close' }}
                                                                </button>
                                                            </form> -->
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="3" class="text-right">Grand Total:</td>
                                                <td>{{ number_format($totalAssigned) }} Pcs</td>
                                                <td>{{ number_format($totalReceived) }} Pcs</td>
                                                <td>{{ number_format($totalPending) }} Pcs</td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @else
                                    <table class="table table-bordered table-report">
                                        <thead>
                                            <tr>
                                                <!-- <th>Date</th> -->
                                                <th>Unit Person</th>
                                                @if(!$productionStatus)
                                                    <th>From Stage</th>
                                                    <th>Current Stage</th>
                                                @endif
                                                <th>Lot No</th>
                                                <th>Design No</th>
                                                @if(!$productionStatus)
                                                    <th>Sent By</th>
                                                @endif
                                                @if($productionStatus)
                                                    <th>Total Quantity</th>
                                                @else
                                                    <th>Assigned Qty</th>
                                                    <th>Receive Qty</th>
                                                    <th>Pending Qty</th>
                                                @endif
                                                @if($productionStatus)
                                                    <th>Lot Date</th>
                                                @else
                                                    <th>Start Date</th>
                                                    <th>Completed Date</th>
                                                    <th>Estimated Date</th>
                                                @endif
                                                <th>Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php 
                                                $totalAssignedOther = 0;
                                                $totalReceivedOther = 0;
                                                $totalPendingOther = 0;
                                            @endphp
                                            @foreach($assignments as $item)
                                                @php 
                                                    $assignedValOther = $item->assigned_qty ?? 0;
                                                    $pendingValOther = $item->pending_qty ?? 0;
                                                    $receivedValOther = max(0, $assignedValOther - $pendingValOther);
                                                    $totalAssignedOther += $assignedValOther;
                                                    $totalReceivedOther += $receivedValOther;
                                                    $totalPendingOther += $pendingValOther;
                                                @endphp
                                                <tr>
                                                    <!-- <td>{{ $item->created_at->format('d M Y') }}</td> -->
                                                    <td>
                                                        {{ $item->getToUnitMaster->name ?? $item->stage_master_unit->name ?? '-' }}
                                                        @php $phone = $item->getToUnitMaster->phone ?? $item->stage_master_unit->phone ?? null; @endphp
                                                        @if($phone)
                                                            <a href="https://wa.me/{{ $phone }}" target="_blank" class="ml-1 text-success" title="WhatsApp">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    @if(!$productionStatus)
                                                        <td>{{ $item->from_stage->name ?? $item->fromStage->name ?? '-' }}</td>
                                                        <td>{{ $item->to_stage_name ?? $item->to_stage->name ?? $item->toStage->name ?? '-' }}</td>
                                                    @endif
                                                    <td>{{ $item->lot_no ?? 'Pending' }}</td>
                                                    <td>{{ $item->design_number ?? '-' }}</td>
                                                    @if(!$productionStatus)
                                                        <td>{{ $item->getFromUnitMaster->name ?? $item->getUnitMaster->name ?? '-' }}</td>
                                                    @endif
                                                    @if($productionStatus)
                                                        <td>{{ $assignedValOther }} Pcs</td>
                                                    @else
                                                        <td>{{ $assignedValOther }} Pcs</td>
                                                        <td>{{ $receivedValOther }} Pcs</td>
                                                        <td>{{ $pendingValOther }} Pcs</td>
                                                    @endif
                                                    @if($productionStatus)
                                                        <td>{{ $item->production_date ?? '-' }}</td>
                                                    @else
                                                        <td>{{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}</td>
                                                        <td>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</td>
                                                        <td>{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</td>
                                                    @endif
                                                    <td>
                                                        <span class="badge badge-{{ $item->status_class ?? 'secondary' }}">
                                                            {{ $item->status_text ?? 'Unknown' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if(request('stage_id') == 3 && empty(request('production_status')))
                                                            {{-- <a href="#" class="btn btn-sm btn-info" title="View Lot"><i class="fas fa-eye"></i> View</a> --}}
                                                        @elseif(!empty($item->lot_no))
                                                            <a href="{{ route('admin.report.lots.lot-details', $item->lot_no) }}" class="btn btn-sm btn-info" title="View Lot">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        @endif
                                                        @if($canCloseTasks)
                                                            <form method="POST"
                                                                 action="{{ $item->is_closed_for_unit == 1 ? route('admin.reports.unit-assignments.reopen', ['type' => $item->transaction_type ?? 'production', 'id' => $item->id] + request()->query()) : route('admin.reports.unit-assignments.close', ['type' => $item->transaction_type ?? 'production', 'id' => $item->id] + request()->query()) }}"
                                                                 class="d-inline ml-1">
                                                                 @csrf
                                                                 <button type="submit"
                                                                     class="btn btn-sm {{ $item->is_closed_for_unit == 1 ? 'btn-secondary' : 'btn-outline-danger' }}"
                                                                     onclick="return confirm('{{ $item->is_closed_for_unit == 1 ? "Are you sure you want to Re-open?" : "Are you sure you want to Close?" }}')">
                                                                     <i
                                                                         class="fas {{ $item->is_closed_for_unit == 1 ? 'fa-undo' : 'fa-times' }}"></i>
                                                                     {{ $item->is_closed_for_unit == 1 ? 'Re-open' : 'Close' }}
                                                                 </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="{{ $productionStatus ? 3 : 6 }}" class="text-right">Grand Total:</td>
                                                @if($productionStatus)
                                                    <td>{{ number_format($totalAssignedOther) }} Pcs</td>
                                                @else
                                                    <td>{{ number_format($totalAssignedOther) }} Pcs</td>
                                                    <td>{{ number_format($totalReceivedOther) }} Pcs</td>
                                                    <td>{{ number_format($totalPendingOther) }} Pcs</td>
                                                @endif
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @endif

                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#unit_id').select2({
                placeholder: 'Select Unit Person',
                width: '100%'
            });
            $('#design_no').select2({
                placeholder: 'Search Design No',
                width: '100%'
            });
        }
    });
</script>
@endsection