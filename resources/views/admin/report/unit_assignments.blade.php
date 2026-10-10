@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-tasks"></i> Unit assignments report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-layer-group text-muted"></i> Tracking unit task allotments, outputs, and rework</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.reports.unit-assignments.export', request()->query()) }}" class="btn-erp btn-erp-outline mr-1" style="border-color: #22c55e; color: #166534;" title="Export Excel">
                <i class="fas fa-file-excel mr-1 text-success"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.unit-assignments.pdf', request()->query()) }}" class="btn-erp btn-erp-outline" style="border-color: #ef4444; color: #991b1b;" title="Export PDF">
                <i class="fas fa-file-pdf mr-1 text-danger"></i> Export PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 px-3 mb-2" style="font-size: 13px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 px-3 mb-2" style="font-size: 13px;">{{ session('error') }}</div>
    @endif

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.reports.unit-assignments') }}" class="mb-0">
            <div class="form-row align-items-end">

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Stage</label>
                    <select name="stage_id" id="stage_id" class="form-control form-control-sm select2 erp-filter-select"
                        onchange="this.form.submit()">
                        <option value="">All stages</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Unit person</label>
                    <select name="unit_id[]" id="unit_id" class="form-control form-control-sm erp-filter-select" multiple>
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

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Status view</label>
                    <select name="view" class="form-control form-control-sm select2 erp-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ request('view', 'open') == 'all' ? 'selected' : '' }}>All tasks</option>
                        <option value="open" {{ request('view', 'open') == 'open' ? 'selected' : '' }}>Pending tasks</option>
                        <option value="closed" {{ request('view', 'open') == 'closed' ? 'selected' : '' }}>Done tasks</option>
                        <option value="delayed" {{ request('view', 'open') == 'delayed' ? 'selected' : '' }}>Delayed tasks</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Order no</label>
                    <input type="text" name="order_no" value="{{ request('order_no') }}" class="form-control form-control-sm erp-filter-input"
                        placeholder="Search order no...">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Lot no</label>
                    <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control form-control-sm erp-filter-input"
                        placeholder="Search lot no...">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Design no</label>
                    <select name="design_no[]" id="design_no" class="form-control form-control-sm erp-filter-select" multiple>
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

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Start date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm erp-filter-input">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">End date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm erp-filter-input">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Production status</label>
                    <select name="production_status" class="form-control form-control-sm select2 erp-filter-select" onchange="this.form.submit()">
                        <option value="">Select status</option>
                        <option value="not_printing" {{ request('production_status') == 'not_printing' ? 'selected' : '' }}>Not sent to printing</option>
                        <option value="not_stitching" {{ request('production_status') == 'not_stitching' ? 'selected' : '' }}>Not sent to stitching</option>
                        <option value="not_both" {{ request('production_status') == 'not_both' ? 'selected' : '' }}>Both not sent</option>
                        <option value="printing_sent_stitching_pending" {{ request('production_status') == 'printing_sent_stitching_pending' ? 'selected' : '' }}>Printing sent but stitching pending</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Pending qty range</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" name="min_pending_qty" value="{{ request()->has('min_pending_qty') ? request('min_pending_qty') : ($min_pending_qty ?? 1) }}" class="form-control erp-filter-input" placeholder="Min">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">-</span>
                        </div>
                        <input type="number" step="any" name="max_pending_qty" value="{{ request('max_pending_qty') }}" class="form-control erp-filter-input" placeholder="Max">
                    </div>
                </div>

                <div class="col-md-3 col-sm-12 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filters">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.reports.unit-assignments') }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            @if($assignments->isEmpty())
                <div class="text-center p-4">
                    <h5 class="text-muted mb-0">No assignments found</h5>
                </div>
            @else
                @if($type == 'cutting')
                    <table class="table table-sm table-hover erp-table mb-0">
                        <thead>
                            <tr>
                                <th>Unit person</th>
                                <th>Order no</th>
                                <th>Design no</th>
                                <th class="text-right">Assigned qty</th>
                                <th class="text-right">Receive qty</th>
                                <th class="text-right">Pending qty</th>
                                <th>Start date</th>
                                <th>Completed date</th>
                                <th>Estimated date</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 100px;">Action</th>
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
                                    <td>
                                        <strong>{{ $item->stage_master_unit->name ?? '-' }}</strong>
                                        @if(isset($item->stage_master_unit->phone) && $item->stage_master_unit->phone)
                                            <a href="https://wa.me/{{ $item->stage_master_unit->phone }}" target="_blank" class="ml-1 text-success" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td><code>{{ $item->orderMain->sku ?? '-' }}</code></td>
                                    <td>{{ $item->design_number ?? '-' }}</td>
                                    <td class="text-right font-weight-bold">{{ $assignedVal }} pcs</td>
                                    <td class="text-right font-weight-bold text-success">{{ $receivedVal }} pcs</td>
                                    <td class="text-right font-weight-bold text-danger">{{ $pendingVal }} pcs</td>
                                    <td>{{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}</td>
                                    <td>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</td>
                                    <td>{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</td>
                                    <td class="text-center">
                                        <span class="erp-badge-status erp-badge-status-{{ $item->status_class ?? 'neutral' }}">
                                            {{ $item->status_text ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if(request('stage_id') != 3 || !empty(request('production_status')))
                                            @if(!empty($item->lot_no))
                                                <a href="{{ route('admin.report.lots.lot-details', $item->lot_no) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;" title="View lot">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Grand total</th>
                                <th class="text-right">{{ number_format($totalAssigned) }} pcs</th>
                                <th class="text-right text-success">{{ number_format($totalReceived) }} pcs</th>
                                <th class="text-right text-danger">{{ number_format($totalPending) }} pcs</th>
                                <th colspan="5"></th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <table class="table table-sm table-hover erp-table mb-0">
                        <thead>
                            <tr>
                                <th>Unit person</th>
                                @if(!$productionStatus)
                                    <th>From stage</th>
                                    <th>Current stage</th>
                                @endif
                                <th>Lot no</th>
                                <th>Design no</th>
                                @if(!$productionStatus)
                                    <th>Sent by</th>
                                @endif
                                @if($productionStatus)
                                    <th class="text-right">Total quantity</th>
                                @else
                                    <th class="text-right">Assigned qty</th>
                                    <th class="text-right">Receive qty</th>
                                    <th class="text-right">Pending qty</th>
                                @endif
                                @if($productionStatus)
                                    <th>Lot date</th>
                                @else
                                    <th>Start date</th>
                                    <th>Completed date</th>
                                    <th>Estimated date</th>
                                @endif
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 100px;">Action</th>
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
                                    <td>
                                        <strong>{{ $item->getToUnitMaster->name ?? $item->stage_master_unit->name ?? '-' }}</strong>
                                        @php $phone = $item->getToUnitMaster->phone ?? $item->stage_master_unit->phone ?? null; @endphp
                                        @if($phone)
                                            <a href="https://wa.me/{{ $phone }}" target="_blank" class="ml-1 text-success" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    </td>
                                    @if(!$productionStatus)
                                        <td>
                                            {{ $item->from_stage->name ?? $item->fromStage->name ?? '-' }}
                                            @if(!empty($item->has_rework) && !empty($item->rework_from_stage) && $item->rework_from_stage !== ($item->from_stage->name ?? ''))
                                                <div class="text-danger small font-weight-bold" style="font-size: 10px;" title="Rework sent from {{ $item->rework_from_stage }}">
                                                    <i class="fas fa-undo"></i> Rework: {{ $item->rework_from_stage }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $item->to_stage_name ?? $item->to_stage->name ?? $item->toStage->name ?? '-' }}</td>
                                    @endif
                                    <td>
                                        <code>{{ $item->lot_no ?? 'Pending' }}</code>
                                        @if(!empty($item->has_rework))
                                            <span class="erp-badge-status erp-badge-status-open ml-1" style="font-size: 10px; padding: 1px 4px;" title="{{ $item->rework_remarks ?? 'Defect return for rework' }}">
                                                <i class="fas fa-tools"></i> Rework
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $item->design_number ?? '-' }}</td>
                                    @if(!$productionStatus)
                                        <td>
                                            {{ $item->getFromUnitMaster->name ?? $item->getUnitMaster->name ?? '-' }}
                                            @if(!empty($item->has_rework) && !empty($item->rework_from_unit) && $item->rework_from_unit !== ($item->getFromUnitMaster->name ?? ''))
                                                <div class="text-danger small font-weight-bold" style="font-size: 10px;">
                                                    (Rework: {{ $item->rework_from_unit }})
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                    @if($productionStatus)
                                        <td class="text-right font-weight-bold">{{ $assignedValOther }} pcs</td>
                                    @else
                                        <td class="text-right font-weight-bold">
                                            <div>{{ $assignedValOther }} pcs</div>
                                            @if(!empty($item->has_rework) && !empty($item->rework_qty))
                                                <div class="text-muted" style="font-size: 11px; white-space: nowrap;">
                                                    ({{ $item->regular_qty ?? ($assignedValOther - $item->rework_qty) }} reg + <span class="text-warning font-weight-bold">{{ $item->rework_qty }} rework</span>)
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold text-success">{{ $receivedValOther }} pcs</td>
                                        <td class="text-right font-weight-bold text-danger">
                                            <div>{{ $pendingValOther }} pcs</div>
                                            @if(!empty($item->has_rework) && !empty($item->rework_pending_qty) && $item->rework_pending_qty > 0)
                                                <span class="erp-badge-status erp-badge-status-delayed" style="font-size: 10px; padding: 1px 4px; margin-top: 2px;" title="{{ $item->rework_remarks ?? 'Defect return for rework' }}">
                                                    <i class="fas fa-exclamation-triangle"></i> {{ $item->rework_pending_qty }} pcs rework
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                    @if($productionStatus)
                                        <td>{{ $item->production_date ?? '-' }}</td>
                                    @else
                                        <td>
                                            {{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}
                                            @if(!empty($item->has_rework) && !empty($item->rework_start_time) && $item->rework_start_time->format('d M Y') !== ($item->start_time ? $item->start_time->format('d M Y') : ''))
                                                <div class="text-muted small" style="font-size: 10px;" title="Rework Returned Date">
                                                    Rework: {{ $item->rework_start_time->format('d M Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</td>
                                        <td>{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</td>
                                    @endif
                                    <td class="text-center">
                                        <span class="erp-badge-status erp-badge-status-{{ $item->status_class ?? 'neutral' }}">
                                            {{ $item->status_text ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if(request('stage_id') != 3 || !empty(request('production_status')))
                                            @if(!empty($item->lot_no))
                                                <a href="{{ route('admin.report.lots.lot-details', $item->lot_no) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;" title="View lot">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="{{ $productionStatus ? 3 : 6 }}" class="text-right">Grand total</th>
                                @if($productionStatus)
                                    <th class="text-right">{{ number_format($totalAssignedOther) }} pcs</th>
                                @else
                                    <th class="text-right">{{ number_format($totalAssignedOther) }} pcs</th>
                                    <th class="text-right text-success">{{ number_format($totalReceivedOther) }} pcs</th>
                                    <th class="text-right text-danger">{{ number_format($totalPendingOther) }} pcs</th>
                                @endif
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#unit_id').select2({
                placeholder: 'Select unit person',
                width: '100%'
            });
            $('#design_no').select2({
                placeholder: 'Search design no',
                width: '100%'
            });
        }
    });
</script>
@endsection