@extends('layouts.unit')

@section('title', 'Pending Tasks')

@section('content')
<style>
    .filter-section {
        background: white;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }
    .filter-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    .filter-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    .filter-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
    }
    .btn-apply {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        flex: 1;
    }
    .btn-clear {
        background: #f3f4f6;
        color: #4b5563;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 1;
    }

    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    .excel-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .excel-table th {
        background: #f8f9fa;
        color: #4b5563;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;
        white-space: nowrap;
    }
    .excel-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
        vertical-align: middle;
    }
    .excel-table tr:hover {
        background-color: #f9fafb;
    }
    .badge-delayed {
        background: #fee2e2;
        color: #b91c1c;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 6px;
    }
    .text-center { text-align: center; }

    /* Mobile Responsive Card Design */
    @media (max-width: 768px) {
        .table-container {
            border: none;
            box-shadow: none;
            background: transparent;
        }
        .excel-table thead {
            display: none;
        }
        .excel-table, .excel-table tbody {
            display: block;
            width: 100%;
        }
        .excel-table tr {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background: #fff;
            padding: 12px 14px;
        }
        .excel-table td {
            display: flex;
            flex-direction: column;
            border: none;
            padding: 0;
            text-align: left;
            min-height: auto;
        }
        .excel-table td::before {
            content: attr(data-label);
            position: relative;
            left: 0;
            width: auto;
            font-size: 10px;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        /* Make Lot No full width and styled as header */
        .excel-table td[data-label="Lot No"] {
            width: 100%;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            border-bottom: 1px dashed #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .excel-table td[data-label="Lot No"]::before {
            display: none;
        }
        
        /* Other fields take 50% width to form a 2-column grid */
        .excel-table td:not([data-label="Lot No"]) {
            width: 50%;
            margin-bottom: 10px;
            font-size: 13px;
            color: #374151;
            font-weight: 600;
        }
        
        /* Specific adjustments for emphasis */
        .excel-table td[data-label="Pending Qty"] {
            font-size: 14px;
        }
        
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid py-4">
        
        <!-- Filter Form -->
        <form action="{{ route('unit.pending-tasks') }}" method="GET" class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="lot_no">Lot No</label>
                    <input type="text" id="lot_no" name="lot_no" class="filter-input" placeholder="e.g. 557"
                        value="{{ request('lot_no') }}">
                </div>
                <div class="filter-group">
                    <label for="start_date">Assigned From</label>
                    <input type="date" id="start_date" name="start_date" class="filter-input"
                        value="{{ request('start_date') }}">
                </div>
                <div class="filter-group">
                    <label for="end_date">Estimated By</label>
                    <input type="date" id="end_date" name="end_date" class="filter-input"
                        value="{{ request('end_date') }}">
                </div>
                <div class="filter-group">
                    <label for="is_delayed">Status</label>
                    <select id="is_delayed" name="is_delayed" class="filter-input">
                        <option value="">All Pending</option>
                        <option value="1" {{ request('is_delayed') == '1' ? 'selected' : '' }}>Only Delayed</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-apply"><i class="fas fa-search"></i> Apply Filters</button>
                <a href="{{ route('unit.pending-tasks') }}" class="btn-clear">Clear All</a>
            </div>
        </form>

        <div class="table-container">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Lot No</th>
                        <th>Cutting Pieces</th>
                        <th>Size Set</th>
                        <th>Assign Qty</th>
                        <th>Delivery QTY</th>
                        <th>Balance</th>
                        <th>Sent By</th>
                        <th>Design Number</th>
                        <th>Assign Date</th>
                        <th>Estimated Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grouped as $task)
                        <tr>
                            <td data-label="Lot No">
                                <strong>{{ $task['lot_no'] }}</strong>
                            </td>
                            <td data-label="Cutting Pieces">{{ number_format($task['total_cutting_pieces']) }}</td>
                            <td data-label="Size Set">{{ $task['size_set'] }}</td>
                            <td data-label="Assign Qty">{{ number_format($task['total_assigned']) }}</td>
                            <td data-label="Delivery QTY">{{ number_format($task['total_assigned'] - $task['total_pending']) }}</td>
                            <td data-label="Balance" style="font-weight: 700; color: #d97706;">{{ number_format($task['total_pending']) }}</td>
                            <td data-label="Sent By">{{ $task['sent_by'] }}</td>
                            <td data-label="Design Number">{{ $task['design_no'] }}</td>
                            <td data-label="Assign Date">{{ $task['assigned_date'] ? \Carbon\Carbon::parse($task['assigned_date'])->format('d M Y') : '-' }}</td>
                            <td data-label="Estimated Date">{{ $task['estimated_date'] ? \Carbon\Carbon::parse($task['estimated_date'])->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 40px 20px; color: #6b7280; border: none;">
                                <i class="fas fa-inbox mb-2" style="font-size: 32px; color: #d1d5db;"></i><br><br>
                                <span style="font-weight: 600; font-size: 16px;">No pending tasks found</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection
