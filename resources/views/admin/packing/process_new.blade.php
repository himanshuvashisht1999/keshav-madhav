@extends('admin.layouts.app')

@section('content')
<style>
    /* Enterprise ERP Design System */
    :root {
        --erp-bg: #f4f6f9;
        --erp-card-bg: #ffffff;
        --erp-border: #e0e4e8;
        --erp-primary: #0056b3;
        --erp-text-main: #333333;
        --erp-text-muted: #6c757d;
        --erp-header-bg: #f8f9fa;
        --erp-radius: 4px;
        --erp-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .content-wrapper {
        background-color: var(--erp-bg);
    }

    .erp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: var(--erp-card-bg);
        border-bottom: 1px solid var(--erp-border);
        margin-bottom: 1rem;
    }

    .erp-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--erp-text-main);
        margin: 0;
    }

    .erp-card {
        background: var(--erp-card-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        box-shadow: var(--erp-shadow);
        margin-bottom: 1.5rem;
    }

    .erp-card-header {
        background-color: var(--erp-header-bg);
        border-bottom: 1px solid var(--erp-border);
        padding: 0.75rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .erp-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--erp-text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .erp-card-body {
        padding: 1.25rem;
    }

    /* Form Controls */
    .erp-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--erp-text-main);
        margin-bottom: 0.5rem;
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
        font-size: 0.9rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
    }

    .btn-erp {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border-radius: var(--erp-radius);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: background-color 0.15s ease-in-out;
    }

    .btn-erp-primary {
        background-color: var(--erp-primary);
        border: 1px solid var(--erp-primary);
        color: white;
    }

    .btn-erp-primary:hover {
        background-color: #004494;
        color: white;
    }

    .btn-erp-default {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #333;
    }

    .btn-erp-default:hover {
        background-color: #e2e6ea;
    }

    /* Data Tables */
    .erp-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .erp-table th {
        background-color: #f1f3f5;
        color: #495057;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 0.75rem;
        border-bottom: 2px solid #dee2e6;
        border-top: none;
    }

    .erp-table td {
        padding: 0.75rem;
        font-size: 0.9rem;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        color: #333;
    }

    .erp-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .erp-badge {
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 0.25rem;
    }

    .erp-badge-light {
        background-color: #e9ecef;
        color: #495057;
        border: 1px solid #ced4da;
    }
    
    .erp-badge-info {
        background-color: #cff4fc;
        color: #055160;
        border: 1px solid #b6effb;
    }

    .erp-badge-success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--erp-text-muted);
    }

    .empty-state i {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="content-wrapper">
    <!-- Top Header Bar -->
    <div class="erp-header shadow-sm">
        <h1>
            <i class="fas fa-boxes mr-2 text-primary"></i> 
            Packing Operations | <span class="text-muted" style="font-size: 1rem; font-weight: normal;">Slip #{{ $slip->id ?? '' }}</span>
        </h1>
        <div>
            @if($slip && $slip->slip_file)
                <button type="button" class="btn btn-erp btn-erp-default" onclick="window.open('{{ asset('assets/production_slips/' . $slip->slip_file) }}', '_blank')">
                    <i class="fas fa-file-image text-info"></i> View Source Document
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
                        <div class="card-tools m-0 d-flex align-items-center" style="gap: 15px;">
                            <span class="text-muted" style="font-size: 0.85rem;">Showing available lots for current unit</span>
                            <button type="submit" class="btn-erp btn-erp-primary text-white">
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
                                        <input type="checkbox" name="lots[]" class="lot-checkbox" value="{{ $lot->lot_no }}" {{ in_array($lot->lot_no, $selected_lots ?? []) ? 'checked' : '' }}>
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

        $('#selectAllLots').on('change', function() {
            $('.lot-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Update select all if individuals are unchecked
        $('.lot-checkbox').on('change', function() {
            if ($('.lot-checkbox:checked').length == $('.lot-checkbox').length) {
                $('#selectAllLots').prop('checked', true);
            } else {
                $('#selectAllLots').prop('checked', false);
            }
        });
        
        // Initial check on load
        if ($('.lot-checkbox:checked').length > 0 && $('.lot-checkbox:checked').length == $('.lot-checkbox').length) {
            $('#selectAllLots').prop('checked', true);
        }
    });
</script>
@endsection