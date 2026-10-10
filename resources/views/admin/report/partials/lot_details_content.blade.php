@php use Carbon\Carbon; @endphp
<div class="content-wrapper erp-page p-2 lot-details-container">
    <section class="content">
        <div class="container-fluid">

            @php $lot = $data['lots_data'][0] ?? null; @endphp

            {{-- ================= INFO HEADER ================= --}}
            <div class="erp-header-bar mb-3">
                <div class="d-flex align-items-center">
                    <h1 class="erp-page-title mb-0">Lot details</h1>
                    <div class="erp-report-meta ml-3">
                        <span class="meta-item"><i class="fas fa-tag mr-1 text-muted"></i> Lot #{{ $data['lot_no'] }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.report.lots.lot-details.pdf', ['lot_no' => $data['lot_no']]) }}"
                        class="btn-erp btn-erp-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Download PDF
                    </a>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <label>Lot number</label>
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
                    <label>Report date</label>
                    <div>{{ now()->format('d M Y') }}</div>
                </div>
            </div>

                {{-- ================= ORDER SUMMARY ================= --}}
                <div class="section">
                    <div class="section-title">Order summary</div>
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
                            <label>Design number</label>
                            <div>{{ $lot->orderProductSet->design_number ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Fitting</label>
                            <div>{{ $lot->orderProductSet->master_product_fitting->name ?? '-' }}</div>
                        </div>
                        <div class="summary-card">
                            <label>Production unit</label>
                            <div>{{ $lot->productionSlipDigitization?->getUnitMaster?->name ?? ($lot->cutting_master?->name ?? ($lot->orderProductSet?->stage_master_unit?->name ?? '-')) }}</div>
                        </div>
                    </div>
                </div>

                {{-- ================= CUTTING & ROLLS ================= --}}
                <div class="section">
                    <div class="section-title">Cutting & rolls</div>
                    <div class="cutting-grid">

                        <div class="cut-card">
                            <h6>Size wise quantity</h6>
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
                            <h6>Roll consumption</h6>
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
                    <div class="section-title">Production progress</div>

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
                    <div class="section-title">Production sessions</div>
                    <div class="progress-grid">

                        @php
                            $cutting_unit = '-';
                            if(isset($data['lots_data']) && count($data['lots_data']) > 0) {
                                $first_lot = $data['lots_data']->first();
                                if(!empty($first_lot->productionSlipDigitization?->getUnitMaster?->name)) {
                                    $cutting_unit = $first_lot->productionSlipDigitization->getUnitMaster->name;
                                } elseif(!empty($first_lot->cutting_master?->name)) {
                                    $cutting_unit = $first_lot->cutting_master->name;
                                } elseif(!empty($first_lot->orderProductSet?->stage_master_unit?->name)) {
                                    $cutting_unit = $first_lot->orderProductSet->stage_master_unit->name;
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
                                            Unit: <strong class="text-dark">{{ $printing->from_stage_id == 3 ? $cutting_unit : ($printing->getFromUnitMaster?->name ?? $cutting_unit) }} <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px;"></i> {{ $printing->getToUnitMaster?->name ?? '-' }}</strong>
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
                                    $isTxRework = ($transaction->type === 'rework' || (string)$transaction->type === '0' || stripos($transaction->remarks ?? '', 'rework') !== false || stripos($transaction->remarks ?? '', 'rewash') !== false);
                                @endphp
                                <div class="stage-card" style="border-left-color: {{ $isTxRework ? '#ef4444' : '#f59e0b' }}; background: {{ $isTxRework ? '#fff5f5' : '#fffbeb' }};">
                                    @if($isTxRework)
                                        <span class="status-badge" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-tools mr-1"></i> Rework</span>
                                    @else
                                        <span class="status-badge" style="background: #fef3c7; color: #b45309;">Transfer</span>
                                    @endif
                                    
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
                                        @if($isTxRework && !empty($transaction->remarks))
                                            <div class="text-danger small font-weight-bold mt-1" style="font-size: 11px;">
                                                <i class="fas fa-exclamation-circle"></i> {{ $transaction->remarks }}
                                            </div>
                                        @endif
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
