@extends('admin.layouts.app')

@section('content')
<style>
    /* Enterprise ERP Design System */
    :root {
        --erp-bg: #f8fafc;
        --erp-card-bg: #ffffff;
        --erp-border: #e2e8f0;
        --erp-primary: #05421c;
        --erp-text-main: #1e293b;
        --erp-text-muted: #64748b;
        --erp-header-bg: #f8fafc;
        --erp-radius: 6px;
        --erp-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    .erp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #05421c 0%, #0a5c28 100%);
        border-radius: 8px;
        margin-bottom: 1rem;
        color: #fff;
    }

    .erp-header h1 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
    }

    .erp-card {
        background: var(--erp-card-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        box-shadow: var(--erp-shadow);
        margin-bottom: 1rem;
    }

    .erp-card-header {
        background-color: #ffffff;
        border-bottom: 1px solid var(--erp-border);
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .erp-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #05421c;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .erp-card-body {
        padding: 1rem;
    }

    /* Form Controls */
    .erp-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--erp-text-main);
        margin-bottom: 0.35rem;
        display: block;
    }

    .select2-container--default .select2-selection--single {
        height: 36px;
        border: 1px solid #ced4da;
        border-radius: var(--erp-radius);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px;
        color: #495057;
        font-size: 0.88rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
    }

    .btn-erp {
        font-size: 0.85rem;
        padding: 0.4rem 0.85rem;
        border-radius: var(--erp-radius);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.15s ease-in-out;
    }

    .btn-erp-primary {
        background-color: #05421c;
        border: 1px solid #05421c;
        color: white;
    }

    .btn-erp-primary:hover {
        background-color: #043617;
        color: #fcee21;
    }

    .btn-erp-default {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .btn-erp-default:hover {
        background-color: #e2e8f0;
    }

    /* Data Tables */
    .erp-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .erp-table th {
        background-color: #edf7e4;
        color: #05421c;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.65rem 0.75rem;
        border-bottom: 2px solid #c3e6cb;
        border-top: none;
    }

    .erp-table td {
        padding: 0.65rem 0.75rem;
        font-size: 0.88rem;
        vertical-align: middle;
        border-top: 1px solid #e2e8f0;
        color: #1e293b;
    }

    .erp-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .erp-badge {
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 4px;
    }

    .erp-badge-light {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    
    .erp-badge-info {
        background-color: #edf7e4;
        color: #05421c;
        border: 1px solid #c3e6cb;
    }

    .erp-badge-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--erp-text-muted);
    }

    .empty-state i {
        font-size: 2.5rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
</style>

<div class="content-wrapper erp-page p-2">
    <!-- Top Header Bar -->
    <div class="erp-header shadow-sm">
        <div class="d-flex align-items-center">
            <h1 class="mb-0">
                <i class="fas fa-boxes mr-2 text-warning"></i> 
                Packing Operations | <span class="text-white-50" style="font-size: 0.95rem; font-weight: normal;">Slip #{{ $slip->id ?? '' }}</span>
            </h1>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('admin.packing.index') }}" class="btn-erp btn-erp-outline" style="color: #fff; border-color: rgba(255,255,255,0.4); background: transparent;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @if($slip && $slip->slip_file)
                <button type="button" class="btn btn-erp btn-erp-default" onclick="window.open('{{ asset('assets/production_slips/' . $slip->slip_file) }}', '_blank')">
                    <i class="fas fa-file-image text-warning"></i> View Source Document
                </button>
            @endif
        </div>
    </div>

    <div class="container-fluid px-3">
        
        <!-- Filter / Order Selection Area -->
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fas fa-filter"></i> Order Selection Criteria</h3>
            </div>
            <div class="erp-card-body">
                <form method="GET" action="{{ route('admin.packing.processNew', $slip->id) }}" id="orderSelectForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="erp-label">Active Production Order</label>
                            <div class="d-flex" style="gap: 10px;">
                                <div style="flex-grow: 1;">
                                    <select name="order_id" class="form-control select2" onchange="document.getElementById('orderSelectForm').submit();">
                                        <option value="">-- Select Order Reference --</option>
                                        @foreach($active_orders as $activeOrder)
                                            <option value="{{ $activeOrder->id }}" {{ ($order && $order->id == $activeOrder->id) ? 'selected' : '' }}>
                                                [{{ $activeOrder->sku ?? 'NO-SKU' }}] {{ $activeOrder->customer->name ?? 'Unknown Customer' }} - {{ strtoupper($activeOrder->order_type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($order)
                                    <div>
                                        <a href="{{ route('admin.packing.processNew', $slip->id) }}" class="btn btn-erp btn-erp-default text-danger" title="Clear Selection">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($order)
                        <div class="col-md-6 border-left">
                            <label class="erp-label">Selected Order Details</label>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                <tr>
                                    <td width="100" class="text-muted font-weight-bold">Customer:</td>
                                    <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                    <td width="100" class="text-muted font-weight-bold">Type:</td>
                                    <td><span class="erp-badge erp-badge-light">{{ strtoupper($order->order_type) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold">SKU Ref:</td>
                                    <td>{{ $order->sku ?? 'N/A' }}</td>
                                    <td class="text-muted font-weight-bold">Order ID:</td>
                                    <td>#{{ $order->id }}</td>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Grid: Lots -->
        @if($order)
            <form method="POST" action="{{ route('admin.packing.saveSelectedLots', $slip->id) }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fas fa-list-ol"></i> Lot Distribution Data</h3>
                        <div class="card-tools m-0 d-flex align-items-center" style="gap: 12px;">
                            <div class="badge badge-light border py-2 px-3 text-dark d-flex align-items-center shadow-sm" style="font-size: 0.85rem; gap: 8px; border-radius: 6px;">
                                <span>Selected: <strong id="selectedLotsCount" style="color: #05421c;">0</strong> Lots</span>
                                <span class="text-muted">|</span>
                                <span>Gross Qty: <strong id="selectedGrossQty" class="text-dark">0</strong></span>
                                <span class="text-muted">|</span>
                                <span>Total Pending to Pack: <strong id="selectedPendingQty" class="text-success" style="font-size: 0.95rem;">0</strong> pcs</span>
                            </div>
                            <button type="submit" class="btn-erp btn-erp-primary text-white shadow-sm">
                                <i class="fas fa-save"></i> Save Selected Lots
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive p-0">
                        <table class="table erp-table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">
                                        <input type="checkbox" id="selectAllLots">
                                    </th>
                                    <th width="15%">Lot ID</th>
                                <th width="30%">Design Ref</th>
                                <th width="25%">Size Profile</th>
                                <th width="15%" class="text-center">Gross Qty</th>
                                <th width="15%" class="text-right pr-4">Pending (To Pack)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(collect($unit_lots)->where('remaining_quantity', '>', 0) as $lot)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="lots[]" class="lot-checkbox" value="{{ $lot->lot_no }}" data-gross="{{ $lot->quantity }}" data-pending="{{ $lot->remaining_quantity }}" {{ in_array($lot->lot_no, $selected_lots ?? []) ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <span class="erp-badge erp-badge-info">LOT-{{ str_pad($lot->lot_no, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="font-weight-bold">
                                        {{ $lot->design_number }}
                                    </td>
                                    <td>
                                        {{ $lot->size_set_name }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($lot->quantity) }}
                                    </td>
                                    <td class="text-right pr-4 font-weight-bold text-success">
                                        {{ number_format($lot->remaining_quantity) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fas fa-clipboard-check"></i>
                                            <h5>No Pending Lots</h5>
                                            <p class="text-sm">There are no lots waiting to be packed for this order at the current stage.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(collect($unit_lots)->where('remaining_quantity', '>', 0)->count() > 0)
                        <tfoot>
                            <tr class="bg-light font-weight-bold" style="border-top: 2px solid #dee2e6;">
                                <td colspan="4" class="text-right py-2">
                                    Total Selected (<span id="footerSelectedCount">0</span> Lots):
                                </td>
                                <td class="text-center py-2 text-dark font-weight-bold" id="footerGrossTotal">0</td>
                                <td class="text-right pr-4 py-2 font-weight-bold text-success" style="font-size: 1rem;" id="footerPendingTotal">0</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            </form>
        @else
            <div class="erp-card bg-light border-0">
                <div class="empty-state py-5">
                    <i class="fas fa-search mb-3"></i>
                    <h4>Awaiting Order Selection</h4>
                    <p>Please select a production order from the criteria panel above to load packing data.</p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default',
            width: '100%'
        });

        function updateSelectedTotals() {
            let totalGross = 0;
            let totalPending = 0;
            let selectedCount = 0;

            $('.lot-checkbox:checked').each(function() {
                selectedCount++;
                totalGross += parseFloat($(this).data('gross')) || 0;
                totalPending += parseFloat($(this).data('pending')) || 0;
            });

            $('#selectedLotsCount').text(selectedCount);
            $('#selectedGrossQty').text(totalGross.toLocaleString());
            $('#selectedPendingQty').text(totalPending.toLocaleString());

            $('#footerSelectedCount').text(selectedCount);
            $('#footerGrossTotal').text(totalGross.toLocaleString());
            $('#footerPendingTotal').text(totalPending.toLocaleString());
        }

        $('#selectAllLots').on('change', function() {
            $('.lot-checkbox').prop('checked', $(this).prop('checked'));
            updateSelectedTotals();
        });

        // Update select all if individuals are unchecked
        $('.lot-checkbox').on('change', function() {
            if ($('.lot-checkbox:checked').length == $('.lot-checkbox').length) {
                $('#selectAllLots').prop('checked', true);
            } else {
                $('#selectAllLots').prop('checked', false);
            }
            updateSelectedTotals();
        });
        
        // Initial check on load
        if ($('.lot-checkbox:checked').length > 0 && $('.lot-checkbox:checked').length == $('.lot-checkbox').length) {
            $('#selectAllLots').prop('checked', true);
        }
        updateSelectedTotals();
    });
</script>
@endsection