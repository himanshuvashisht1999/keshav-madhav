@php use Carbon\Carbon; @endphp
    <style>
        /* ================= PAGE ================= */
        .report-page {
            background: #f4f6f9;
        }

        /* ================= SECTION ================= */
        .section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
            margin-bottom: 14px;
        }

        .section-title {
            padding: 12px 16px;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
        }

        /* ================= INFO HEADER ================= */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 14px;
        }

        .info-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
            border-left: 5px solid #2563eb;
        }

        .info-card label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600;
        }

        .info-card div {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        /* ================= SUMMARY ================= */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            padding: 16px;
        }

        .summary-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .summary-card label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600;
        }

        .summary-card div {
            font-size: 14px;
            font-weight: 700;
        }

        /* ================= TABLE ================= */
        .compact-table {
            width: 100%;
            font-size: 13px;
        }

        .compact-table th {
            background: #111827;
            color: #fff;
            padding: 6px;
        }

        .compact-table td {
            padding: 6px;
        }

        .compact-table tr:last-child td {
            background: #f1f5f9;
            font-weight: 700;
        }

        /* ================= CUTTING ================= */
        .cutting-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 16px;
        }

        .cut-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
            padding: 12px;
        }

        .cut-card h6 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        /* ================= PROGRESS CARDS ================= */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
            padding: 16px;
        }

        .stage-card {
            border-radius: 10px;
            padding: 14px;
            position: relative;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
            border-left: 6px solid;
        }

        .stage-card h5 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .stage-card .unit {
            font-size: 12px;
            color: #374151;
            margin-bottom: 10px;
        }

        .stage-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .metric {
            background: rgba(255, 255, 255, .7);
            border-radius: 6px;
            padding: 6px;
            text-align: center;
            font-size: 13px;
        }

        .metric strong {
            display: block;
            font-size: 15px;
        }

        /* STATUS */
        .card-progress {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .card-completed {
            background: #dcfce7;
            border-color: #16a34a;
        }

        .card-delayed {
            background: #fee2e2;
            border-color: #dc2626;
        }

        .card-not_started {
            background: #e5e7eb;
            border-color: #6b7280;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 14px;
            color: #fff;
        }

        .badge-progress {
            background: #f59e0b;
        }

        .badge-completed {
            background: #16a34a;
        }

        .badge-delayed {
            background: #dc2626;
        }

        .badge-not_started {
            background: #6b7280;
        }
    </style>

    <div class="content-wrapper report-page">
        <section class="content">
            <div class="container-fluid">

                @php $lot = $data['lots_data'][0] ?? null; @endphp

                {{-- ================= INFO HEADER ================= --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 font-weight-bold" style="color: #1e293b;">Lot Details</h4>
                    <a href="{{ route('admin.report.lots.lot-details.pdf', ['lot_no' => $data['lot_no']]) }}"
                        class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
                    </a>
                </div>

                <div class="info-grid">
                    <div class="info-card">
                        <label>Lot Number</label>
                        <div>{{ $lot->lot_no ?? '-' }}</div>
                    </div>
                    <div class="info-card">
                        <label>Order SKU</label>
                        <div>{{ $lot->orderProductSet->orderMain->sku ?? '-' }}</div>
                    </div>
                    <div class="info-card">
                        <label>Customer</label>
                        <div>{{ $lot->orderProductSet->orderMain->customer->name ?? '-' }}</div>
                    </div>
                    <div class="info-card">
                        <label>Report Date</label>
                        <div>{{ now()->format('d M Y') }}</div>
                    </div>
                </div>

                {{-- ================= ORDER SUMMARY ================= --}}
                <div class="section">
                    <div class="section-title">Order Summary</div>
                    <div class="summary-grid">
                        <div class="summary-card">
                            <label>Fabric</label>
                            <div>{{ $lot->orderProductSet->fabric->name ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Color</label>
                            <div>{{ $lot->orderProductSet->colors->name ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Pattern</label>
                            <div>{{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Design Number</label>
                            <div>{{ $lot->orderProductSet->design_number ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Fitting</label>
                            <div>{{ $lot->orderProductSet->master_product_fitting->name ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Production Unit</label>
                            <div>{{ $lot->productionSlipDigitization->getUnitMaster->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- ================= CUTTING & ROLLS ================= --}}
                <div class="section">
                    <div class="section-title">Cutting & Rolls</div>
                    <div class="cutting-grid">

                        <div class="cut-card">
                            <h6>Size Wise Quantity</h6>
                            <table class="compact-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th class="text-right">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $qtyTotal = 0; @endphp
                                    @foreach($data['rolls_data'] as $roll)
                                        @foreach($roll->fabricRollAssigningsDetail ?? [] as $d)
                                            <tr>
                                                <td>{{ $d->size }}</td>
                                                <td class="text-right">{{ $d->quantity }}</td>
                                            </tr>
                                            @php $qtyTotal += $d->quantity; @endphp
                                        @endforeach
                                    @endforeach
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-right">{{ $qtyTotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="cut-card">
                            <h6>Roll Consumption</h6>
                            <table class="compact-table">
                                <thead>
                                    <tr>
                                        <th>Roll</th>
                                        <th class="text-right">Meter</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['rolls_data'] as $roll)
                                        <tr>
                                            <td>{{ $roll->roll_no }}</td>
                                            <td class="text-right">{{ $roll->meter }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-right">{{ $data['rolls_data']->sum('meter') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                {{-- ================= PRODUCTION PROGRESS (UNCHANGED LOGIC) ================= --}}
                <div class="section">
                    <div class="section-title">Production Progress</div>

                    <div class="progress-grid">
                        @foreach($master_stages as $stage)
                            @php
                                $d = getLotDetails($data['lot_no'], $stage->id);
                                if (!$d || !$d['time_allocation'])
                                    continue;

                                $remaining = (int) $d['remaining_quantity'];
                                $total = (int) $d['quantity'];

                                if ($total === 0)
                                    continue;

                                $eta = Carbon::parse($d['time_allocation']);
                                $completed = $d['completed_time'] ? Carbon::parse($d['completed_time']) : null;

                                if ($remaining === 0)
                                    $status = ($completed && $completed->gt($eta)) ? 'delayed' : 'completed';
                                elseif (now()->gt($eta))
                                    $status = 'delayed';
                                else
                                    $status = 'progress';
                            @endphp

                            <div class="stage-card card-{{ $status }}">
                                <span class="status-badge badge-{{ $status }}">
                                    {{ $status == 'not_started' ? 'Not Started' : ucfirst($status) }}
                                </span>

                                <h5>{{ $stage->name }}</h5>
                                <div class="unit">Unit: {{ $status == 'not_started' ? 'Not Assigned' : $d['unit_name'] }}</div>

                                <div class="stage-metrics">
                                    <div class="metric">Total<strong>{{ $total }}</strong></div>
                                    <div class="metric">Remaining<strong>{{ $remaining }}</strong></div>
                                    <div class="metric">Start<strong>{{ $d['start_date'] ? Carbon::parse($d['start_date'])->format('d M H:i') : '-' }}</strong></div>
                                    <div class="metric">ETA<strong>{{ $eta->format('d M Y') }}</strong></div>
                                    <div class="metric">
                                        Completed<strong>{{ ($remaining == 0 && $completed) ? $completed->format('d M Y') : '-' }}</strong></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ================= ALL SESSIONS AS CARDS ================= --}}
                <div class="section mt-4">
                    <div class="section-title">Production Sessions</div>
                    <div class="progress-grid">

                        @php
                            $cutting_unit = '-';
                            if(isset($data['lots_data']) && count($data['lots_data']) > 0) {
                                $first_lot = $data['lots_data']->first();
                                if($first_lot && $first_lot->productionSlipDigitization && $first_lot->productionSlipDigitization->getUnitMaster) {
                                    $cutting_unit = $first_lot->productionSlipDigitization->getUnitMaster->name;
                                }
                            }

                            // Compute last session
                            $lastSessionType = null;
                            $lastSessionId = null;
                            $allTxs = collect();
                            if(isset($data['lots'])) {
                                foreach($data['lots'] as $lot) {
                                    $allTxs->push(['type' => 'lot', 'id' => $lot->id, 'created_at' => $lot->created_at]);
                                }
                            }
                            if(isset($data['printings'])) {
                                foreach($data['printings'] as $p) {
                                    $allTxs->push(['type' => 'printing', 'id' => $p->id, 'created_at' => $p->created_at]);
                                }
                            }
                            if(isset($data['stage_transactions'])) {
                                foreach($data['stage_transactions'] as $st) {
                                    $type = 'transfer';
                                    if (get_class($st) == 'App\Models\OrderPrintingToStichingTransaction') $type = 'printing_stitching';
                                    if (get_class($st) == 'App\Models\OrderGodamStageTransaction') $type = 'godam';
                                    $allTxs->push(['type' => $type, 'id' => $st->id, 'created_at' => $st->created_at]);
                                }
                            }
                            $lastTx = $allTxs->sortBy('created_at')->last();
                            if ($lastTx) {
                                $lastSessionType = $lastTx['type'];
                                $lastSessionId = $lastTx['id'];
                            }
                        @endphp

                        {{-- TYPE 1 : ROLLS (Sessions) --}}
                        @if(count($data['lots']) > 0)
                            @foreach($data['lots'] as $index => $lot)
                                @php $currentRolls = $data['rolls']->where('order_lot_id', $lot->id); @endphp
                                <div class="stage-card card-completed">
                                    <span class="status-badge badge-completed">Cutting</span>
                                    
                                    @if($lastSessionType == 'lot' && $lastSessionId == $lot->id)
                                        <form action="{{ route('admin.report.lots.delete-session', ['type' => 'lot', 'id' => $lot->id]) }}" method="POST" style="position: absolute; top: 12px; right: 80px; z-index: 10;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 m-0" onclick="return confirm('Are you sure you want to delete this session? Quantities will be restored.');" title="Delete Session">
                                                <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <div class="mb-3" style="margin-top: 28px;">
                                        <div class="text-muted" style="font-size: 11px; margin-bottom: 2px;">
                                            Stage: <strong class="text-dark">Cutting</strong>
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Unit: <strong class="text-dark">{{ $cutting_unit }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="stage-metrics">
                                        <div class="metric" style="text-align: left;">Date<strong>{{ \Carbon\Carbon::parse($lot->production_datetime)->format('d M Y') }}</strong></div>
                                        <div class="metric" style="text-align: center;">Rolls<strong>{{ $currentRolls->count() }}</strong></div>
                                        <div class="metric" style="text-align: right;">Meters<strong>{{ $currentRolls->sum('meter') }}</strong></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- TYPE 2 : PRINTING --}}
                        @if(count($data['printings']) > 0)
                            @foreach($data['printings'] as $index => $printing)
                                <div class="stage-card card-progress">
                                    <span class="status-badge badge-progress">Printing</span>
                                    
                                    @if($lastSessionType == 'printing' && $lastSessionId == $printing->id)
                                        <form action="{{ route('admin.report.lots.delete-session', ['type' => 'printing', 'id' => $printing->id]) }}" method="POST" style="position: absolute; top: 12px; right: 85px; z-index: 10;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 m-0" onclick="return confirm('Are you sure you want to delete this session? Quantities will be restored.');" title="Delete Session">
                                                <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <div class="mb-3" style="margin-top: 28px;">
                                        <div class="text-muted" style="font-size: 11px; margin-bottom: 2px;">
                                            Stage: <strong class="text-dark">{{ $printing->from_stage?->name ?? 'Cutting' }} <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px;"></i> {{ $printing->to_stage?->name ?? '-' }}</strong>
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Unit: <strong class="text-dark">{{ $cutting_unit }} <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px;"></i> {{ $printing->getToUnitMaster?->name ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="stage-metrics">
                                        <div class="metric" style="text-align: left;">Date<strong>{{ \Carbon\Carbon::parse($printing->production_datetime)->format('d M Y') }}</strong></div>
                                        <div class="metric" style="text-align: right;">Pieces<strong>{{ $printing->quantity }}</strong></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- TYPE 3 : STAGE TRANSFER --}}
                        @if(count($data['stage_transactions']) > 0)
                            @foreach($data['stage_transactions'] as $index => $transaction)
                                @php
                                    $txType = 'transfer';
                                    if (get_class($transaction) == 'App\Models\OrderPrintingToStichingTransaction') $txType = 'printing_stitching';
                                    if (get_class($transaction) == 'App\Models\OrderGodamStageTransaction') $txType = 'godam';
                                @endphp
                                <div class="stage-card" style="border-left-color: #f59e0b; background: #fffbeb;">
                                    <span class="status-badge" style="background: #fef3c7; color: #b45309;">Transfer</span>
                                    
                                    @if($lastSessionType == $txType && $lastSessionId == $transaction->id)
                                        <form action="{{ route('admin.report.lots.delete-session', ['type' => $txType, 'id' => $transaction->id]) }}" method="POST" style="position: absolute; top: 12px; right: 85px; z-index: 10;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 m-0" onclick="return confirm('Are you sure you want to delete this session? Quantities will be restored.');" title="Delete Session">
                                                <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <div class="mb-3" style="margin-top: 28px;">
                                        <div class="text-muted" style="font-size: 11px; margin-bottom: 2px;">
                                            Stage: <strong class="text-dark">{{ $transaction->from_stage?->name ?? '-' }} <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px;"></i> {{ $transaction->to_stage?->name ?? '-' }}</strong>
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Unit: <strong class="text-dark">{{ $transaction->getFromUnitMaster?->name ?? '-' }} <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px;"></i> {{ $transaction->getToUnitMaster?->name ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="stage-metrics">
                                        <div class="metric" style="text-align: left;">Date<strong>{{ \Carbon\Carbon::parse($transaction->production_datetime)->format('d M Y') }}</strong></div>
                                        <div class="metric" style="text-align: right;">Pieces<strong>{{ $transaction->quantity }}</strong></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </section>
