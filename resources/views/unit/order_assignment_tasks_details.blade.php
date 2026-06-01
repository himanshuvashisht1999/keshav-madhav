@extends('layouts.unit')

@section('title', 'Order Assignments Details')

@section('header_icon')
    <a href="javascript:history.back()" style="color: white; margin-right: 10px;">
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

        /* Info Grid */
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

        /* Breakdown Table */
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }

        .breakdown-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #9ca3af;
            padding: 12px 0;
            border-bottom: 2px solid #f3f4f6;
        }

        .breakdown-table td {
            padding: 12px 0;
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
            border-bottom: 1px solid #f9fafb;
        }

        .size-badge {
            background: #eff6ff;
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .page-subtitle {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        
        .btn-simple {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            text-decoration: none !important;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            width: 100%;
        }

        .btn-simple-primary {
            background: #4f46e5;
            color: #ffffff;
        }

        .btn-simple-primary:hover {
            background: #4338ca;
            color: #ffffff;
        }
        
        .btn-simple-danger {
            border: 1px solid #fca5a5;
            color: #b91c1c;
            background: #fef2f2;
        }
        
        .btn-simple-danger:hover {
            background: #fee2e2;
        }

        .btn-simple-success {
            border: 1px solid #6ee7b7;
            color: #047857;
            background: #ecfdf5;
        }

        .btn-simple-success:hover {
            background: #d1fae5;
        }
    </style>
@endpush

@section('content')
    <div style="margin-bottom: 25px;">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h1 class="page-title">{{ $groupLabel }}: {{ $orderSku }}</h1>
                <div class="page-subtitle">
                    {{ count($allDetails) }} Order Set(s) Found
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap: 12px;">
                <a href="{{ route('unit.order-summary', ['sku' => $orderSku]) }}" class="btn btn-primary shadow-sm" style="border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="fas fa-file-invoice mr-1"></i> Order Summary
                </a>
            </div>
        </div>
    </div>

    @foreach($allDetails as $detail)
        @php
            $header = $detail['header'];
            $sizeData = $detail['sizeData'];
            $transaction = $detail['transaction'];
            $isRework = $detail['isRework'];
        @endphp
        
        <div style="margin-bottom: 40px; border-bottom: 3px dashed #cbd5e1; padding-bottom: 20px;">
            <!-- PRIMARY DETAILS -->
            <div class="card">
                <div class="card-header-badge">#{{ $header['id'] }}</div>
                @if($isRework)
                    <div
                        style="position: absolute; top: 0; left: 0; padding: 8px 16px; background: #e11d48; color: white; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom-right-radius: 20px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> REWORK TASK
                    </div>
                @endif
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Production Info
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">CMPO No</span>
                        <span class="info-value">CMPO-{{ $header['id'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Company</span>
                        <span class="info-value">{{ $header['customer'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date</span>
                        <span class="info-value">{{ $header['date'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Design No</span>
                        <span class="info-value">{{ $header['design_no'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Lot No</span>
                        <span class="info-value text-blue">{{ $header['lot_no'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fabric</span>
                        <span class="info-value">{{ $header['fabric'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Color</span>
                        <span class="info-value">{{ $header['color'] }}</span>
                    </div>

                    @if($type === 'cutting')
                        <div class="info-item info-full">
                            <span class="info-label">Warehouse (Cutting Master)</span>
                            <span class="info-value">{{ $header['warehouse'] }} ({{ $header['unit_name'] }})</span>
                        </div>
                    @else
                        <div class="info-item">
                            <span class="info-label">From Stage</span>
                            <span class="info-value">{{ $header['from_stage'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Sent By</span>
                            <span class="info-value">{{ $header['sent_by'] }}</span>
                        </div>
                    @endif

                    <div class="info-item info-full">
                        <span class="info-label">Pattern & Fitting</span>
                        <span class="info-value">{{ $header['pattern'] }} | {{ $header['fitting'] }}</span>
                    </div>
                    <div class="info-item info-full">
                        <span class="info-label">Belt</span>
                        <span class="info-value">{{ $header['belt'] }}</span>
                    </div>
                </div>

                @if(isset($header['start_date']) || isset($header['end_date']))
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #f1f5f9;">
                    <div class="section-title">
                        <i class="fas fa-clock"></i> Timing Info
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Start Date</span>
                            <span class="info-value">{{ $header['start_date'] ? date('d M Y, h:i A', strtotime($header['start_date'])) : '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Expected End</span>
                            <span class="info-value @if(!$header['complete_date'] && now() > $header['end_date']) text-danger @endif">
                                {{ $header['end_date'] ? date('d M Y, h:i A', strtotime($header['end_date'])) : '-' }}
                                @if(!$header['complete_date'] && now() > $header['end_date'])
                                    <span style="font-size: 10px; color: #dc2626; font-weight: 800; text-transform: uppercase;">(Delayed)</span>
                                @endif
                            </span>
                        </div>
                        @if($header['complete_date'])
                        <div class="info-item info-full">
                            <span class="info-label">Completed At</span>
                            <span class="info-value text-success">{{ date('d M Y, h:i A', strtotime($header['complete_date'])) }}</span>
                        </div>
                        @endif
                    </div>
                @endif

                @if($header['remark'] && $header['remark'] != '-')
                    <div
                        style="margin-top: 15px; padding: 12px; background: #fffbe6; border-radius: 12px; border: 1px solid #ffe58f; font-size: 13px; color: #856404;">
                        <strong>Note:</strong> {{ $header['remark'] }}
                    </div>
                @endif
            </div>

            <!-- BREAKDOWN -->
            <div class="card" style="padding-bottom: 5px;">
                <div class="section-title" style="margin-bottom: 15px;">
                    <i class="fas fa-layer-group"></i> Quantity & Set Breakdown
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                    <div
                        style="background: #fdf2f8; border-radius: 16px; padding: 15px; border: 1px solid #fce7f3; text-align: center;">
                        <span
                            style="font-size: 10px; color: #be185d; text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 5px; letter-spacing: 0.5px;">Master
                            Set</span>
                        <span style="font-size: 18px; color: #831843; font-weight: 900;">{{ $header['size_set'] ?? '-' }}</span>
                    </div>
                    <div
                        style="background: #f0f9ff; border-radius: 16px; padding: 15px; border: 1px solid #e0f2fe; text-align: center;">
                        <span
                            style="font-size: 10px; color: #0369a1; text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 5px; letter-spacing: 0.5px;">Pcs/Set</span>
                        <span style="font-size: 18px; color: #0c4a6e; font-weight: 900;">{{ $header['pcs_in_set'] ?? 0 }}</span>
                    </div>
                    <div
                        style="background: #f0fdf4; border-radius: 16px; padding: 15px; border: 1px solid #dcfce7; text-align: center;">
                        <span
                            style="font-size: 10px; color: #15803d; text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 5px; letter-spacing: 0.5px;">Grand
                            Total</span>
                        <span
                            style="font-size: 18px; color: #064e3b; font-weight: 900;">{{ number_format($header['total_pcs'], 0) }}</span>
                    </div>
                </div>

                <div style="padding: 0 5px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span style="font-size: 12px; color: #64748b; font-weight: 700;">Detailed Piece Breakdown</span>
                        <span
                            style="font-size: 11px; background: #3b82f6; color: white; padding: 2px 8px; border-radius: 10px; font-weight: 600;">{{ count($sizeData) }}
                            Sizes</span>
                    </div>

                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th style="padding-left: 5px;">Size</th>
                                <th>Color</th>
                                <th style="text-align: right; padding-right: 5px;">Total Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $calculatedTotal = 0; @endphp
                            @foreach($sizeData as $row)
                                @php $calculatedTotal += $row['pcs']; @endphp
                                <tr>
                                    <td style="padding-left: 5px;"><span class="size-badge">{{ $row['size'] }}</span></td>
                                    <td style="color: #475569; font-weight: 500;">{{ $row['color'] }}</td>
                                    <td style="text-align: right; font-weight: 800; color: #1e293b; padding-right: 5px;">
                                        {{ number_format($row['pcs'], 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                                <td colspan="2" style="padding: 15px 12px; font-weight: 900; color: #475569;">TOTAL QUANTITY</td>
                                <td
                                    style="text-align: right; padding: 15px 12px; color: #2563eb; font-size: 18px; font-weight: 900;">
                                    {{ number_format($calculatedTotal ?: $header['total_pcs'], 0) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                @if($canCloseTasks)
                    <form method="POST"
                        action="{{ ($view ?? 'open') === 'closed'
                            ? route('unit.assignments.reopen', ['type' => 'cutting', 'id' => $header['id']])
                            : route('unit.assignments.close', ['type' => 'cutting', 'id' => $header['id']]) }}">
                        @csrf
                        <button type="submit" class="btn-simple {{ ($view ?? 'open') === 'closed' ? 'btn-simple-success' : 'btn-simple-danger' }}">
                            <i class="fas {{ ($view ?? 'open') === 'closed' ? 'fa-undo' : 'fa-check' }} mr-1"></i>
                            {{ ($view ?? 'open') === 'closed' ? 'Re-open Task' : 'Close Task' }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('unit.assignments.details', ['type' => 'cutting', 'id' => $header['id']]) }}" class="btn-simple btn-simple-primary">
                    <i class="fas fa-file-invoice mr-1"></i> Full Process / Upload
                </a>
            </div>
            
        </div>
    @endforeach
    
    @if(count($allDetails) == 0)
        <div class="card text-center p-5">
            <i class="fas fa-box-open text-muted mb-3" style="font-size: 40px;"></i>
            <h5 class="text-muted">No assignments found for this order.</h5>
        </div>
    @endif
@endsection
