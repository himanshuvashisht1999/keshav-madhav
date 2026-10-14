@extends('layouts.unit')

@section('title', 'Home')

@push('styles')
<style>
    .hm-hello {
        background: var(--u-green);
        color: #fff;
        border-radius: 6px;
        border-bottom: 3px solid var(--u-yellow);
        padding: 14px 14px 12px;
        margin-bottom: 10px;
    }
    .hm-hello small { display: block; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; opacity: 0.8; }
    .hm-hello strong { display: block; font-size: 22px; font-weight: 800; line-height: 1.2; margin-top: 2px; }
    .hm-hello .hm-stage { display: inline-block; margin-top: 6px; background: var(--u-yellow); color: var(--u-green); font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 2px 9px; border-radius: 10px; }

    .hm-menu tr { cursor: pointer; }
    .hm-menu td:first-child { width: 44px; text-align: center; color: var(--u-green); font-size: 17px; }
    .hm-menu .hm-name { font-weight: 800; color: var(--u-text); font-size: 14px; }
    .hm-menu .hm-desc { font-size: 12px; color: var(--u-muted); }
    .hm-menu td:last-child { width: 30px; text-align: center; color: #b8c4b9; }
</style>
@endpush

@section('content')
    @php
        $stageId = $data->master_stage_id;
        $isCuttingUnit = $stageId == 3;
        $phone = (string) ($data->phone ?? '');
    @endphp

    <div class="hm-hello">
        <small>Welcome</small>
        <strong>{{ $data->name }}</strong>
        <span class="hm-stage">{{ $data->masterStage->name ?? 'N/A' }}</span>
    </div>

    <div class="xl-head">
        <h3><i class="fas fa-th-list"></i> Menu</h3>
    </div>
    <div class="xl-wrap">
        <table class="hm-menu">
            <tbody>
                <tr data-href="{{ route('unit.assignments') }}">
                    <td><i class="fas fa-clipboard-list"></i></td>
                    <td><div class="hm-name">Tasks</div><div class="hm-desc">Lots and orders assigned to you</div></td>
                    <td><i class="fas fa-chevron-right"></i></td>
                </tr>
                @unless($isCuttingUnit)
                    <tr data-href="{{ route('unit.pending-tasks') }}">
                        <td><i class="fas fa-tasks"></i></td>
                        <td><div class="hm-name">Pending</div><div class="hm-desc">Lot-wise balance still to deliver</div></td>
                        <td><i class="fas fa-chevron-right"></i></td>
                    </tr>
                @endunless
                <tr class="open-upload-modal" data-type="{{ $isCuttingUnit ? '1' : '2' }}" data-product-set-id=""
                    data-transaction-id="" data-transaction-type="production">
                    <td><i class="fas fa-camera"></i></td>
                    <td><div class="hm-name">Upload Slip</div><div class="hm-desc">Take a photo of a {{ $isCuttingUnit ? 'fabric' : 'piece' }} slip</div></td>
                    <td><i class="fas fa-chevron-right"></i></td>
                </tr>
                <tr data-href="{{ route('unit.lot.search') }}">
                    <td><i class="fas fa-search"></i></td>
                    <td><div class="hm-name">Track Lot</div><div class="hm-desc">See where a lot is right now</div></td>
                    <td><i class="fas fa-chevron-right"></i></td>
                </tr>
                <tr data-href="{{ route('unit.history') }}">
                    <td><i class="fas fa-clock"></i></td>
                    <td><div class="hm-name">History</div><div class="hm-desc">Slips and tasks you have done</div></td>
                    <td><i class="fas fa-chevron-right"></i></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="xl-head">
        <h3><i class="fas fa-id-badge"></i> My Details</h3>
    </div>
    <div class="xl-wrap">
        <table class="xl-kv">
            <tbody>
                <tr><th>Name</th><td class="xl-strong">{{ $data->name }}</td></tr>
                <tr><th>Employee ID</th><td>{{ $data->employee_id ?? '-' }}</td></tr>
                <tr><th>Stage</th><td>{{ $data->masterStage->name ?? 'N/A' }}</td></tr>
                <tr><th>Cutting Master</th><td>{{ $data->masterFabricWarehouse->cutting_master_name ?? 'N/A' }}</td></tr>
                <tr><th>Phone</th><td>{{ $phone !== '' ? '••••' . substr($phone, -4) : '-' }}</td></tr>
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var row = e.target.closest('.hm-menu tr[data-href]');
        if (row) window.location.href = row.getAttribute('data-href');
    });
</script>
@endpush
