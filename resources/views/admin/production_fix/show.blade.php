@extends('admin.layouts.app')

@section('content')
@php
    $nothing = empty($plan['stitching']) && empty($plan['godam']) && empty($plan['completed']) && empty($plan['timings_done']) && empty($plan['timings_reopened']);
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
            {{ $r['timings_done'] }} stage end dates filled, {{ $r['timings_reopened'] }} stage end dates cleared.
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
                </tbody>
            </table>
            <p class="text-muted mb-3" style="font-size: 13px;">
                Only items 1 and 2 change a unit's balance (its "My Work" in the worker app). Cutting (Task Complete) and Packing / Dispatch (packing screen) are not touched.
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
