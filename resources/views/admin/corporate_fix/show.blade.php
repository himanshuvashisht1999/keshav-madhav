@extends('admin.layouts.app')

@section('content')
@php
    $nothing = empty($plan['tasks']) && empty($plan['cartons']) && empty($plan['orders']);
    $m = fn($v) => number_format((float) $v, 2);
    $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y, h:i:s A') : '-';
@endphp
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-tools text-primary"></i> Repair old corporate order data</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline mr-1"><i class="fas fa-list mr-1"></i> Corporate Orders</a>
            <a href="{{ route('admin.order-dispatch.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-truck mr-1"></i> Order Dispatch</a>
        </div>
    </div>

    @if(session('fix_error'))
        <div class="alert alert-danger">{{ session('fix_error') }}</div>
    @endif
    @if(session('fix_result'))
        @php $r = session('fix_result'); @endphp
        <div class="alert alert-success">
            <strong>Done.</strong>
            {{ $r['tasks'] }} cutting tasks removed, {{ $r['cartons'] }} cartons marked dispatched, {{ $r['orders'] }} order statuses corrected.
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
                    <tr><td>1. Cutting tasks made twice by a double click (nothing cut on them) and empty tasks of 0 pieces: removed</td><td class="text-right font-weight-bold">{{ count($plan['tasks']) }}</td></tr>
                    <tr><td>2. Cartons that are in a dispatch but show "ready": marked dispatched</td><td class="text-right font-weight-bold">{{ count($plan['cartons']) }}</td></tr>
                    <tr><td>3. Corporate orders whose status is behind what was dispatched: status raised</td><td class="text-right font-weight-bold">{{ count($plan['orders']) }}</td></tr>
                </tbody>
            </table>
            <p class="text-muted mb-3" style="font-size: 13px;">
                Only these tasks, carton statuses and order statuses change. Bills, customer balances, stock and packing are not touched.
                The lists at the bottom are shown only. Every old value is saved, so the change can be undone.
            </p>
            <form method="POST" action="{{ route('admin.corporate-fix.run') }}"
                  onsubmit="if(!confirm('Apply all changes listed on this page?\n\nTake a database backup first (Download DB).')) return false; this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Repairing, please wait...';">
                @csrf
                <a href="{{ route('admin.download-db') }}" class="btn-erp btn-erp-outline mr-2"><i class="fas fa-database mr-1"></i> 1. Download backup</a>
                <button type="submit" class="btn-erp btn-erp-primary px-4"><i class="fas fa-magic mr-1"></i> 2. Repair now</button>
            </form>
        @endif
    </div>

    @if(!empty($plan['tasks']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">1. Cutting tasks to remove ({{ count($plan['tasks']) }})</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead><tr><th>Task</th><th>Order line</th><th>Design</th><th>Cutting master</th><th class="text-right">Pieces</th><th class="text-right">Not cut</th><th>Made on</th><th>Why</th></tr></thead>
                    <tbody>
                        @foreach($plan['tasks'] as $row)
                            <tr>
                                <td>{{ $row['task'] }}</td><td>{{ $row['order_line'] }}</td><td>{{ $row['design'] }}</td><td>{{ $row['unit'] }}</td>
                                <td class="text-right">{{ $row['pieces'] }}</td><td class="text-right">{{ $row['pending'] }}</td><td>{{ $fmt($row['created']) }}</td><td>{{ $row['why'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($plan['cartons']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">2. Cartons to mark dispatched ({{ count($plan['cartons']) }})</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width: 760px;">
                <thead><tr><th>Dispatch</th><th>Carton no</th><th>Shows now</th><th>Changed to "ready" on</th></tr></thead>
                <tbody>
                    @foreach($plan['cartons'] as $row)
                        <tr>
                            <td><a href="{{ route('admin.order-dispatch.view', ['id' => $row['dispatch_id']]) }}" target="_blank">{{ $row['dispatch'] }}</a></td>
                            <td>{{ $row['carton_no'] }}</td><td class="text-danger">{{ (int) $row['status'] === 1 ? 'Ready' : 'Status ' . $row['status'] }}</td><td>{{ $fmt($row['updated_at']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($plan['orders']))
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold">3. Order statuses to correct ({{ count($plan['orders']) }})</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width: 760px;">
                <thead><tr><th>Order</th><th class="text-right">Dispatched / ordered (pcs)</th><th>Status now</th><th>After repair</th></tr></thead>
                <tbody>
                    @foreach($plan['orders'] as $row)
                        <tr>
                            <td>{{ $row['order'] }}</td><td class="text-right">{{ number_format($row['dispatched']) }} / {{ number_format($row['ordered']) }}</td>
                            <td class="text-danger">{{ $row['was'] }}</td><td class="text-success font-weight-bold">{{ $row['now'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($plan['over_assigned']) || !empty($plan['totals']) || !empty($plan['no_stock']))
        <div class="erp-card p-3 mb-2" style="border-left: 4px solid #f0ad4e;">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-eye text-warning mr-1"></i> Please check by hand (shown only, nothing is changed)</h6>
            @if(!empty($plan['totals']))
                <div class="font-weight-bold mt-2">Dispatches whose saved bill differs from their items (the customer balance uses the saved bill; open the dispatch and use Edit Invoice once it is checked)</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 900px;">
                    <thead><tr><th>Dispatch</th><th class="text-right">Saved bill</th><th class="text-right">Items</th><th class="text-right">Bill by items</th><th class="text-right">Difference</th></tr></thead>
                    <tbody>
                        @foreach($plan['totals'] as $row)
                            <tr>
                                <td><a href="{{ route('admin.order-dispatch.view', ['id' => $row['id']]) }}" target="_blank">{{ $row['dispatch'] }}</a></td>
                                <td class="text-right">{{ $m($row['saved']) }}</td><td class="text-right">{{ $m($row['items']) }}</td><td class="text-right">{{ $m($row['by_items']) }}</td>
                                <td class="text-right text-danger">{{ $m($row['difference']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(!empty($plan['over_assigned']))
                <div class="font-weight-bold mt-2">Order lines given to cutting for more pieces than ordered (both tasks already used)</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 900px;">
                    <thead><tr><th>Order line</th><th>Design</th><th class="text-right">Ordered</th><th class="text-right">Given to cutting</th><th>Tasks</th></tr></thead>
                    <tbody>
                        @foreach($plan['over_assigned'] as $row)
                            <tr><td>{{ $row['sku'] }}</td><td>{{ $row['design_number'] }}</td><td class="text-right">{{ $row['total_quantity'] }}</td><td class="text-right">{{ $row['assigned'] }}</td><td>{{ $row['tasks'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(!empty($plan['no_stock']))
                <div class="font-weight-bold mt-2">Purchased boxes waiting for dispatch that are no longer in stock (they cannot be dispatched until the stock is checked)</div>
                <table class="table table-sm table-bordered mb-2" style="max-width: 900px;">
                    <thead><tr><th>Production PO</th><th>Order</th><th>Design</th><th class="text-right">Boxes to dispatch</th><th class="text-right">In stock</th></tr></thead>
                    <tbody>
                        @foreach($plan['no_stock'] as $row)
                            <tr><td>{{ $row['po_number'] }}</td><td>{{ $row['order_sku'] ?? '-' }}</td><td>{{ $row['design_number'] ?? '-' }}</td><td class="text-right">{{ $row['box_quantity'] }}</td><td class="text-right text-danger">{{ $row['in_stock'] }}</td></tr>
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
