@extends('admin.layouts.app')

@section('content')
@php
    $nothing = empty($plan['unpicked']) && empty($plan['redispatched']) && empty($plan['purchase_lines']) && empty($plan['negatives']);
    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '-';
@endphp
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-tools text-primary"></i> Repair old inventory data</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.inventory.warehouse_stock') }}" class="btn-erp btn-erp-outline"><i class="fas fa-warehouse mr-1"></i> Warehouse Stock</a>
        </div>
    </div>

    @if(!$plan['picks_table'])
        <div class="alert alert-warning">
            The new pick tracking table is not created yet. Please run the database update command first
            (see the update steps), then open this page again.
        </div>
    @endif

    @if(session('fix_error'))
        <div class="alert alert-danger">{{ session('fix_error') }}</div>
    @endif
    @if(session('fix_result'))
        @php $r = session('fix_result'); @endphp
        <div class="alert alert-success">
            <strong>Done.</strong>
            {{ $r['unpicked'] }} order lines un-picked, {{ $r['redispatched'] }} re-dispatched lines taken out of stock,
            {{ $r['purchase_lines'] }} purchase lines restored, {{ $r['negatives'] }} negative / inactive values corrected.
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
            <table class="table table-sm table-bordered mb-3" style="max-width: 820px;">
                <tbody>
                    <tr><td>1. Order lines of deleted dispatches still marked "picked" although their boxes are back in stock (they become "to be picked")</td><td class="text-right font-weight-bold">{{ count($plan['unpicked']) }}</td></tr>
                    <tr><td>2. Lines dispatched again without being picked again: their boxes are still counted in stock (taken out now)</td><td class="text-right font-weight-bold">{{ count($plan['redispatched']) }}</td></tr>
                    <tr><td>3. Purchase lines hidden from Inbound History by the old purchase edit (shown again)</td><td class="text-right font-weight-bold">{{ array_sum(array_column($plan['purchase_lines'], 'lines')) }}</td></tr>
                    <tr><td>4. Negative fabric rolls / stock rows set to 0, inactive stock rows made active</td><td class="text-right font-weight-bold">{{ count($plan['negatives']) }}</td></tr>
                </tbody>
            </table>
            <p class="text-muted mb-3" style="font-size: 13px;">
                Items 2 and 4 change stock figures; item 1 changes only the order lines; item 3 changes only history.
                Party balances, bills and dispatches are not touched. The lists at the bottom are shown only.
                Every old value is saved, so the change can be undone.
            </p>
            <form method="POST" action="{{ route('admin.inventory-fix.run') }}"
                  onsubmit="if(!confirm('Apply all changes listed on this page?\n\nTake a database backup first (Download DB).')) return false; this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Repairing, please wait...';">
                @csrf
                <a href="{{ route('admin.download-db') }}" class="btn-erp btn-erp-outline mr-2"><i class="fas fa-database mr-1"></i> 1. Download backup</a>
                <button type="submit" class="btn-erp btn-erp-primary px-4"><i class="fas fa-magic mr-1"></i> 2. Repair now</button>
            </form>
        @endif
    </div>

    @if(!empty($plan['unpicked']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">1. Order lines to un-pick ({{ count($plan['unpicked']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Order</th><th>Item</th><th>Deleted dispatch</th><th>Deleted on</th><th class="text-right">Picked boxes (already in stock)</th></tr></thead>
                    <tbody>
                        @foreach($plan['unpicked'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.agent-orders.show', $row['order']) }}" target="_blank">#{{ $row['order'] }}</a></td>
                                <td>{{ $row['item'] }}</td><td>#{{ $row['dispatch'] }}</td><td>{{ $fmtDate($row['deleted_at']) }}</td>
                                <td class="text-right">{{ $row['picked'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['redispatched']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">2. Boxes to take out of stock ({{ array_sum(array_column($plan['redispatched'], 'taken')) }} boxes)</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Order</th><th>Item</th><th>Deleted dispatch</th><th>Dispatched again in</th><th class="text-right">Boxes</th><th class="text-right">Taken out</th></tr></thead>
                    <tbody>
                        @foreach($plan['redispatched'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.agent-orders.show', $row['order']) }}" target="_blank">#{{ $row['order'] }}</a></td>
                                <td>{{ $row['item'] }}</td><td>#{{ $row['deleted_dispatch'] }}</td>
                                <td><a href="{{ route('admin.agent-orders.dispatches.show', $row['new_dispatch']) }}" target="_blank">#{{ $row['new_dispatch'] }}</a></td>
                                <td class="text-right">{{ $row['boxes'] }}</td>
                                <td class="text-right">{{ $row['taken'] }}@if($row['short'] > 0) <span class="text-danger">({{ $row['short'] }} not in stock any more)</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['purchase_lines']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">3. Purchases whose lines come back to Inbound History ({{ count($plan['purchase_lines']) }})</h6>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-bordered table-striped mb-0" style="max-width: 760px;">
                    <thead><tr><th>Purchase</th><th>Bill No</th><th class="text-right">Lines</th><th class="text-right">Boxes</th><th>Entry date</th></tr></thead>
                    <tbody>
                        @foreach($plan['purchase_lines'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.inventory.purchase_history.show', $row['purchase']) }}" target="_blank">#{{ $row['purchase'] }}</a></td>
                                <td>{{ $row['bill_no'] ?: '-' }}</td><td class="text-right">{{ $row['lines'] }}</td><td class="text-right">{{ $row['boxes'] }}</td><td>{{ $fmtDate($row['date']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0 mt-2" style="font-size: 13px;">
                The old purchase edit could also add back boxes that were already sold. How many cannot be worked out afterwards,
                so a physical count of these purchases' items is recommended.
            </p>
        </div>
    @endif

    @if(!empty($plan['negatives']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">4. Values to correct</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width: 760px;">
                <thead><tr><th>What</th><th>Now (wrong)</th><th>After repair</th></tr></thead>
                <tbody>
                    @foreach($plan['negatives'] as $row)
                        <tr><td>{{ $row['what'] }}</td><td class="text-danger">{{ $row['was'] }}</td><td class="text-success font-weight-bold">{{ $row['now'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($plan['duplicate_variants']) || !empty($plan['zero_pcs_lines']) || $plan['no_rack_stock'] > 0)
        <div class="erp-card p-3 mb-2" style="border-left: 4px solid #f0ad4e;">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-eye text-warning mr-1"></i> Please check by hand (shown only, nothing is changed)</h6>
            @if(!empty($plan['duplicate_variants']))
                <div class="font-weight-bold mt-2">Products with the same size set entered twice (stock lists now use the first one)</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 700px;">
                    <thead><tr><th>Design</th><th>Size set</th><th>Variant ids</th><th>MRPs</th></tr></thead>
                    <tbody>
                        @foreach($plan['duplicate_variants'] as $row)
                            <tr><td>{{ $row['design_number'] }}</td><td>{{ $row['size_set'] }}</td><td>{{ $row['variant_ids'] }}</td><td>{{ $row['mrps'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(!empty($plan['zero_pcs_lines']))
                <div class="font-weight-bold mt-2">Open order lines with 0 pieces (open the order and save it again)</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 700px;">
                    <thead><tr><th>Order</th><th>Item</th><th class="text-right">Boxes</th></tr></thead>
                    <tbody>
                        @foreach($plan['zero_pcs_lines'] as $row)
                            <tr><td>#{{ $row['order'] }}</td><td>{{ $row['design_number'] }} / {{ $row['color_name'] }} / {{ $row['size_set_name'] }}</td><td class="text-right">{{ $row['box_qty'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if($plan['no_rack_stock'] > 0)
                <div class="mt-2">{{ $plan['no_rack_stock'] }} stock row(s) have boxes but no rack. Move them to a rack from Stock Transfer.</div>
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
