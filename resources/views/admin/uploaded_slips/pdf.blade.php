<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Slip #{{ $slip->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        h2, h3, h4 {
            margin: 0 0 6px 0;
        }

        .section {
            margin-bottom: 18px;
        }

        .box {
            border: 1px solid #333;
            padding: 10px;
        }

        .row {
            width: 100%;
            margin-bottom: 6px;
        }

        .col {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .label {
            font-size: 10px;
            color: #555;
        }

        .value {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 10px;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<div class="section">
    <h2>Production Slip</h2>
    <!-- <div class="label">Slip ID</div>
    <div class="value">#{{ $slip->id }}</div> -->
</div>

{{-- ================= SLIP SUMMARY ================= --}}
<div class="section box">
    <!-- <div class="row">
        <div class="col">
            <div class="label">Slip Type</div>
            <div class="value">
                @if($slip->save_type == 1) Lot / Rolls
                @elseif($slip->save_type == 2) Printing
                @else Other
                @endif
            </div>
        </div>

        <div class="col">
            <div class="label">Status</div>
            <div class="value">
                {{ $slip->status == 1 ? 'Processed' : 'Pending' }}
            </div>
        </div>
    </div> -->

    <div class="row">
        <div class="col">
            <div class="label">Slip ID</div>
            <div class="value">#{{$slip->id }}</div>
        </div>

        <div class="col">
            <div class="label">From Stage</div>
            <div class="value">{{ $slip->fromStage?->name ?? '-' }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="label">Unit</div>
            <div class="value">{{ $slip->getUnitMaster?->name }}</div>
        </div>

        <div class="col">
            <div class="label">Warehouse</div>
            <div class="value">
                {{ $slip->getUnitMaster?->masterFabricWarehouse?->cutting_master_name }}
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     🟢 TYPE 1 → LOT / ROLLS
===================================================== --}}
@if($slip->save_type == 1 && $lot)
<div class="section">
    <h3>Lot & Design Details</h3>

    <div class="box">
        <div class="row">
            <div class="col">
                <div class="label">Lot No</div>
                <div class="value">{{ $lot->lot_no }}</div>
            </div>

            <div class="col">
                <div class="label">Production Date</div>
                <div class="value">{{ getformatDateTime($lot->production_datetime) }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Order No</div>
                <div class="value">{{ $lot->orderMain?->sku }}</div>
            </div>

            <div class="col">
                <div class="label">Design</div>
                <div class="value">{{ $lot->orderProductSet?->design_number }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Fabric</div>
                <div class="value">{{ $lot->orderProductSet?->fabric?->name }}</div>
            </div>

            <div class="col">
                <div class="label">Color</div>
                <div class="value">{{ $lot->orderProductSet?->colors?->name }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Pattern</div>
                <div class="value">{{ $lot->orderProductSet?->master_design_pattern?->name }}</div>
            </div>

            <div class="col">
                <div class="label">Fitting</div>
                <div class="value">{{ $lot->orderProductSet?->master_product_fitting?->name }}</div>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <h3>Rolls & Size Allocation</h3>

    <table>
        <thead>
            <tr>
                <th>Roll No</th>
                <th>Meter Used</th>
                <th>Size</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rolls as $roll)
                @foreach($roll->fabricRollAssigningsDetail as $size)
                    <tr>
                        <td>{{ $roll->roll_no }}</td>
                        <td>{{ $roll->meter }}</td>
                        <td>{{ $size->size }}</td>
                        <td>{{ $size->quantity }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- =====================================================
     🔵 TYPE 2 → PRINTING
===================================================== --}}
@if($slip->save_type == 2 && $printing)
<div class="section">
    <h3>Printing Details</h3>

    <div class="box">
        <div class="row">
            <div class="col">
                <div class="label">Lot No</div>
                <div class="value">{{ $printing->lot_no }}</div>
            </div>

            <div class="col">
                <div class="label">Production Date</div>
                <div class="value">{{ getformatDateTime($printing->production_datetime) }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">From Stage</div>
                <div class="value">{{ $printing->from_stage?->name }}</div>
            </div>

            <div class="col">
                <div class="label">To Stage</div>
                <div class="value">{{ $printing->to_stage?->name }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Total Quantity</div>
                <div class="value">{{ $printing->quantity }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Size</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($printing_sizes as $row)
                <tr>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- =====================================================
     🟠 TYPE 3 → OTHER
===================================================== --}}
@if($slip->save_type == 3 && isset($stage_transaction))
<div class="section">
    <h3>Stage Movement Details</h3>

    <div class="box">
        <div class="row">
            <div class="col">
                <div class="label">Lot No</div>
                <div class="value">{{ $stage_transaction->lot_no }}</div>
            </div>

            <div class="col">
                <div class="label">Production Date</div>
                <div class="value">{{ getformatDateTime($stage_transaction->production_datetime) }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">From Stage</div>
                <div class="value">{{ $stage_transaction->from_stage?->name }}</div>
            </div>

            <div class="col">
                <div class="label">To Stage</div>
                <div class="value">{{ $stage_transaction->to_stage?->name }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Total Quantity</div>
                <div class="value">{{ $stage_transaction->quantity }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Size</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stage_sizes as $row)
                <tr>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    Generated on {{ now()->format('d M Y H:i') }} | Production Management System
</div>

</body>
</html>
