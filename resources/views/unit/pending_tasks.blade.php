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
        overflow-x: auto; /* Allow horizontal scroll */
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
        color: #000000;
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

    /* Mobile Responsive Design */
    @media (max-width: 768px) {
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
                    <label for="min_balance">Min Balance</label>
                    <input type="number" id="min_balance" name="min_balance" class="filter-input" placeholder="e.g. 50"
                        value="{{ request('min_balance') }}" min="0">
                </div>
                <div class="filter-group">
                    <label for="max_balance">Max Balance</label>
                    <input type="number" id="max_balance" name="max_balance" class="filter-input" placeholder="e.g. 1000"
                        value="{{ request('max_balance') }}" min="0">
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
                        <th>S.No</th>
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
                            <td data-label="S.No">{{ $loop->iteration }}</td>
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
                            <td colspan="11" class="text-center" style="padding: 40px 20px; color: #6b7280; border: none;">
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
