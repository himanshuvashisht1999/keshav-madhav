@extends('admin.layouts.app')

@section('content')
<style>
    /* ENTERPRISE ERP DESIGN (SAP / ZOHO STYLE) */
    :root {
        --erp-bg: #f5f6f8;
        --erp-panel-bg: #ffffff;
        --erp-border: #d1d5db;
        --erp-primary: #0f62fe;
        --erp-primary-light: #e0e8ff;
        --erp-text-main: #111827;
        --erp-text-muted: #6b7280;
        --erp-active-bg: #f4f8ff;
        --erp-radius: 4px;
        --font-base: 13px;
    }

    .erp-container {
        padding: 16px;
        background: var(--erp-bg);
        min-height: calc(100vh - 60px);
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        font-size: var(--font-base);
        color: var(--erp-text-main);
    }

    .erp-card {
        background: var(--erp-panel-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .erp-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--erp-border);
        background-color: #fafafa;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .erp-card-body {
        padding: 16px;
    }

    .erp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .erp-table th, .erp-table td {
        border: 1px solid var(--erp-border);
        padding: 8px 12px;
        text-align: left;
    }

    .erp-table th {
        background-color: #f1f5f9;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .erp-table tbody tr:hover {
        background-color: var(--erp-active-bg);
    }

    .btn-primary {
        background-color: var(--erp-primary);
        color: #fff;
        border: none;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
    }
    .btn-primary:hover {
        background-color: #0353e9;
    }

    .btn-success {
        background-color: #10b981;
        color: #fff;
        border: none;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
    }
    .btn-success:hover {
        background-color: #059669;
    }

    .form-group {
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-control {
        border: 1px solid var(--erp-border);
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 13px;
        min-width: 250px;
    }
</style>

<div class="content-wrapper">
    <!-- <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>WIP Complete Report</h1>
                </div>
            </div>
        </div>
    </section> -->

    <section class="content">
        <div class="container-fluid">
            <div class="erp-container" style="min-height: auto; padding: 0;">
                <div class="erp-card" id="filter-card">
                    <div class="erp-card-header" style="cursor: pointer;" onclick="$('#filter-form-body').slideToggle();">
                        <span><i class="fas fa-filter"></i> WIP Complete Report Filters</span>
                        <i class="fas fa-chevron-down" style="color: #9ca3af; font-size: 14px;"></i>
                    </div>
                    <div class="erp-card-body" id="filter-form-body">
                        <form action="{{ route('admin.report.wip-complete') }}" method="GET">
                            <div class="row align-items-end" style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 0;">
                                <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                                    <label style="font-weight: 600;">Select Customer:</label>
                                    <select name="customer_id" class="form-control select2" onchange="this.form.submit()">
                                        <option value="">-- Select Customer --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $selectedCustomer == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($selectedCustomer)
                                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                                        <label style="font-weight: 600;">Select Orders:</label>
                                        <select name="order_ids[]" class="form-control select2" multiple data-placeholder="-- All Orders --">
                                            @foreach($availableOrders as $id => $sku)
                                                <option value="{{ $id }}" {{ in_array($id, $selectedOrders) ? 'selected' : '' }}>{{ $sku }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                                        <label style="font-weight: 600;">Select Lots:</label>
                                        <select name="lot_nos[]" class="form-control select2" multiple data-placeholder="-- All Lots --">
                                            @foreach($availableLots as $no => $no2)
                                                <option value="{{ $no }}" {{ in_array($no, $selectedLots) ? 'selected' : '' }}>{{ $no }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group" style="display: flex; gap: 8px; margin-bottom: 0; align-items: flex-end;">
                                        <button type="submit" class="btn btn-primary" style="height: 38px;">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        
                                        @if(!empty($data))
                                            @php
                                                $exportParams = request()->all();
                                                $exportParams['export'] = 1;
                                            @endphp
                                            <a href="{{ route('admin.report.wip-complete', $exportParams) }}" class="btn-success btn" style="height: 38px; display: inline-flex; align-items: center;">
                                                <i class="fas fa-file-excel"></i> &nbsp;Export
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedCustomer)
                    <div class="erp-card">
                        <div class="erp-card-header">
                            Customer Data (Orders, Designs, Lots)
                        </div>
                        <div class="erp-card-body auto-scroll-table" style="padding: 0; overflow-x: auto; max-height: calc(100vh - 150px); overflow-y: auto; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            @if(empty($data))
                                <div style="padding: 24px; text-align: center; color: #6b7280;">
                                    No lots found for this customer.
                                </div>
                            @else
                                <table class="erp-table">
                                    <thead>
                                        <tr>
                                            <th>Order No</th>
                                            <th>Order Date</th>
                                            <th>Design No</th>
                                            <th>Lot No</th>
                                            <th>Lot Qty</th>
                                            <th>Lot Status</th>
                                            <th>Current Stage</th>
                                            @foreach($master_stages as $stage)
                                                <th>{{ $stage->name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $row)
                                            <tr>
                                                <td>{{ $row['order']->sku ?? '-' }}</td>
                                                <td>{{ $row['order']->created_at ? $row['order']->created_at->format('d M, Y') : '-' }}</td>
                                                <td>{{ $row['set']->design_number ?? '-' }}</td>
                                                <td>{{ $row['lot']->lot_no }}</td>
                                                <td>{{ $row['lot_quantity'] ?? 0 }}</td>
                                                <td>
                                                    @if($row['lot']->status == 0) <span style="color: #f59e0b; font-weight: bold;">Pending</span>
                                                    @elseif($row['lot']->status == 1) <span style="color: #3b82f6; font-weight: bold;">Processing</span>
                                                    @elseif($row['lot']->status == 2) <span style="color: #10b981; font-weight: bold;">Completed</span>
                                                    @else <span style="color: #ef4444; font-weight: bold;">Cancelled</span>
                                                    @endif
                                                </td>
                                                <td>{{ getLastCurrentStage($row['lot']->lot_no) }}</td>

                                                <!-- STAGE WISE STATUS -->
                                                @foreach($master_stages as $stage)
                                                    @php
                                                        $d = getLotDetails($row['lot']->lot_no, $stage->id);
                                                    @endphp
                                                    <td style="font-size: 11px;">
                                                        @if($d && $d['time_allocation'])
                                                            <div style="font-weight: 600; color: #111827; margin-bottom: 2px;">
                                                                In: <span style="color: #2563eb">{{ $d['quantity'] }}</span> | 
                                                                Out: <span style="color: #16a34a">{{ $d['quantity'] - $d['remaining_quantity'] }}</span>
                                                            </div>
                                                            <div style="font-size: 10px; color: #d97706; font-weight: 600; margin-bottom: 4px;">WIP: {{ $d['remaining_quantity'] }}</div>
                                                            <div style="color: #6b7280; font-size: 10px;">Unit: <span style="font-weight: 600; color: #374151;">{{ $d['unit_name'] ?? '-' }}</span></div>
                                                            <div style="color: #6b7280; font-size: 10px;">Start: {{ $d['start_date'] ? \Carbon\Carbon::parse($d['start_date'])->format('d M y') : '-' }}</div>
                                                            @php
                                                                $rem = (int)$d['remaining_quantity'];
                                                                $compDate = $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time']) : null;
                                                            @endphp
                                                            <div style="color: #6b7280; font-size: 10px;">Comp: {{ ($rem == 0 && $compDate) ? $compDate->format('d M y') : '-' }}</div>
                                                        @else
                                                            <span style="color: #9ca3af;">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                allowClear: true
            });
        }

        // Auto-hide filters on table scroll down
        let isScrollingDown = false;
        $('.auto-scroll-table').on('scroll', function() {
            let scrollTop = $(this).scrollTop();
            if (scrollTop > 80 && !isScrollingDown) {
                $('#filter-form-body').slideUp(200);
                isScrollingDown = true;
            } else if (scrollTop < 20 && isScrollingDown) {
                $('#filter-form-body').slideDown(200);
                isScrollingDown = false;
            }
        });
    });
</script>
@endsection
