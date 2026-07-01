<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Agent Orders Export</title>
    <style>
        @page { margin: 1cm 1.2cm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #1e293b;
            margin: 0;
        }

        /* ── Header ── */
        .doc-header {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .doc-header table { width: 100%; border-collapse: collapse; }
        .doc-title  { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .doc-sub    { font-size: 8px; color: #64748b; margin-top: 2px; }
        .doc-right  { text-align: right; }
        .doc-date   { font-size: 8px; color: #64748b; }

        /* ── Active filters ── */
        .filters-bar {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 4px 8px;
            margin-bottom: 10px;
            font-size: 8px;
            color: #475569;
            border-radius: 3px;
        }
        .filters-bar strong { color: #0f172a; }

        /* ── Summary strip ── */
        .summary-strip {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary-strip td {
            width: 33%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .summary-strip .s-label { font-size: 7px; text-transform: uppercase; color: #94a3b8; font-weight: bold; }
        .summary-strip .s-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        /* ── Data table ── */
        table.data-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-tbl thead tr {
            background: #1e293b;
            color: #fff;
        }
        table.data-tbl th {
            padding: 5px 5px;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
            border: 1px solid #334155;
        }
        table.data-tbl td {
            padding: 4px 5px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            vertical-align: middle;
        }
        table.data-tbl tr:nth-child(even) td { background: #f8fafc; }

        .badge-pending    { background:#fef3c7; color:#92400e; padding:1px 5px; border-radius:3px; font-size:7px; font-weight:bold; }
        .badge-delayed    { background:#fee2e2; color:#991b1b; padding:1px 5px; border-radius:3px; font-size:7px; font-weight:bold; }
        .badge-dispatched { background:#d1fae5; color:#065f46; padding:1px 5px; border-radius:3px; font-size:7px; font-weight:bold; }

        /* ── Totals row ── */
        .totals-row td {
            background: #fef9c3 !important;
            font-weight: bold;
            border-top: 2px solid #ca8a04;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 8px;
            left: 0; right: 0;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="doc-header">
        <table>
            <tr>
                <td>
                    <div class="doc-title">Agent Orders Report</div>
                    <div class="doc-sub">Sales Agent Order Management System</div>
                </td>
                <td class="doc-right">
                    <div class="doc-date">Generated: {{ now()->format('d M Y, h:i A') }}</div>
                    @if(!empty($filters))
                        <div class="doc-date" style="margin-top:3px; color:#6366f1;">Filtered Report</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ACTIVE FILTERS --}}
    @if(!empty($filters))
        <div class="filters-bar">
            <strong>Active Filters:</strong>
            @foreach($filters as $key => $val)
                <span style="margin-left:8px;">{{ ucfirst(str_replace('_', ' ', $key)) }}: <strong>{{ $val }}</strong></span>
            @endforeach
        </div>
    @endif

    {{-- SUMMARY STRIP --}}
    <table class="summary-strip">
        <tr>
            <td>
                <div class="s-label">Total Orders</div>
                <div class="s-value">{{ number_format($totals->total_orders) }}</div>
            </td>
            <td>
                <div class="s-label">Total Pieces</div>
                <div class="s-value">{{ number_format($totals->total_pieces) }}</div>
            </td>
            <td>
                <div class="s-label">Total Grand Total</div>
                <div class="s-value">Rs. {{ number_format($totals->total_grand_total, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- DATA TABLE --}}
    <table class="data-tbl">
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="9%">Order ID</th>
                <th width="10%">Order Date</th>
                <th width="14%">Agent</th>
                <th width="16%">Shop / Party</th>
                <th width="8%">Sale Type</th>
                <th width="8%">Total Pcs</th>
                <th width="11%">Grand Total (Rs.)</th>
                <th width="8%">Status</th>
                <th width="13%">Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $o)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>#ORD-{{ str_pad($o->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($o->order_date)->format('d M Y') }}</td>
                    <td>{{ $o->agent_name }}</td>
                    <td>{{ $o->shop_name }}</td>
                    <td>{{ ucfirst($o->sale_type ?? 'item') }}</td>
                    <td>{{ number_format($o->total_qty, $o->sale_type == 'fabric' ? 2 : 0) }}</td>
                    <td>{{ number_format($o->grand_total, 2) }}</td>
                    <td>
                        @if($o->status == 'dispatched')
                            <span class="badge-dispatched">DISPATCHED</span>
                        @elseif($o->status == 'delayed')
                            <span class="badge-delayed">DELAYED</span>
                        @else
                            <span class="badge-pending">PENDING</span>
                        @endif
                    </td>
                    <td>{{ $o->expected_dispatch_date ? \Carbon\Carbon::parse($o->expected_dispatch_date)->format('d M Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:20px; color:#94a3b8;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="6" style="text-align:right;">TOTAL</td>
                <td>{{ number_format($totals->total_pieces) }}</td>
                <td>Rs. {{ number_format($totals->total_grand_total, 2) }}</td>
                <td colspan="2" style="text-align:center;">{{ $totals->total_orders }} orders</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Agent Orders Export &nbsp;·&nbsp; {{ now()->format('d M Y, h:i A') }} &nbsp;·&nbsp; Total {{ $totals->total_orders }} orders
    </div>

</body>
</html>
