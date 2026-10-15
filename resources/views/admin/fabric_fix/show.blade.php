@extends('admin.layouts.app')

@section('content')
@php
    $nothing = empty($plan['relinked']) && empty($plan['auto_pos']) && empty($plan['lines']);
    $m = fn($v) => $v === null ? '-' : number_format((float) $v, 2);
    $removedPos = collect($plan['auto_pos'])->where('done', 'removed');
    $syncedPos = collect($plan['auto_pos'])->where('done', 'updated');
@endphp
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-tools text-primary"></i> Repair old fabric PO data</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.purchase_order.index') }}" class="btn-erp btn-erp-outline mr-1"><i class="fas fa-file-invoice mr-1"></i> Fabric POs</a>
            <a href="{{ route('admin.fabric_receipt.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-truck mr-1"></i> Fabric Shipment</a>
        </div>
    </div>

    @if(session('fix_error'))
        <div class="alert alert-danger">{{ session('fix_error') }}</div>
    @endif
    @if(session('fix_result'))
        @php $r = session('fix_result'); @endphp
        <div class="alert alert-success">
            <strong>Done.</strong>
            {{ $r['relinked'] }} rolls linked back to their PO, {{ $r['auto_pos'] }} automatic POs corrected,
            {{ $r['lines'] }} PO lines corrected.
            Old values saved in <code>storage/app/{{ $r['log_file'] }}</code>.
        </div>
    @endif

    <div class="erp-card p-3 mb-2">
        @if($nothing)
            <div class="text-center py-3">
                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                <h5 class="font-weight-bold mb-0">Everything is correct. Nothing to repair.</h5>
            </div>
        @else
            <h5 class="font-weight-bold mb-2">What the button will change</h5>
            <table class="table table-sm table-bordered mb-3" style="max-width: 860px;">
                <tbody>
                    <tr><td>1. Rolls that lost the link to their PO line (old "edit PO" / "edit shipment"): linked back</td><td class="text-right font-weight-bold">{{ count($plan['relinked']) }}</td></tr>
                    <tr><td>2. Automatic POs (made for a shipment received without a PO) whose shipment was deleted: removed</td><td class="text-right font-weight-bold">{{ $removedPos->count() }}</td></tr>
                    <tr><td>3. Automatic POs set to match their shipment</td><td class="text-right font-weight-bold">{{ $syncedPos->count() }}</td></tr>
                    <tr><td>4. PO lines whose "left to receive" was wrong: set to ordered - received (the figure Reports &gt; Purchase Order already shows)</td><td class="text-right font-weight-bold">{{ count($plan['lines']) }}</td></tr>
                </tbody>
            </table>
            <p class="text-muted mb-3" style="font-size: 13px;">
                Only PO links and PO "left to receive" figures change. Shipments, bills, roll meters, stock, payments and
                vendor balances are not touched. The lists at the bottom are shown only. Every old value is saved, so the change can be undone.
            </p>
            <form method="POST" action="{{ route('admin.fabric-fix.run') }}"
                  onsubmit="if(!confirm('Apply all changes listed on this page?\n\nTake a database backup first (Download DB).')) return false; this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Repairing, please wait...';">
                @csrf
                <a href="{{ route('admin.download-db') }}" class="btn-erp btn-erp-outline mr-2"><i class="fas fa-database mr-1"></i> 1. Download backup</a>
                <button type="submit" class="btn-erp btn-erp-primary px-4"><i class="fas fa-magic mr-1"></i> 2. Repair now</button>
            </form>
        @endif
    </div>

    @if(!empty($plan['relinked']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">1. Rolls linked back to their PO ({{ count($plan['relinked']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Shipment</th><th>Roll no</th><th>Fabric</th><th class="text-right">Meters</th><th>Linked to PO</th><th>Why</th></tr></thead>
                    <tbody>
                        @foreach($plan['relinked'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.fabric_receipt.view', ['id' => $row['shipment_id']]) }}" target="_blank">{{ $row['shipment'] }}</a></td>
                                <td>{{ $row['roll_no'] }}</td><td>{{ $row['fabric'] }}</td><td class="text-right">{{ $m($row['meter']) }}</td>
                                <td><a href="{{ route('admin.purchase_order.view', ['id' => $row['po_id']]) }}" target="_blank">{{ $row['po'] }}</a></td>
                                <td>{{ $row['why'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($removedPos->isNotEmpty() || $syncedPos->isNotEmpty())
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">2 + 3. Automatic POs</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width: 760px;">
                <thead><tr><th>PO</th><th>Change</th></tr></thead>
                <tbody>
                    @foreach($removedPos as $row)
                        <tr><td>{{ $row['po'] }}</td><td class="text-danger">Removed: its shipment was deleted, nothing was received on it</td></tr>
                    @endforeach
                    @foreach($syncedPos as $row)
                        <tr><td><a href="{{ route('admin.purchase_order.view', ['id' => $row['po_id']]) }}" target="_blank">{{ $row['po'] }}</a></td><td>Lines set to the rolls of its shipment (see the lines below)</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($plan['lines']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">4. PO lines corrected ({{ count($plan['lines']) }})</h6>
            <div class="table-responsive" style="max-height: 520px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>PO</th><th>Fabric</th><th class="text-right">Ordered (m)</th><th class="text-right">Received (m)</th>
                            <th class="text-right">Left to receive now</th><th class="text-right">After repair</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan['lines'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.purchase_order.view', ['id' => $row['po_id']]) }}" target="_blank">{{ $row['po'] }}</a></td>
                                <td>{{ $row['fabric'] }}</td>
                                <td class="text-right">
                                    @if($row['ordered_was'] !== null && $row['ordered_now'] !== null && abs($row['ordered_was'] - $row['ordered_now']) > 0.001)
                                        <span class="text-danger">{{ $m($row['ordered_was']) }}</span> &rarr; <b>{{ $m($row['ordered_now']) }}</b>
                                    @elseif($row['ordered_now'] === null)
                                        <span class="text-danger">{{ $m($row['ordered_was']) }} (line removed)</span>
                                    @elseif($row['ordered_was'] === null)
                                        <b>{{ $m($row['ordered_now']) }}</b> (new line)
                                    @else
                                        {{ $m($row['ordered_now']) }}
                                    @endif
                                </td>
                                <td class="text-right">{{ $m($row['received']) }}</td>
                                <td class="text-right text-danger">{{ $m($row['left_was']) }}</td>
                                <td class="text-right text-success font-weight-bold">{{ $m($row['left_now']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['wrong_fabric']) || !empty($plan['bill_mismatch']))
        <div class="erp-card p-3 mb-2" style="border-left: 4px solid #f0ad4e;">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-eye text-warning mr-1"></i> Please check by hand (shown only, nothing is changed)</h6>
            @if(!empty($plan['bill_mismatch']))
                <div class="font-weight-bold mt-2">Shipments whose rolls do not add up to the bill (open the shipment, check the rolls against the challan and save)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-2" style="max-width: 900px;">
                        <thead><tr><th>Shipment</th><th>Bill No</th><th class="text-right">Bill rolls / meters / amount</th><th class="text-right">Rolls entered / meters / amount</th></tr></thead>
                        <tbody>
                            @foreach($plan['bill_mismatch'] as $row)
                                <tr>
                                    <td><a href="{{ route('admin.fabric_receipt.view', ['id' => $row['id']]) }}" target="_blank">{{ $row['sku'] }}</a></td>
                                    <td>{{ $row['bill_no'] }}</td>
                                    <td class="text-right">{{ $row['roll'] }} / {{ $m($row['total_meter']) }} / {{ $m($row['amount']) }}</td>
                                    <td class="text-right">{{ $row['rolls'] }} / {{ $m($row['meters']) }} / {{ $m($row['rolls_amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @if(!empty($plan['wrong_fabric']))
                <div class="font-weight-bold mt-2">Rolls on a PO line of another fabric (made by the old PO "adjustment" page)</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 900px;">
                    <thead><tr><th>Shipment</th><th>Roll no</th><th class="text-right">Meters</th><th>Roll fabric</th><th>PO</th><th>PO line fabric</th></tr></thead>
                    <tbody>
                        @foreach($plan['wrong_fabric'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.fabric_receipt.view', ['id' => $row['shipment_id']]) }}" target="_blank">{{ $row['shipment'] }}</a></td>
                                <td>{{ $row['roll_number'] }}</td><td class="text-right">{{ $m($row['meter']) }}</td>
                                <td>{{ $row['roll_fabric'] }}</td><td>{{ $row['po'] }}</td><td>{{ $row['line_fabric'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if($runs->isNotEmpty())
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold mb-1">Earlier repairs (old values saved)</h6>
            @foreach($runs as $file)
                <div style="font-size: 13px;"><code>storage/app/{{ $file }}</code></div>
            @endforeach
        </div>
    @endif
</div>
@endsection
