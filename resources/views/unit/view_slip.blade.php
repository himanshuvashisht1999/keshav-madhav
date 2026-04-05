@extends('layouts.unit')

@section('title', 'Slip Details')

@section('header_icon')
<a href="{{ route('unit.history') }}" style="color: white; margin-right: 10px;">
    <i class="fas fa-arrow-left"></i>
</a>
@endsection

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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

    th, td {
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
        z-index: 2000;
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
</style>
@endpush

@section('content')
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
    <div class="card" style="border-top: 4px solid #6366f1;">
        <div class="section-title">
            <i class="fas fa-id-card" style="color: #6366f1;"></i>
            Slip Summary
        </div>
        <div class="info-grid" style="grid-template-columns: 1fr 1fr;">
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Slip ID</span>
                <span class="info-value" style="color: #6366f1;">#{{ $slip->id }}</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">From Stage</span>
                <span class="info-value">{{ $slip->fromStage->name ?? '-' }}</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Lot No.</span>
                <span class="info-value" style="color: #f59e0b;">{{ $summary['lot_no'] ?: '-' }}</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Design No.</span>
                <span class="info-value">{{ $summary['design'] ?: '-' }}</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Pieces</span>
                <span class="info-value" style="color: #10b981;">{{ $total_pcs }}</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Size Set</span>
                <span class="info-value" style="color: #3b82f6;">{{ $summary['size_group'] }} ({{ $actual_range }})</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Order No.</span>
                <span class="info-value">{{ $summary['order_no'] ?: '-' }}</span>
            </div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="info-label">Customer</span>
                <span class="info-value">{{ $summary['customer'] ?: '-' }}</span>
            </div>
        </div>
        <div class="info-row" style="margin-top: 10px; justify-content: space-between;">
            <span class="info-label">Status</span>
            <span class="status-badge {{ $slip->status == 0 ? 'status-pending' : 'status-approved' }}">
                {{ $slip->status == 0 ? '⏳ Pending' : '✅ Approved' }}
            </span>
        </div>
    </div>

    {{-- DIGITIZATION SESSIONS --}}

    {{-- 1. CUTTING SESSIONS --}}
    @if($lots->isNotEmpty())
        <div class="subsection-label" style="margin: 10px 0 15px 5px; font-size: 14px; color: #10b981;">
            <i class="fas fa-cut"></i> Cutting Sessions ({{ $lots->count() }})
        </div>
        @foreach($lots as $index => $lot)
            @php $currentRolls = $rolls->where('order_lot_id', $lot->id); @endphp
            <div class="card" style="border-left: 5px solid #10b981; padding: 0; overflow: hidden;">
                <div style="background: #ecfdf5; padding: 10px 15px; border-bottom: 1px solid #d1fae5; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: #065f46; font-size: 14px;">Session #{{ $index + 1 }} - <span style="font-weight: 900; color: #10b981;">LOT #{{ $lot->lot_no }}</span> - {{ $currentRolls->sum(fn($r) => $r->fabricRollAssigningsDetail->sum('quantity')) }} Pcs</span>
                </div>

                @if($lot->orderProductSet)
                    <div style="padding: 12px 15px; background: #fff; border-bottom: 1px solid #f3f4f6;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Lot No</div>
                                <div style="font-size: 13px; font-weight: 600; color: #10b981;">#{{ $lot->lot_no }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Order No</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $lot->orderMain?->sku ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Design No</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $lot->orderProductSet->design_number ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Fabric</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $lot->orderProductSet->fabric?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Color</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $lot->orderProductSet->colors?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Customer</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $lot->orderMain?->customer?->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div style="padding: 15px;">
                    @php $currentRolls = $rolls->where('order_lot_id', $lot->id); @endphp
                    @if($currentRolls->isNotEmpty())
                        <div class="subsection-label">Rolls Detail</div>
                        @foreach($currentRolls as $roll)
                            <div class="item-card" style="margin-bottom: 8px; border-left-color: #10b981;">
                                <div class="item-header" style="margin-bottom: 4px;">
                                    <span>Roll: {{ $roll->roll_no }}</span>
                                    <span>{{ $roll->meter }}m</span>
                                </div>
                                <div class="box-grid" style="margin-bottom: 0;">
                                    @foreach($roll->fabricRollAssigningsDetail as $size)
                                        <div class="box-badge" style="padding: 4px 8px; font-size: 11px;">
                                            {{ $size->size }}: <strong>{{ $size->quantity }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #9ca3af; padding: 10px; font-size: 13px; font-style: italic;">No rolls digitized yet</div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    {{-- 2. PRINTING SESSIONS --}}
    @if($printings->isNotEmpty())
        <div class="subsection-label" style="margin: 20px 0 15px 5px; font-size: 14px; color: #3b82f6;">
            <i class="fas fa-print"></i> Printing Sessions ({{ $printings->count() }})
        </div>
        @foreach($printings as $index => $printing)
            <div class="card" style="border-left: 5px solid #3b82f6; padding: 0; overflow: hidden;">
                <div style="background: #eff6ff; padding: 10px 15px; border-bottom: 1px solid #dbeafe; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: #1e40af; font-size: 14px;">Session #{{ $index + 1 }} - <span style="font-weight: 900; color: #3b82f6;">LOT #{{ $printing->lot_no }}</span> - {{ $printing->details->sum('quantity') }} Pcs</span>
                </div>

                @if($printing->orderProduct?->orderProductSet)
                    @php $ops = $printing->orderProduct->orderProductSet; @endphp
                    <div style="padding: 12px 15px; background: #fff; border-bottom: 1px solid #f3f4f6;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Lot No</div>
                                <div style="font-size: 13px; font-weight: 600; color: #3b82f6;">#{{ $printing->lot_no }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Order No</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->orderMain?->sku ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Design No</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->design_number ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Fabric</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->fabric?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Color</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->colors?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Customer</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->orderMain?->customer?->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div style="padding: 15px;">
                    <div class="subsection-label">Size Distribution</div>
                    <div class="box-grid">
                        @foreach($printing->details as $size)
                            <div class="box-badge" style="border-color: #3b82f6; background: #eff6ff;">
                                {{ $size->size }}: <strong>{{ $size->quantity }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- 3. OTHER SESSIONS --}}
    @if($stage_transactions->isNotEmpty())
        <div class="subsection-label" style="margin: 20px 0 15px 5px; font-size: 14px; color: #f59e0b;">
            <i class="fas fa-exchange-alt"></i> Transfer Sessions ({{ $stage_transactions->count() }})
        </div>
        @foreach($stage_transactions as $index => $transaction)
            <div class="card" style="border-left: 5px solid #f59e0b; padding: 0; overflow: hidden;">
                <div style="background: #fffbeb; padding: 10px 15px; border-bottom: 1px solid #fef3c7; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: #92400e; font-size: 14px;">Session #{{ $index + 1 }} - <span style="font-weight: 900; color: #f59e0b;">LOT #{{ $transaction->lot_no }}</span> - {{ $transaction->details->sum('quantity') }} Pcs</span>
                </div>

                @if($transaction->orderProduct?->orderProductSet)
                    @php $ops = $transaction->orderProduct->orderProductSet; @endphp
                    <div style="padding: 12px 15px; background: #fff; border-bottom: 1px solid #f3f4f6;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Lot No</div>
                                <div style="font-size: 13px; font-weight: 600; color: #f59e0b;">#{{ $transaction->lot_no }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Order No</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->orderMain?->sku ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Design No</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->design_number ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Fabric</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->fabric?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Color</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->colors?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="subsection-label" style="font-size: 9px; margin-bottom: 2px;">Customer</div>
                                <div style="font-size: 13px; font-weight: 600;">{{ $ops->orderMain?->customer?->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div style="padding: 15px;">
                    <div class="subsection-label">Size Distribution</div>
                    <div class="box-grid">
                        @foreach($transaction->details as $size)
                            <div class="box-badge" style="border-color: #f59e0b; background: #fffbeb;">
                                {{ $size->size }}: <strong>{{ $size->quantity }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- 4. PACKING DETAILS --}}
    @if($packing_details && $packing_details->cartons->count() > 0)
        <div class="subsection-label" style="margin: 20px 0 15px 5px; font-size: 14px; color: #8b5cf6;">
            <i class="fas fa-box-open"></i> Packing Summary ({{ $packing_details->cartons->count() }} Cartons)
        </div>
        <div class="card" style="border-left: 5px solid #8b5cf6;">
            @foreach($packing_details->cartons as $carton)
                <div class="item-card" style="border-left-color: #8b5cf6;">
                    <div class="item-header">
                        <span>📦 Carton #{{ $carton->carton_no }}</span>
                        <span class="status-badge" style="background:#ede9fe; color:#5b21b6;">{{ $carton->boxes->count() }} Boxes</span>
                    </div>
                    @if($carton->boxes->isNotEmpty())
                        <div class="subsection-label">Box Numbers</div>
                        <div class="box-grid">
                            @foreach($carton->boxes as $box)
                                <div class="box-badge" style="font-size: 11px;">#{{ $box->box_no }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Image Zoom Modal -->
    <div class="image-modal" id="imageModal">
        <div class="close-modal" id="closeModal"><i class="fas fa-times"></i></div>
        <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" alt="Slip" class="modal-image">
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slipImage = document.getElementById('slipImage');
        const imageModal = document.getElementById('imageModal');
        const closeModal = document.getElementById('closeModal');

        if(slipImage && imageModal && closeModal) {
            slipImage.addEventListener('click', () => imageModal.classList.add('active'));
            closeModal.addEventListener('click', () => imageModal.classList.remove('active'));
            imageModal.addEventListener('click', (e) => {
                if (e.target === imageModal) imageModal.classList.remove('active');
            });
        }
    });
</script>
@endpush