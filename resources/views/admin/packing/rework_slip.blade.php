<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rework Slip - {{ $isBulk ? 'Bulk Batch' : 'Lot #' . ($items->first()->lot_no ?? $items->first()->id) }}</title>
    <style>
        @page {
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }
        .slip-box {
            border: 2px solid #111827;
            padding: 12px 14px;
            background: #ffffff;
        }
        /* Top Header */
        .top-header {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #111827;
            margin-bottom: 12px;
        }
        .top-header td {
            padding-bottom: 10px;
        }
        .company-title {
            font-size: 22px;
            font-weight: 900;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.1;
        }
        .company-sub {
            font-size: 9.5px;
            color: #4b5563;
            margin-top: 3px;
        }
        .title-badge {
            background: #b91c1c;
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
            padding: 5px 14px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .slip-ref-text {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            margin-top: 6px;
        }

        /* Unit & Assignment Details Card */
        .assignment-card {
            width: 100%;
            border: 2px solid #b91c1c;
            background: #fef2f2;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .assignment-card td {
            padding: 9px 12px;
            vertical-align: top;
        }
        .label-sm {
            font-size: 10px;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            display: block;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .value-lg {
            font-size: 18px;
            font-weight: 900;
            color: #b91c1c;
            line-height: 1.2;
        }
        .value-md {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
        }

        /* Goods Details Table */
        .goods-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .goods-table th {
            background: #111827;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 7px 5px;
            border: 1px solid #111827;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .goods-table td {
            padding: 7px 5px;
            border: 1px solid #9ca3af;
            font-size: 11.5px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
        }
        .goods-table tr:nth-child(even) td {
            background: #f9fafb;
        }
        .cell-lot {
            font-size: 14px;
            font-weight: 900;
            color: #111827;
        }
        .cell-design {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            text-align: left;
            padding-left: 8px !important;
        }
        .cell-qty {
            font-size: 14px;
            font-weight: 900;
            color: #b91c1c;
            background: #fee2e2 !important;
            white-space: nowrap;
        }

        /* Total Banner Table */
        .total-banner-table {
            width: 100%;
            border-collapse: collapse;
            background: #111827;
            margin-bottom: 12px;
        }
        .total-banner-text {
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
            vertical-align: middle;
        }
        .total-banner-qty {
            padding: 9px 12px;
            font-size: 20px;
            font-weight: 900;
            color: #fef08a;
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Defect Remarks Table */
        .defect-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px dashed #b91c1c;
            background: #fffbeb;
            margin-bottom: 12px;
        }
        .defect-table td {
            padding: 8px 12px;
            vertical-align: top;
        }
        .defect-title {
            font-size: 10.5px;
            font-weight: 900;
            color: #b91c1c;
            text-transform: uppercase;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .defect-text {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
        }

        /* Notice Table */
        .notice-table {
            width: 100%;
            border-collapse: collapse;
            background: #f3f4f6;
            border-left: 4px solid #b91c1c;
            margin-bottom: 15px;
        }
        .notice-table td {
            padding: 7px 12px;
            font-size: 10px;
            color: #374151;
            line-height: 1.4;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 25px;
        }
        .signature-line {
            border-top: 1.5px solid #111827;
            margin-top: 40px;
            margin-bottom: 4px;
        }
        .signature-label {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            color: #111827;
        }
        .signature-sub {
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    @php
        $logoBase64 = '';
        $logoPath = public_path('images/snapkid_logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $first = $items->first();
        $targetStage = $first->assignedStage ? $first->assignedStage->name : ($first->responsibleStage ? $first->responsibleStage->name : '-');
        $targetUnit = $first->assignedUnit ? $first->assignedUnit->name : ($first->responsibleUnit ? $first->responsibleUnit->name : '-');
        $storageLoc = ($first->rack && $first->rack->storeroom ? $first->rack->storeroom->name . ' / ' : '') . ($first->rack ? $first->rack->name : 'N/A');
        $totalPieces = $items->sum('quantity');
    @endphp

    <div class="slip-box">
        <!-- HEADER -->
        <table class="top-header">
            <tr>
                <td style="width: 22%; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="SnapKid" style="max-height: 52px; max-width: 130px; object-fit: contain;">
                    @else
                        <div class="company-title">SNAPKID</div>
                    @endif
                </td>
                <td style="width: 48%; vertical-align: middle; padding-left: 10px;">
                    <div class="company-title">{{ $general_setting->website_name ?? 'SNAPKID' }}</div>
                    <div class="company-sub">
                        {{ $general_setting->address ?? 'Garment Manufacturing & Quality Unit' }}
                        @if(!empty($general_setting->phone))
                            | Phone: {{ $general_setting->phone }}
                        @endif
                    </div>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: middle;">
                    <div class="title-badge">REWORK ISSUE SLIP</div>
                    <div class="slip-ref-text">
                        SLIP NO: <span style="font-family: monospace; font-size: 14px; color: #b91c1c;">RWK-{{ $isBulk ? 'BATCH' : str_pad($first->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- UNIT PERSON & ASSIGNMENT DETAILS -->
        <table class="assignment-card">
            <tr>
                <td style="width: 50%; border-right: 1px dashed #f87171;">
                    <span class="label-sm">ASSIGNED UNIT / PERSON:</span>
                    <div class="value-lg">{{ $isBulk ? 'Multiple Units (See Table)' : $targetUnit }}</div>

                    <div style="margin-top: 8px;">
                        <span class="label-sm">REWORK STAGE:</span>
                        <div class="value-md" style="color: #b91c1c;">{{ $isBulk ? 'As per table' : $targetStage }}</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <span class="label-sm">PICK UP FROM RACK LOCATION:</span>
                    <div class="value-md" style="color: #d97706; font-size: 14px;">
                        {{ $isBulk ? 'As per table' : $storageLoc }}
                    </div>

                    <div style="margin-top: 8px;">
                        <span class="label-sm">ISSUE DATE & TIME:</span>
                        <div class="value-md">
                            {{ $first->assigned_at ? date('d M Y, h:i A', strtotime($first->assigned_at)) : date('d M Y, h:i A') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- GOODS / ITEM TABLE -->
        <table class="goods-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 18%;">LOT NO</th>
                    <th style="width: {{ $isBulk ? '27%' : '35%' }}; text-align: left; padding-left: 8px;">DESIGN & GARMENT</th>
                    <th style="width: 14%;">COLOR</th>
                    <th style="width: 10%;">SIZE</th>
                    @if($isBulk)
                        <th style="width: 12%;">RACK LOCATION</th>
                    @endif
                    <th style="width: 18%;">QUANTITY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                    @php
                        $designNo = $item->product ? ($item->product->design_number ?: 'N/A') : '-';
                        $seriesName = ($item->product && $item->product->series) ? $item->product->series->name : '';
                        $colorName = $item->color ? $item->color->name : '-';
                        $sizeVal = $item->size ? $item->size->size : '-';
                        $rackName = ($item->rack && $item->rack->storeroom ? $item->rack->storeroom->name . ' / ' : '') . ($item->rack ? $item->rack->name : '-');
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="cell-lot">{{ $item->lot_no ?: '-' }}</td>
                        <td class="cell-design">
                            <strong>{{ $designNo }}</strong>
                            @if($seriesName)
                                <div style="font-size: 9.5px; color: #4b5563; font-weight: normal;">{{ $seriesName }}</div>
                            @endif
                        </td>
                        <td style="font-weight: bold;">{{ $colorName }}</td>
                        <td style="font-weight: 900; font-size: 13px;">{{ $sizeVal }}</td>
                        @if($isBulk)
                            <td style="font-size: 9.5px; font-weight: bold;">{{ $rackName }}</td>
                        @endif
                        <td class="cell-qty">{{ $item->quantity }} Pcs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTAL QUANTITY BANNER (AS TABLE - CANNOT OVERFLOW) -->
        <table class="total-banner-table">
            <tr>
                <td class="total-banner-text">
                    TOTAL REWORK PIECES
                </td>
                <td class="total-banner-qty">
                    {{ $totalPieces }} PCS
                </td>
            </tr>
        </table>

        <!-- DEFECT REMARKS (AS TABLE - CANNOT OVERFLOW) -->
        <table class="defect-table">
            <tr>
                <td>
                    <div class="defect-title">DEFECT / REPAIR WORK TO DO:</div>
                    <div class="defect-text">
                        @php
                            $allRemarks = $items->pluck('remarks')->filter()->unique()->implode(' | ');
                        @endphp
                        {{ $allRemarks ?: 'Defect rectification required. Please check and fix according to standard.' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- SIMPLE INSTRUCTIONS (AS TABLE - CANNOT OVERFLOW) -->
        <table class="notice-table">
            <tr>
                <td>
                    <strong>NOTE:</strong> Please count all pieces when taking delivery. After completing the rework, return this slip along with the finished pieces to the packing department.
                </td>
            </tr>
        </table>

        <!-- SIGNATURES -->
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-label">ISSUED BY</div>
                    <div class="signature-sub">Packing / Store In-Charge</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-label">RECEIVED BY</div>
                    <div class="signature-sub">Unit Person / Worker Signature</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
