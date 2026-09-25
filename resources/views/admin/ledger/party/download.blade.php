<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $party->name ?? 'Party' }} - Account Ledger Statement</title>
    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #2b2b2b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Utility classes */
        .w-100 { width: 100%; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        
        /* Snapkid Brand Colors */
        .color-primary { color: #165b33; }
        .color-gold { color: #d97706; }
        .color-muted { color: #64748b; }
        .color-debit { color: #b91c1c; font-weight: bold; }
        .color-credit { color: #15803d; font-weight: bold; }

        /* Document Container */
        .document-wrapper {
            width: 100%;
        }

        /* Top Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-container {
            width: 25%;
        }

        .logo-img {
            max-height: 56px;
            max-width: 140px;
        }

        .company-info {
            width: 45%;
            padding-left: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #165b33;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
        }

        .company-subtitle {
            font-size: 9px;
            color: #475569;
            line-height: 1.3;
        }

        .statement-title-box {
            width: 30%;
            text-align: right;
            border-left: 3px solid #165b33;
            padding-left: 10px;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #165b33;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
        }

        .doc-subtitle {
            font-size: 9px;
            font-weight: bold;
            color: #d97706;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .doc-meta {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.3;
        }

        /* Brand Accent Divider */
        .brand-divider {
            height: 4px;
            background: #165b33;
            margin-bottom: 12px;
            border-bottom: 2px solid #f59e0b;
        }

        /* Party Details & Period Cards */
        .info-card-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-card-table td {
            vertical-align: top;
            padding: 0;
        }

        .card-box {
            background-color: #f8faf9;
            border: 1px solid #d1e7dd;
            border-radius: 4px;
            padding: 8px 10px;
        }

        .card-heading {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #165b33;
            border-bottom: 1px solid #d1e7dd;
            padding-bottom: 4px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .party-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .badge-type {
            display: inline-block;
            background-color: #e8f5e9;
            color: #165b33;
            border: 1px solid #a3cfbb;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            padding: 1px 6px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .meta-row {
            font-size: 9px;
            color: #334155;
            margin-bottom: 2px;
        }

        /* Financial Metric Ribbon */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .metric-cell {
            padding: 7px 8px;
            border: 1px solid #d1e7dd;
            text-align: center;
            background: #fbfdfb;
        }

        .metric-cell-primary {
            background: #165b33;
            color: #ffffff;
            border: 1px solid #0e3d22;
        }

        .metric-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 3px;
            display: block;
        }

        .metric-cell-primary .metric-label {
            color: #fef08a;
        }

        .metric-value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        .metric-cell-primary .metric-value {
            color: #ffffff;
            font-size: 13px;
        }

        .metric-tag {
            font-size: 8px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
            margin-left: 2px;
        }

        .tag-cr { background: #dcfce7; color: #166534; }
        .tag-dr { background: #fee2e2; color: #991b1b; }
        .metric-cell-primary .tag-cr { background: #fef08a; color: #854d0e; }
        .metric-cell-primary .tag-dr { background: #fca5a5; color: #7f1d1d; }

        /* Ledger Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .ledger-table th {
            background-color: #165b33;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #165b33;
            letter-spacing: 0.4px;
        }

        .ledger-table th.border-right-light {
            border-right: 1px solid #237a47;
        }

        .ledger-table td {
            padding: 5px 6px;
            font-size: 8.5px;
            border-bottom: 1px solid #e2ece5;
            border-left: 1px solid #f1f5f2;
            border-right: 1px solid #f1f5f2;
            vertical-align: middle;
        }

        .ledger-table tr:nth-child(even) td {
            background-color: #fbfdfb;
        }

        .ledger-table .row-opening td {
            background-color: #f4f9f4;
            font-style: italic;
            font-weight: bold;
            color: #334155;
            border-bottom: 1.5px solid #d1e7dd;
        }

        .totals-row td {
            background-color: #eaf4ed !important;
            font-size: 9px;
            font-weight: bold;
            padding: 7px 6px;
            border-top: 2px solid #165b33 !important;
            border-bottom: 2px solid #165b33 !important;
            color: #0f172a;
        }

        .type-pill {
            font-size: 7.5px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            background: #eef2f6;
            color: #334155;
            display: inline-block;
            text-transform: uppercase;
        }

        /* Signatures & Footer */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .sign-table td {
            vertical-align: bottom;
            font-size: 8.5px;
        }

        .sign-line {
            border-top: 1px dashed #64748b;
            width: 170px;
            margin-bottom: 4px;
        }

        .footer-note {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 7.5px;
            color: #64748b;
            text-align: center;
        }

        .footer-note span {
            color: #165b33;
            font-weight: bold;
        }
    </style>
</head>
<body>
@php
    $generalSettings = \App\Models\GeneralSettings::first();
    $companyName = $generalSettings->website_name ?? 'SNAPKID';
    $companyAddress = $generalSettings->address ?? 'K-43 SECTOR D-1(P), TRONIKA CITY GHAZIABAD-201102';
    $companyPhone = $generalSettings->phone ?? '9625927505';
    $companyEmail = $generalSettings->email ?? 'info@snapkidindia.com';

    // High-resolution Logo base64 resolution
    $logoBase64 = '';
    $snapkidBrand = \App\Models\Brand::where('name', 'SNAPKID')->first();
    if ($snapkidBrand && $snapkidBrand->logo) {
        $brandLogoPath = public_path('assets/brands/' . $snapkidBrand->logo);
        if (file_exists($brandLogoPath)) {
            $mime = strtolower(pathinfo($brandLogoPath, PATHINFO_EXTENSION));
            $mimeType = $mime === 'png' ? 'image/png' : 'image/jpeg';
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($brandLogoPath));
        }
    }
    if (!$logoBase64 && $generalSettings && $generalSettings->logo) {
        $gsLogoPath = public_path('assets/general-settings-image/' . $generalSettings->logo);
        if (file_exists($gsLogoPath)) {
            $mime = strtolower(pathinfo($gsLogoPath, PATHINFO_EXTENSION));
            $mimeType = $mime === 'png' ? 'image/png' : 'image/jpeg';
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($gsLogoPath));
        }
    }

    $totalDebit = isset($transactions) ? (float) $transactions->sum('debit') : 0;
    $totalCredit = isset($transactions) ? (float) $transactions->sum('credit') : 0;
    $netBalance = (float) ($party->balance ?? 0);
    $closingTag = $netBalance >= 0 ? 'CR' : 'DR';
    $openingTag = $openingBalAmount >= 0 ? 'CR' : 'DR';
@endphp

<div class="document-wrapper">

    {{-- HEADER SECTION LIKE IN SALES ORDER PDFS --}}
    <table class="header-table">
        <tr>
            <td class="logo-container">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Snapkid">
                @else
                    <div style="font-size: 22px; font-weight: bold; color: #165b33; letter-spacing: 1px;">SNAPKID</div>
                @endif
            </td>
            <td class="company-info">
                <div class="company-name">{{ strtoupper($companyName) }}</div>
                <div class="company-subtitle">
                    {{ $companyAddress }}<br>
                    <strong>Phone:</strong> {{ $companyPhone }} &nbsp;|&nbsp; <strong>Email:</strong> {{ $companyEmail }}
                </div>
            </td>
            <td class="statement-title-box">
                <div class="doc-title">ACCOUNT LEDGER</div>
                <div class="doc-subtitle">FINANCIAL STATEMENT</div>
                <div class="doc-meta">
                    <strong>Date:</strong> {{ date('d M Y, h:i A') }}<br>
                    <strong>Ref No:</strong> LGR-{{ strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $type), 0, 3)) }}-{{ $party->id }}-{{ date('Ymd') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- BRAND ACCENT STRIPE --}}
    <div class="brand-divider"></div>

    {{-- PARTY DETAILS & STATEMENT INFO --}}
    <table class="info-card-table">
        <tr>
            <td style="width: 58%; padding-right: 6px;">
                <div class="card-box">
                    <div class="card-heading">Party Account Details</div>
                    <div class="party-name">{{ $party->name ?? 'N/A' }}</div>
                    <div><span class="badge-type">{{ str_replace('_', ' ', strtoupper($type)) }}</span></div>
                    <div class="meta-row"><strong>Address:</strong> {{ $party->address ?? 'Not Specified' }}</div>
                    <div class="meta-row"><strong>Contact:</strong> {{ $party->phone ?? '-' }}</div>
                </div>
            </td>
            <td style="width: 42%; padding-left: 6px;">
                <div class="card-box">
                    <div class="card-heading">Statement Summary</div>
                    <div class="meta-row">
                        <strong>Period:</strong> 
                        @if($startDate && $endDate)
                            {{ date('d-M-Y', strtotime($startDate)) }} to {{ date('d-M-Y', strtotime($endDate)) }}
                        @elseif($startDate)
                            From {{ date('d-M-Y', strtotime($startDate)) }}
                        @elseif($endDate)
                            Up to {{ date('d-M-Y', strtotime($endDate)) }}
                        @else
                            All Transactions to Date
                        @endif
                    </div>
                    <div class="meta-row"><strong>Account Type:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}</div>
                    <div class="meta-row"><strong>Opening Position:</strong> ₹ {{ number_format(abs($openingBalAmount), 2) }} {{ $openingTag }}</div>
                    <div class="meta-row"><strong>Current Closing:</strong> ₹ {{ number_format(abs($netBalance), 2) }} {{ $closingTag }}</div>
                    @php
                        $filterItems = [];
                        if(request('transaction_type')) $filterItems[] = 'Type: ' . request('transaction_type');
                        if(request('adjustment_type')) $filterItems[] = 'Adj: ' . ucfirst(request('adjustment_type'));
                        if(request('ref_no')) $filterItems[] = 'Ref: ' . request('ref_no');
                        if(request('debit_value')) $filterItems[] = 'Dr: ₹' . request('debit_value');
                        if(request('credit_value')) $filterItems[] = 'Cr: ₹' . request('credit_value');
                    @endphp
                    @if(count($filterItems) > 0)
                        <div class="meta-row" style="color: #165b33; font-weight: bold; margin-top: 3px;">
                            <strong>Active Filters:</strong> {{ implode(' | ', $filterItems) }}
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if(isset($viewMode) && $viewMode === 'party_wise' && isset($groupedLedgers))
        {{-- PARTY-WISE GROUPED MODE (FOR SALES AGENTS) --}}
        @forelse($groupedLedgers as $ledger)
            @php
                $grpOpenBal = (float) $ledger->opening_balance;
                $grpCloseBal = (float) $ledger->closing_balance;
                $grpTotalDebit = (float) $ledger->transactions->sum('debit');
                $grpTotalCredit = (float) $ledger->transactions->sum('credit');
            @endphp

            <div style="margin-top: 15px; margin-bottom: 6px; padding: 6px 10px; background: #f0f7f2; border-left: 4px solid #165b33; font-weight: bold; font-size: 11px; color: #165b33;">
                Customer: {{ $ledger->shop->name }}
            </div>

            <table class="metrics-table">
                <tr>
                    <td class="metric-cell" style="width: 25%;">
                        <span class="metric-label">Opening Balance</span>
                        <span class="metric-value">₹ {{ number_format(abs($grpOpenBal), 2) }}</span>
                        <span class="metric-tag {{ $grpOpenBal >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $grpOpenBal >= 0 ? 'CR' : 'DR' }}</span>
                    </td>
                    <td class="metric-cell" style="width: 25%;">
                        <span class="metric-label">Total Debit (Dr)</span>
                        <span class="metric-value color-debit">₹ {{ number_format($grpTotalDebit, 2) }}</span>
                    </td>
                    <td class="metric-cell" style="width: 25%;">
                        <span class="metric-label">Total Credit (Cr)</span>
                        <span class="metric-value color-credit">₹ {{ number_format($grpTotalCredit, 2) }}</span>
                    </td>
                    <td class="metric-cell metric-cell-primary" style="width: 25%;">
                        <span class="metric-label">Closing Balance</span>
                        <span class="metric-value">₹ {{ number_format(abs($grpCloseBal), 2) }}</span>
                        <span class="metric-tag {{ $grpCloseBal >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $grpCloseBal >= 0 ? 'CR' : 'DR' }}</span>
                    </td>
                </tr>
            </table>

            <table class="ledger-table">
                <thead>
                    <tr>
                        <th width="10%" class="text-center border-right-light">Date</th>
                        <th width="12%" class="text-center border-right-light">Type</th>
                        <th width="14%" class="text-left border-right-light">Ref / Voucher</th>
                        <th class="text-left border-right-light">Description</th>
                        <th width="12%" class="text-right border-right-light">Debit (₹)</th>
                        <th width="12%" class="text-right border-right-light">Credit (₹)</th>
                        <th width="14%" class="text-right">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-opening">
                        <td class="text-center">{{ $startDate ? date('d-M-y', strtotime($startDate)) : '-' }}</td>
                        <td class="text-center"><span class="type-pill">OPENING</span></td>
                        <td>-</td>
                        <td>Opening Balance Brought Forward</td>
                        <td class="text-right">{{ $grpOpenBal < 0 ? '₹ ' . number_format(abs($grpOpenBal), 2) : '-' }}</td>
                        <td class="text-right">{{ $grpOpenBal >= 0 ? '₹ ' . number_format(abs($grpOpenBal), 2) : '-' }}</td>
                        <td class="text-right font-bold">
                            ₹ {{ number_format(abs($grpOpenBal), 2) }} 
                            <span class="metric-tag {{ $grpOpenBal >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $grpOpenBal >= 0 ? 'CR' : 'DR' }}</span>
                        </td>
                    </tr>
                    @forelse($ledger->transactions as $tx)
                        @php $bal = (float) $tx->running_balance; @endphp
                        <tr>
                            <td class="text-center">{{ $tx->date ? date('d-M-y', strtotime($tx->date)) : '-' }}</td>
                            <td class="text-center"><span class="type-pill">{{ $tx->type ?? 'Tx' }}</span></td>
                            <td class="font-bold">{{ $tx->ref ?? '-' }}</td>
                            <td>{{ $tx->description ?? '-' }}</td>
                            <td class="text-right color-debit">
                                {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                            </td>
                            <td class="text-right color-credit">
                                {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                            </td>
                            <td class="text-right font-bold">
                                ₹ {{ number_format(abs($bal), 2) }} 
                                <span class="metric-tag {{ $bal >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $bal >= 0 ? 'CR' : 'DR' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">No transactions recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="4" class="text-right text-uppercase font-bold">Total Activity:</td>
                        <td class="text-right color-debit">₹ {{ number_format($grpTotalDebit, 2) }}</td>
                        <td class="text-right color-credit">₹ {{ number_format($grpTotalCredit, 2) }}</td>
                        <td class="text-right font-bold" style="color: #165b33;">
                            ₹ {{ number_format(abs($grpCloseBal), 2) }} {{ $grpCloseBal >= 0 ? 'CR' : 'DR' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @empty
            <div class="text-center" style="padding: 25px; color: #64748b;">No customer ledgers found.</div>
        @endforelse

    @else
        {{-- STANDARD / CONSOLIDATED PARTY LEDGER --}}

        {{-- EXECUTIVE 4-PILLAR FINANCIAL METRICS RIBBON --}}
        <table class="metrics-table">
            <tr>
                <td class="metric-cell" style="width: 25%;">
                    <span class="metric-label">Opening Balance</span>
                    <span class="metric-value">₹ {{ number_format(abs($openingBalAmount), 2) }}</span>
                    <span class="metric-tag {{ $openingBalAmount >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $openingTag }}</span>
                </td>
                <td class="metric-cell" style="width: 25%;">
                    <span class="metric-label">Total Debit (Charges)</span>
                    <span class="metric-value color-debit">₹ {{ number_format($totalDebit, 2) }}</span>
                </td>
                <td class="metric-cell" style="width: 25%;">
                    <span class="metric-label">Total Credit (Payments)</span>
                    <span class="metric-value color-credit">₹ {{ number_format($totalCredit, 2) }}</span>
                </td>
                <td class="metric-cell metric-cell-primary" style="width: 25%;">
                    <span class="metric-label">Net Closing Balance</span>
                    <span class="metric-value">₹ {{ number_format(abs($netBalance), 2) }}</span>
                    <span class="metric-tag {{ $netBalance >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $closingTag }}</span>
                </td>
            </tr>
        </table>

        {{-- TRANSACTION TABLE --}}
        <table class="ledger-table">
            <thead>
                <tr>
                    <th width="9%" class="text-center border-right-light">Date</th>
                    <th width="12%" class="text-center border-right-light">Type</th>
                    <th width="14%" class="text-left border-right-light">Ref / Voucher</th>
                    <th class="text-left border-right-light">Particulars / Description</th>
                    <th width="12%" class="text-right border-right-light">Debit (Dr)</th>
                    <th width="12%" class="text-right border-right-light">Credit (Cr)</th>
                    <th width="14%" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                {{-- Opening balance initial row --}}
                <tr class="row-opening">
                    <td class="text-center">{{ $startDate ? date('d-M-y', strtotime($startDate)) : '-' }}</td>
                    <td class="text-center"><span class="type-pill">OPENING</span></td>
                    <td>-</td>
                    <td>Opening Balance Brought Forward</td>
                    <td class="text-right">{{ $openingBalAmount < 0 ? '₹ ' . number_format(abs($openingBalAmount), 2) : '-' }}</td>
                    <td class="text-right">{{ $openingBalAmount >= 0 ? '₹ ' . number_format(abs($openingBalAmount), 2) : '-' }}</td>
                    <td class="text-right font-bold">
                        ₹ {{ number_format(abs($openingBalAmount), 2) }} 
                        <span class="metric-tag {{ $openingBalAmount >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $openingTag }}</span>
                    </td>
                </tr>

                @forelse($transactions as $tx)
                    @php $bal = (float) $tx->running_balance; @endphp
                    <tr>
                        <td class="text-center">{{ $tx->date ? date('d-M-y', strtotime($tx->date)) : '-' }}</td>
                        <td class="text-center"><span class="type-pill">{{ $tx->type ?? 'Tx' }}</span></td>
                        <td class="font-bold">{{ $tx->ref ?? '-' }}</td>
                        <td>{{ $tx->description ?? '-' }}</td>
                        <td class="text-right color-debit">
                            {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                        </td>
                        <td class="text-right color-credit">
                            {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                        </td>
                        <td class="text-right font-bold">
                            ₹ {{ number_format(abs($bal), 2) }} 
                            <span class="metric-tag {{ $bal >= 0 ? 'tag-cr' : 'tag-dr' }}">{{ $bal >= 0 ? 'CR' : 'DR' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 18px; color: #64748b;">No transaction entries found for the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="4" class="text-right text-uppercase font-bold">Grand Totals:</td>
                    <td class="text-right color-debit">₹ {{ number_format($totalDebit, 2) }}</td>
                    <td class="text-right color-credit">₹ {{ number_format($totalCredit, 2) }}</td>
                    <td class="text-right font-bold" style="color: #165b33;">
                        ₹ {{ number_format(abs($netBalance), 2) }} {{ $closingTag }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- SIGNATURES & VERIFICATION SECTION --}}
    <table class="sign-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div style="font-size: 8px; color: #64748b; line-height: 1.4;">
                    <strong>Important Accounting Notes:</strong><br>
                    1. Please inspect all statement entries promptly upon receipt.<br>
                    2. Any discrepancies should be notified within 7 days.<br>
                    3. This document is system generated under authorized ERP credentials.
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: bottom;">
                <div class="sign-line" style="margin-left: auto;"></div>
                <div style="font-weight: bold; color: #165b33;">Authorized Signatory</div>
                <div style="font-size: 8px; color: #64748b;">SNAPKID MANAGEMENT</div>
            </td>
        </tr>
    </table>

    {{-- FOOTER NOTE --}}
    <div class="footer-note">
        This is a computer-generated account statement issued by <span>SNAPKID ERP</span>. No physical signature is required. | Generated on {{ date('d-M-Y H:i:s') }}
    </div>

</div>
</body>
</html>