@extends('layouts.unit')

@section('title', 'Slip Details')

@section('header_icon')
    <a href="{{ route('unit.assignments') }}" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@push('styles')
    <style>
        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .card-header-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(102, 126, 234, 0.1);
            padding: 8px 16px;
            border-bottom-left-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
        }

        .info-full {
            grid-column: span 2;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-upload {
            background: var(--bg-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .camera-box {
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 20px;
            padding: 40px 20px;
            text-align: center;
            margin-top: 20px;
        }

        .camera-icon {
            font-size: 40px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .camera-text {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        #preview {
            width: 100%;
            border-radius: 16px;
            display: none;
            margin-top: 15px;
        }
        
        .lot-list {
            margin-top: 15px;
        }
        
        .lot-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px;
        }
    </style>
@endpush

@section('content')
    @php
        $cleanSlipNo = str_replace('T', '', $slip_id);
        $totalPieces = 0;
        $lots = [];
        $isCompleted = true;
        $isCutting = $unit->master_stage_id == 1; // Assuming 1 is STAGE_CUTTING
        
        $transactionIds = [];
        
        foreach($transactions as $tx) {
            $totalPieces += ($tx->quantity ?? $tx->remaining_quantity ?? 0);
            if ($tx->lot_no) $lots[] = $tx->lot_no;
            
            $status = !empty($tx->image) || $tx->is_closed_for_unit == 1;
            if (!$status) $isCompleted = false;
            
            $transactionIds[] = [
                'id' => $tx->id,
                'type' => $tx->transaction_type
            ];
        }
        
        $lotString = empty($lots) ? '-' : implode(', ', array_unique($lots));
        
        $firstTx = $transactions->first();
        
        $dateGroups = [];
        foreach($transactions as $tx) {
            $isTaskCompleted = !empty($tx->image) || $tx->is_closed_for_unit == 1;
            $tStartDate = $tx->timing->start_date ?? $tx->start_date ?? $tx->timing->created_at ?? null;
            $tEndDate = $tx->timing->end_date ?? $tx->end_date ?? null;
            
            if ($isTaskCompleted) {
                $tStatus = 'Completed';
            } elseif ($tEndDate && now()->startOfDay() > \Carbon\Carbon::parse($tEndDate)->startOfDay()) {
                $tStatus = 'Delayed';
            } else {
                $tStatus = 'Pending';
            }
            
            $key = $tStartDate . '|' . $tEndDate . '|' . $tStatus;
            if (!isset($dateGroups[$key])) {
                $dateGroups[$key] = [
                    'lots' => [],
                    'start_date' => $tStartDate,
                    'end_date' => $tEndDate,
                    'status' => $tStatus
                ];
            }
            if ($tx->lot_no) {
                $dateGroups[$key]['lots'][] = $tx->lot_no;
            }
        }
    @endphp

    <div class="card">
        <div class="card-header-badge">SLIP #{{ $cleanSlipNo }}</div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="card-title" style="margin-bottom: 0; font-size: 16px; font-weight: 800; color: #1f2937; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-info-circle mr-2"></i> Slip Summary
            </div>
            <a href="{{ route('unit.download.slip', str_replace('T', '', $slip_id)) }}" target="_blank" download="slip_{{ $slip_id }}.pdf" style="background: #4f46e5; color: white; border: none; border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>

        <div class="info-grid mb-4">
            @if(!$isCutting)
            <div class="info-item">
                <span class="info-label"><i class="fas fa-layer-group mr-1"></i> From Stage</span>
                <span class="info-value">{{ $firstTx->from_stage->name ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-user mr-1"></i> Sent By</span>
                <span class="info-value">{{ $firstTx->getFromUnitMaster->name ?? '-' }}</span>
            </div>
            @endif
            <div class="info-item info-full">
                <span class="info-label">Lots Included</span>
                <span class="info-value text-blue">{{ $lotString }}</span>
            </div>
            
            <div class="info-item info-full" style="padding: 0; border: none; background: transparent;">
                <div style="background: #f8fafc; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; margin-top: 10px;">
                    <!-- Header -->
                    <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1.5fr 1fr; gap: 8px; padding: 8px 12px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Lots</div>
                        <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Assigned</div>
                        <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Est. Complete</div>
                        <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Status</div>
                    </div>
                    <!-- Rows -->
                    @foreach($dateGroups as $group)
                        <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1.5fr 1fr; gap: 8px; padding: 10px 12px; border-bottom: 1px dashed #e2e8f0;">
                            <div style="font-size: 12px; font-weight: 700; color: #4f46e5; word-break: break-all; align-self: center;">
                                {{ empty($group['lots']) ? '-' : implode(', ', array_unique($group['lots'])) }}
                            </div>
                            <div style="font-size: 12px; font-weight: 600; color: #1e293b; align-self: center;">
                                {{ $group['start_date'] ? date('d M, Y', strtotime($group['start_date'])) : '-' }}
                            </div>
                            <div style="font-size: 12px; font-weight: 600; {{ $group['status'] === 'Delayed' ? 'color: #dc2626;' : 'color: #1e293b;' }} align-self: center;">
                                {{ $group['end_date'] ? date('d M, Y', strtotime($group['end_date'])) : '-' }}
                            </div>
                            <div style="align-self: center;">
                                @if($group['status'] === 'Delayed')
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase;">Delayed</span>
                                @elseif($group['status'] === 'Completed')
                                    <span style="background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase;">Completed</span>
                                @else
                                    <span style="background: #fef9c3; color: #a16207; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase;">Pending</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="section-title" style="margin-top: 25px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
            <i class="fas fa-list-ul"></i> Lot Breakdown
        </div>
        
        <div class="lot-list">
            @foreach($transactions as $index => $tx)
                @php
                    $data = $tx->productSet ?? $tx->orderProduct?->orderProductSet;
                    $designNo = $data->design_number ?? '-';
                    $sizeSetName = $data->size_set_name ?? '-';
                    
                    if ($designNo === '-' && !empty($tx->lot_no)) {
                        $lRef = \App\Models\OrderLot::where('lot_no', $tx->lot_no)->with('orderProductSet')->first();
                        $designNo = $lRef->orderProductSet->design_number ?? '-';
                        $sizeSetName = $lRef->orderProductSet->size_set_name ?? '-';
                    }
                    
                    $pcs = $tx->quantity ?? $tx->remaining_quantity ?? 0;
                @endphp
                    <div class="lot-item" style="background: white; border: 1px solid #cbd5e1; border-radius: 12px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <!-- Header: Lot and Total Pcs -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="font-size: 18px; font-weight: 800; color: #1e293b;">Lot #{{ $tx->lot_no ?: '-' }}</div>
                            <div style="font-size: 16px; font-weight: 800; color: #4f46e5; background: #eef2ff; padding: 4px 10px; border-radius: 6px;">{{ $pcs }} Pcs</div>
                        </div>
                        
                        <!-- Details: Design and Size Set -->
                        <div style="display: flex; gap: 20px; margin-bottom: 15px; font-size: 15px; font-weight: 600; color: #64748b;">
                            <div>Design: <span style="color: #0f172a;">{{ $designNo }}</span></div>
                            <div>Size Set: <span style="color: #0f172a;">{{ $sizeSetName }}</span></div>
                        </div>
                        
                        <!-- Size Breakdown grid -->
                        @if($tx->details && count($tx->details) > 0)
                        <div style="border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                            <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;">Size Breakdown</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                @foreach($tx->details as $detail)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 15px; text-align: center; min-width: 75px;">
                                    <div style="font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 4px;">{{ $detail->size ?? '-' }}</div>
                                    <div style="font-size: 16px; font-weight: 800; color: #4f46e5;">{{ $detail->quantity ?? 0 }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div style="border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                            <div style="color: #64748b; font-size: 12px; font-style: italic;">No size details available.</div>
                        </div>
                        @endif
                    </div>
            @endforeach
        </div>
    </div>

    @if($previousSlip && $previousSlip->slip_file)
        <div class="card">
            <div class="section-title">
                <i class="fas fa-image"></i> Previous Stage Slip
            </div>
            <div style="text-align: center;">
                <img src="{{ asset('assets/production_slips/' . $previousSlip->slip_file) }}" style="max-width: 100%; max-height: 400px; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                <div style="font-size: 11px; color: #64748b; margin-top: 8px;">Tap image to view full screen</div>
            </div>
        </div>
    @endif


@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('fileInput');
        const openCameraBtn = document.getElementById('openCameraBtn');
        const selectFileBtn = document.getElementById('selectFileBtn');
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('placeholder');
        const photoData = document.getElementById('photoData');
        
        const initialBtns = document.getElementById('initialBtns');
        const afterCaptureBtns = document.getElementById('afterCaptureBtns');
        const retakeBtn = document.getElementById('retakeBtn');

        if(openCameraBtn) {
            openCameraBtn.addEventListener('click', () => {
                fileInput.removeAttribute('capture');
                fileInput.setAttribute('capture', 'environment');
                fileInput.click();
            });
        }

        if(selectFileBtn) {
            selectFileBtn.addEventListener('click', () => {
                fileInput.removeAttribute('capture');
                fileInput.click();
            });
        }

        if(fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        photoData.value = e.target.result;
                        
                        placeholder.style.display = 'none';
                        preview.style.display = 'block';
                        
                        initialBtns.style.display = 'none';
                        afterCaptureBtns.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if(retakeBtn) {
            retakeBtn.addEventListener('click', () => {
                fileInput.value = '';
                photoData.value = '';
                
                preview.style.display = 'none';
                placeholder.style.display = 'block';
                
                afterCaptureBtns.style.display = 'none';
                initialBtns.style.display = 'grid';
            });
        }
    });
</script>
@endpush
