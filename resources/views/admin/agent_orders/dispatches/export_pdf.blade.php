<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispatches Export</title>
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
            width: 50%;
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
                    <div class="doc-title">Dispatch Logs Report</div>
                    <div class="doc-sub">Dispatch Management System</div>
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
                <div class="s-label">Total Dispatches</div>
                <div class="s-value">{{ number_format($dispatches->count()) }}</div>
            </td>
            <td>
                <div class="s-label">Total Grand Total</div>
                <div class="s-value">Rs. {{ number_format($totalGrandTotal, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- DATA TABLE --}}
    <table class="data-tbl">
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="12%">Dispatch ID</th>
                <th width="20%">Party Name</th>
                <th width="10%">Party Type</th>
                <th width="15%">Agent</th>
                <th width="15%">Grand Total (Rs.)</th>
                <th width="10%">Bill No</th>
                <th width="15%">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispatches as $i => $dispatch)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>#DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>{{ $dispatch->party_type === 'vendor' ? ($dispatch->vendor->name ?? 'N/A') : ($dispatch->shop->name ?? 'N/A') }}</td>
                    <td>{{ ucfirst($dispatch->party_type ?? 'N/A') }}</td>
                    <td>{{ $dispatch->agent->name ?? 'Direct' }}</td>
                    <td>{{ number_format($dispatch->grand_total, 2) }}</td>
                    <td>{{ $dispatch->bill_no ?? '-' }}</td>
                    <td>{{ $dispatch->dispatch_date ? \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#94a3b8;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="5" style="text-align:right;">TOTAL</td>
                <td>Rs. {{ number_format($totalGrandTotal, 2) }}</td>
                <td colspan="2" style="text-align:center;">{{ $dispatches->count() }} dispatches</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dispatch Logs Export &nbsp;·&nbsp; {{ now()->format('d M Y, h:i A') }} &nbsp;·&nbsp; Total {{ $dispatches->count() }} dispatches
    </div>

</body>
</html>
