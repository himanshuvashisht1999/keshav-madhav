<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Slip Details</title>

    <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --primary: #667eea;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 90px;
        }

        /* Header */
        .app-header {
            background: var(--bg-gradient);
            padding: 16px 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 20px 24px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
            border-top: 1px solid #f3f4f6;
            z-index: 1000;
        }

        .nav-item {
            text-decoration: none;
            color: #9ca3af;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            font-size: 11px;
            transition: all 0.3s;
        }

        .nav-item i {
            font-size: 22px;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            text-decoration: none;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        /* Content */
        .app-content {
            padding: 20px;
            padding-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Image */
        .slip-image-container {
            background: #f9fafb;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            position: relative;
        }

        .slip-image {
            width: 100%;
            display: block;
            cursor: zoom-in;
        }

        .zoom-hint {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 14px;
            background: #f9fafb;
            border-radius: 12px;
            align-items: center;
        }

        .info-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        .status-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .status-approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }

        th {
            background: #f9fafb;
            font-weight: 700;
            color: #374151;
            font-size: 13px;
        }

        td {
            font-size: 14px;
            color: #6b7280;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Roll/Carton Cards */
        .item-card {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            border-left: 4px solid var(--primary);
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-weight: 700;
            color: #1f2937;
        }

        .box-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .box-badge {
            background: white;
            border: 2px solid #e5e7eb;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .subsection-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .image-modal.active {
            display: flex;
        }

        .modal-image {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            color: #333;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 480px) {
            .app-header {
                padding: 16px;
            }

            .app-content {
                padding: 16px;
            }
        }

        @supports
        (padding: max(0))
            {
            .app-header {
                padding-top: max(20px, env(safe-area-inset-top));
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="app-header">
        <div class="header-left">
            <a href="{{ route('unit.history') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="page-title">Summary</div>
        </div>
        <a href="{{ route('unit.logout') }}" style="color: white; font-size: 20px;">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <!-- Content -->
    <div class="app-content">
        {{-- SLIP IMAGE --}}
        <div class="card">
            <div class="slip-image-container">
                <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" alt="Production Slip"
                    class="slip-image" id="slipImage">
                <div class="zoom-hint">
                    <i class="fas fa-search-plus"></i> Tap to zoom
                </div>
            </div>
        </div>

        {{-- SLIP SUMMARY --}}
        <div class="card">
            <div class="section-title">
                <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                Slip Information
            </div>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Slip ID</span>
                    <span class="info-value">#{{ $slip->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">From Stage</span>
                    <span class="info-value">{{ $slip->fromStage->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Unit</span>
                    <span
                        class="info-value">{{ $slip->getUnitMaster->name ?? $slip->stageMasterUnit->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Warehouse</span>
                    <span
                        class="info-value">{{ $slip->getUnitMaster->masterFabricWarehouse->cutting_master_name ?? $slip->stageMasterUnit->masterFabricWarehouse->cutting_master_name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="status-badge {{ $slip->status == 0 ? 'status-pending' : 'status-approved' }}">
                        {{ $slip->status == 0 ? '⏳ Pending' : '✅ Approved' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Uploaded</span>
                    <span class="info-value">{{ $slip->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- TYPE 1: LOT / ROLLS --}}
        @if($slip->save_type == 1 && $lot)
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-palette" style="color: var(--primary);"></i>
                    Lot & Design
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Lot No</span>
                        <span class="info-value">{{ $lot->lot_no }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order No</span>
                        <span class="info-value">{{ $lot->orderMain->sku ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Design</span>
                        <span class="info-value">{{ $lot->orderProductSet->design_number ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fabric</span>
                        <span class="info-value">{{ $lot->orderProductSet->fabric->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Color</span>
                        <span class="info-value">{{ $lot->orderProductSet->colors->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pattern</span>
                        <span class="info-value">{{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fitting</span>
                        <span class="info-value">{{ $lot->orderProductSet->master_product_fitting->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- ROLLS --}}
            {{-- @if($rolls->count() > 0)
                <div class="card">
                    <div class="section-title">
                        <i class="fas fa-scroll" style="color: var(--primary);"></i>
                        Rolls Allocation ({{ $rolls->count() }})
                    </div>
                    @foreach($rolls as $roll)
                        <div class="item-card">
                            <div class="item-header">
                                <span>Roll: {{ $roll->roll_no }}</span>
                                <span>{{ $roll->meter }} m</span>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roll->fabricRollAssigningsDetail as $size)
                                        <tr>
                                            <td>{{ $size->size }}</td>
                                            <td><strong>{{ $size->quantity }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endif --}}

            {{-- ROLLS --}}
            @if($rolls->count() > 0)
                <div class="card">
                    <div class="section-title">
                        <i class="fas fa-scroll" style="color: var(--primary);"></i>
                        Rolls Allocation ({{ $rolls->count() }})
                    </div>

                    @php
                        $totalMeter = 0;
                    @endphp

                    {{-- STEP 1: ALL ROLL NO & METERS --}}
                    @foreach($rolls as $roll)
                        @php
                            $totalMeter += $roll->meter;
                        @endphp

                        <div class="item-card">
                            <div class="item-header">
                                <span>Roll: {{ $roll->roll_no }}</span>
                                <span>{{ $roll->meter }} m</span>
                            </div>
                        </div>
                    @endforeach

                    {{-- STEP 2: TOTAL METER (ONCE) --}}
                    <div class="info-row" style="margin-top: 12px;">
                        <span class="info-label">Total Meter</span>
                        <span class="info-value">
                            <strong style="color: var(--primary);">
                                {{ $totalMeter }} m
                            </strong>
                        </span>
                    </div>

                    {{-- STEP 3: SIZE & QTY TABLE (ONCE) --}}
                    <table>
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rolls as $roll)
                                @foreach($roll->fabricRollAssigningsDetail as $size)
                                    <tr>
                                        <td>{{ $size->size }}</td>
                                        <td><strong>{{ $size->quantity }}</strong></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        @endif

        {{-- TYPE 2: PRINTING --}}
        @if($slip->save_type == 2 && $printing)
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-print" style="color: var(--primary);"></i>
                    Printing Details
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Lot No</span>
                        <span class="info-value">{{ $printing->lot_no }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">From Stage</span>
                        <span class="info-value">{{ $printing->from_stage->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">To Stage</span>
                        <span class="info-value">{{ $printing->to_stage->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Qty</span>
                        <span class="info-value">{{ $printing->quantity }}</span>
                    </div>
                </div>

                @if($printing_sizes->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($printing_sizes as $row)
                                <tr>
                                    <td>{{ $row->size }}</td>
                                    <td><strong>{{ $row->quantity }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- TYPE 3: STAGE MOVEMENT --}}
        @if($slip->save_type == 3 && isset($stage_transaction))
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-exchange-alt" style="color: var(--primary);"></i>
                    Stage Movement
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Lot No</span>
                        <span class="info-value">{{ $stage_transaction->lot_no }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">From Stage</span>
                        <span class="info-value">{{ $stage_transaction->from_stage->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">To Stage</span>
                        <span class="info-value">{{ $stage_transaction->to_stage->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Qty</span>
                        <span class="info-value">{{ $stage_transaction->quantity }}</span>
                    </div>
                </div>

                @if(isset($stage_sizes) && $stage_sizes->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stage_sizes as $row)
                                <tr>
                                    <td>{{ $row->size }}</td>
                                    <td><strong>{{ $row->quantity }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- PACKING DETAILS --}}
        @if(isset($packing_details) && $packing_details)
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-box-open" style="color: var(--primary);"></i>
                    Packed Cartons ({{ $packing_details->cartons->count() }})
                </div>

                @foreach($packing_details->cartons as $carton)
                    <div class="item-card">
                        <div class="item-header">
                            <span>📦 Carton #{{ $carton->carton_no }}</span>
                            <span class="status-badge status-approved">{{ $carton->boxes->count() }} Boxes</span>
                        </div>

                        {{-- Box Serial Numbers --}}
                        <div class="subsection-label">Box Serial Numbers</div>
                        <div class="box-grid">
                            @foreach($carton->boxes as $box)
                                <div class="box-badge">{{ $box->box_no }}</div>
                            @endforeach
                        </div>

                        {{-- Loose Pieces --}}
                        @if($carton->items->count() > 0)
                            <div class="subsection-label" style="margin-top: 16px;">Loose Pieces Summary</div>
                            @php
                                $summary = [];
                                foreach ($carton->items as $item) {
                                    $name = $item->detail ? $item->detail->size : ($item->size ? $item->size->name : 'ID:' . $item->size_id);
                                    $summary[$name] = ($summary[$name] ?? 0) + $item->quantity;
                                }
                            @endphp
                            <table>
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $name => $qty)
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td><strong style="color: var(--primary);">{{ $qty }} Pcs</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Image Modal -->
    <div class="image-modal" id="imageModal">
        <div class="close-modal" id="closeModal">
            <i class="fas fa-times"></i>
        </div>
        <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" alt="Slip" class="modal-image">
    </div>

    <script>
        const slipImage = document.getElementById('slipImage');
        const imageModal = document.getElementById('imageModal');
        const closeModal = document.getElementById('closeModal');

        slipImage.addEventListener('click', () => imageModal.classList.add('active'));
        closeModal.addEventListener('click', () => imageModal.classList.remove('active'));
        imageModal.addEventListener('click', (e) => {
            if (e.target === imageModal) imageModal.classList.remove('active');
        });
    </script>

    <div class="bottom-nav">
        <a href="{{ route('unit.dashboard') }}" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('unit.assignments') }}" class="nav-item">
            <i class="fas fa-clipboard-list"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('unit.history') }}" class="nav-item active">
            <i class="fas fa-clock"></i>
            <span>History</span>
        </a>
    </div>
</body>

</html>