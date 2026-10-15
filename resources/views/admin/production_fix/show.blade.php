@extends('admin.layouts.app')

@section('content')
@php
    $pk = $plan['packing'] ?? [];
    $nothing = empty($plan['stitching']) && empty($plan['godam']) && empty($plan['completed']) && empty($plan['timings_done']) && empty($plan['timings_reopened'])
        && empty($pk['balances']) && empty($pk['dates_done']) && empty($pk['dates_reopened']) && empty($pk['domestic_boxes']);
    $listOnly = count($pk['over_packed'] ?? []) + count($pk['duplicate_cartons'] ?? []) + count($pk['packed_not_received'] ?? []) + count($pk['lotless_hold'] ?? []) + count($pk['balance_low'] ?? []);
    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '-';
@endphp
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-tools text-primary"></i> Repair old production data</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-file-invoice mr-1"></i> Uploaded Slips</a>
        </div>
    </div>

    @if(session('fix_error'))
        <div class="alert alert-danger">{{ session('fix_error') }}</div>
    @endif
    @if(session('fix_result'))
        @php $r = session('fix_result'); @endphp
        <div class="alert alert-success">
            <strong>Done.</strong>
            {{ $r['stitching'] }} stitching entries closed, {{ $r['godam'] }} Godam entries closed,
            {{ $r['completed'] }} finished entries marked complete,
            {{ $r['timings_done'] }} stage end dates filled, {{ $r['timings_reopened'] }} stage end dates cleared,
            {{ $r['packing_balances'] ?? 0 }} packing balances corrected, {{ $r['packing_dates_done'] ?? 0 }} packing end dates filled, {{ $r['packing_dates_reopened'] ?? 0 }} packing end dates cleared,
            {{ $r['domestic_boxes'] ?? 0 }} domestic boxes moved to domestic stock status.
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
            <table class="table table-sm table-bordered mb-3" style="max-width: 760px;">
                <tbody>
                    <tr><td>1. Stitching entries that are not real work any more (balance set to 0)</td><td class="text-right font-weight-bold">{{ count($plan['stitching']) }}</td></tr>
                    <tr><td>2. Embroidery parts still in Godam although Cutting already sent the lot to Stitching</td><td class="text-right font-weight-bold">{{ count($plan['godam']) }}</td></tr>
                    <tr><td>3. Entries fully sent on but never marked complete (balance not changed)</td><td class="text-right font-weight-bold">{{ count($plan['completed']) }}</td></tr>
                    <tr><td>4. Stage end dates to fill (no unit of the stage holds the lot any more)</td><td class="text-right font-weight-bold">{{ count($plan['timings_done']) }}</td></tr>
                    <tr><td>5. Stage end dates to clear (a unit of the stage still holds pieces of the lot)</td><td class="text-right font-weight-bold">{{ count($plan['timings_reopened']) }}</td></tr>
                    <tr><td>6. Packing balances to correct (pieces already packed / sent out but still counted as waiting)</td><td class="text-right font-weight-bold">{{ count($pk['balances'] ?? []) }}</td></tr>
                    <tr><td>7. Packing end dates to fill (every piece of the lot is packed / sent out)</td><td class="text-right font-weight-bold">{{ count($pk['dates_done'] ?? []) }}</td></tr>
                    <tr><td>8. Packing end dates to clear (pieces of the lot are still waiting at packing)</td><td class="text-right font-weight-bold">{{ count($pk['dates_reopened'] ?? []) }}</td></tr>
                    <tr><td>9. Domestic boxes saved with the normal carton status (set to domestic stock, not dispatched)</td><td class="text-right font-weight-bold">{{ array_sum(array_column($pk['domestic_boxes'] ?? [], 'boxes')) }}</td></tr>
                </tbody>
            </table>
            <p class="text-muted mb-3" style="font-size: 13px;">
                Only items 1, 2 and 6 change a unit's balance (its "My Work" in the worker app); item 6 only ever lowers a packing balance, never raises it.
                Cutting (Task Complete), dispatch and stock quantities are not touched; item 9 only changes the status of domestic boxes that were never dispatched.
                The lists at the bottom of the page are shown only, nothing in them is changed.
                Every old value is saved, so the change can be undone.
            </p>
            <form method="POST" action="{{ route('admin.production-fix.run') }}"
                  onsubmit="if(!confirm('Apply all changes listed on this page?\n\nTake a database backup first (Download DB).')) return false; this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Repairing, please wait...';">
                @csrf
                <a href="{{ route('admin.download-db') }}" class="btn-erp btn-erp-outline mr-2"><i class="fas fa-database mr-1"></i> 1. Download backup</a>
                <button type="submit" class="btn-erp btn-erp-primary px-4"><i class="fas fa-magic mr-1"></i> 2. Repair now</button>
            </form>
        @endif
    </div>

    @if(!empty($plan['stitching']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">1. Stitching entries to close ({{ count($plan['stitching']) }} entries, {{ number_format(array_sum(array_column($plan['stitching'], 'pcs'))) }} pcs)</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Unit</th><th>Lot No</th><th>Came from</th><th class="text-right">Open pcs</th><th>Why</th></tr></thead>
                    <tbody>
                        @foreach($plan['stitching'] as $row)
                            <tr>
                                <td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $row['source'] }}</td>
                                <td class="text-right">{{ number_format($row['pcs']) }}</td><td>{{ $row['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['godam']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">2. Godam entries to close</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width: 600px;">
                <thead><tr><th>Godam</th><th>Lot No</th><th class="text-right">Pcs in Godam</th></tr></thead>
                <tbody>
                    @foreach($plan['godam'] as $row)
                        <tr><td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td class="text-right">{{ number_format($row['pcs']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($plan['completed']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">3. Entries to mark complete ({{ count($plan['completed']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Stage</th><th>Unit</th><th>Lot No</th><th>Type</th><th>Completed on</th></tr></thead>
                    <tbody>
                        @foreach($plan['completed'] as $row)
                            <tr><td>{{ $row['stage'] }}</td><td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $row['kind'] }}</td><td>{{ $fmtDate($row['completed_on']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['timings_done']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">4. Stage end dates to fill ({{ count($plan['timings_done']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Stage</th><th>Lot No</th><th>End date</th></tr></thead>
                    <tbody>
                        @foreach($plan['timings_done'] as $row)
                            <tr><td>{{ $row['stage'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $fmtDate($row['date']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['timings_reopened']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">5. Stage end dates to clear ({{ count($plan['timings_reopened']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Stage</th><th>Lot No</th><th>End date now (wrong)</th></tr></thead>
                    <tbody>
                        @foreach($plan['timings_reopened'] as $row)
                            <tr><td>{{ $row['stage'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $fmtDate($row['was']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($pk['balances']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">6. Packing balances to correct ({{ count($pk['balances']) }} lots, {{ number_format(array_sum(array_column($pk['balances'], 'was')) - array_sum(array_column($pk['balances'], 'now'))) }} pcs)</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Packing unit</th><th>Lot No</th><th class="text-right">Received</th><th class="text-right">Packed / sent out</th><th class="text-right">Balance now (wrong)</th><th class="text-right">Balance after repair</th></tr></thead>
                    <tbody>
                        @foreach($pk['balances'] as $row)
                            <tr>
                                <td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td>
                                <td class="text-right">{{ number_format($row['received']) }}</td><td class="text-right">{{ number_format($row['used']) }}</td>
                                <td class="text-right text-danger">{{ number_format($row['was']) }}</td><td class="text-right font-weight-bold text-success">{{ number_format($row['now']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($pk['dates_done']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">7. Packing end dates to fill ({{ count($pk['dates_done']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0" style="max-width: 600px;">
                    <thead><tr><th>Lot No</th><th>End date (last packing entry)</th></tr></thead>
                    <tbody>
                        @foreach($pk['dates_done'] as $row)
                            <tr><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $fmtDate($row['date']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($pk['dates_reopened']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">8. Packing end dates to clear ({{ count($pk['dates_reopened']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Lot No</th><th>End date now (wrong)</th><th>Why</th></tr></thead>
                    <tbody>
                        @foreach($pk['dates_reopened'] as $row)
                            <tr><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $fmtDate($row['was']) }}</td><td>{{ $row['why'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($pk['domestic_boxes']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">9. Domestic boxes to mark as domestic stock ({{ array_sum(array_column($pk['domestic_boxes'], 'boxes')) }} boxes)</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width: 600px;">
                <thead><tr><th>Packing session</th><th>Order</th><th class="text-right">Boxes</th></tr></thead>
                <tbody>
                    @foreach($pk['domestic_boxes'] as $row)
                        <tr><td><a href="{{ route('admin.packing.view', $row['session']) }}" target="_blank">#{{ $row['session'] }}</a></td><td>{{ $row['order'] }}</td><td class="text-right">{{ $row['boxes'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($listOnly > 0)
        <div class="erp-card p-3 mb-2" style="border-left: 4px solid #f0ad4e;">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-eye text-warning mr-1"></i> Packing: please check by hand (shown only, nothing is changed)</h6>
            <p class="text-muted mb-2" style="font-size: 13px;">These cannot be repaired automatically, mostly because the cartons are already dispatched.</p>

            @if(!empty($pk['over_packed']))
                <div class="font-weight-bold mt-2">Lots packed more than received ({{ count($pk['over_packed']) }})</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 700px;">
                    <thead><tr><th>Packing unit</th><th>Lot No</th><th class="text-right">Received</th><th class="text-right">Packed / sent out</th><th class="text-right">Extra</th></tr></thead>
                    <tbody>
                        @foreach($pk['over_packed'] as $row)
                            <tr><td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td class="text-right">{{ number_format($row['received']) }}</td><td class="text-right">{{ number_format($row['used']) }}</td><td class="text-right text-danger">{{ number_format($row['used'] - $row['received']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($pk['packed_not_received']))
                <div class="font-weight-bold mt-2">Packed by a unit that never received the lot ({{ count($pk['packed_not_received']) }})</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 600px;">
                    <thead><tr><th>Packing unit</th><th>Lot No</th><th class="text-right">Pcs packed / sent out</th></tr></thead>
                    <tbody>
                        @foreach($pk['packed_not_received'] as $row)
                            <tr><td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td class="text-right">{{ number_format($row['pcs']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($pk['lotless_hold']))
                <div class="font-weight-bold mt-2">Packing end date kept: the order has old cartons saved without a lot number ({{ count($pk['lotless_hold']) }})</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 700px;">
                    <thead><tr><th>Lot No</th><th>Records say still to pack</th><th>End date</th></tr></thead>
                    <tbody>
                        @foreach($pk['lotless_hold'] as $row)
                            <tr><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td>{{ $row['held'] }} pcs</td><td>{{ $fmtDate($row['date']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($pk['balance_low']))
                <div class="font-weight-bold mt-2">Packing balance lower than the records (not raised) ({{ count($pk['balance_low']) }})</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 700px;">
                    <thead><tr><th>Packing unit</th><th>Lot No</th><th class="text-right">Balance</th><th class="text-right">Records say</th></tr></thead>
                    <tbody>
                        @foreach($pk['balance_low'] as $row)
                            <tr><td>{{ $row['unit'] }}</td><td class="font-weight-bold">{{ $row['lot_no'] }}</td><td class="text-right">{{ number_format($row['stored']) }}</td><td class="text-right">{{ number_format($row['records']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($pk['duplicate_cartons']))
                <div class="font-weight-bold mt-2">Same carton number used twice in one packing session ({{ count($pk['duplicate_cartons']) }} sessions)</div>
                <div class="table-responsive" style="max-height: 320px;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Session</th><th>Slip</th><th>Order</th><th>Packing unit</th><th>Carton numbers used twice</th><th class="text-right">Dispatched</th></tr></thead>
                        <tbody>
                            @foreach($pk['duplicate_cartons'] as $row)
                                <tr>
                                    <td><a href="{{ route('admin.packing.view', $row['session']) }}" target="_blank">#{{ $row['session'] }}</a></td>
                                    <td>{{ $row['slip'] }}</td><td>{{ $row['order'] }}</td><td>{{ $row['unit'] }}</td>
                                    <td style="max-width: 360px; word-break: break-word;">{{ $row['cartons'] }}</td>
                                    <td class="text-right">{{ $row['dispatched'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
