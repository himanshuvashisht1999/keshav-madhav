@extends('admin.layouts.app')
@section('title', 'Payment Adjust List')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Payment Adjust List</h1>
                <p class="erp-page-subtitle">Adjustment transactions and multi-entity account balancing</p>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <div class="hud-stat-box" style="padding: 4px 10px; font-size: 0.8rem;">
                    <div class="hud-stat-label text-muted" style="font-size: 0.65rem;">Total DR</div>
                    <div class="hud-stat-value text-danger font-weight-bold">
                        <i class="fas fa-arrow-up mr-1"></i>₹{{ number_format($totalDebit, 2) }}
                    </div>
                </div>
                <div class="hud-stat-box" style="padding: 4px 10px; font-size: 0.8rem;">
                    <div class="hud-stat-label text-muted" style="font-size: 0.65rem;">Total CR</div>
                    <div class="hud-stat-value text-success font-weight-bold">
                        <i class="fas fa-arrow-down mr-1"></i>₹{{ number_format($totalCredit, 2) }}
                    </div>
                </div>
                <a href="{{ route('admin.payment.adjustment.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> Record New Adjustment
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="erp-card mb-2 p-2">
            <form action="{{ route('admin.payment.adjustment.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-2 mb-2">
                    <label class="erp-label">From date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm erp-input" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="erp-label">To date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm erp-input" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="erp-label">Type</label>
                    <select name="type" class="form-control form-control-sm erp-input">
                        <option value="">All types</option>
                        <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="erp-label">To account</label>
                    <select name="payment_account_id" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                        <option value="">All accounts</option>
                        <optgroup label="Bank Accounts">
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('payment_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->bank_name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Cash Accounts">
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('payment_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <label class="erp-label">Amt from</label>
                    <input type="number" step="0.01" name="amount_from" class="form-control form-control-sm erp-input px-1" placeholder="Min" value="{{ request('amount_from') }}">
                </div>
                <div class="col-md-1 mb-2">
                    <label class="erp-label">Amt to</label>
                    <input type="number" step="0.01" name="amount_to" class="form-control form-control-sm erp-input px-1" placeholder="Max" value="{{ request('amount_to') }}">
                </div>
                <div class="col-md-1 mb-2 d-flex" style="gap: 4px;">
                    <button type="submit" class="btn-erp btn-erp-primary flex-fill text-center" title="Apply filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.payment.adjustment.index') }}" class="btn-erp btn-erp-secondary text-center" title="Reset filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="erp-card p-0">
            <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
                <span class="font-weight-bold" style="font-size: 0.85rem; color: #0f172a;">
                    Adjustment history ({{ $grouped->count() }} batches)
                </span>
            </div>
            <div class="table-responsive">
                <table class="table erp-table table-bordered table-hover mb-0 w-100" id="adjust_table">
                    <thead>
                        <tr>
                            <th style="display:none;">ID</th>
                            <th style="width: 120px;">Date</th>
                            <th>Batch / ref</th>
                            <th>Party / entity</th>
                            <th>To account</th>
                            <th class="text-center" style="width: 70px;">Items</th>
                            <th class="text-right" style="width: 130px;">Total amount</th>
                            <th class="text-center" style="width: 80px;">Type</th>
                            <th class="text-center" style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $batchId => $items)
                        @php 
                            $first = $items->first(); 
                            $uniqueParties = $items->map(function($i) { return $i->entity_name; })->unique();
                            $uniqueMasters = $items->map(function($i) { return $i->master->name ?? 'N/A'; })->unique();
                            $isGrouped = !str_starts_with($batchId, 'unique_');
                        @endphp
                        <tr>
                            <td style="display:none;">{{ $first->id }}</td>
                            <td data-sort="{{ strtotime($first->date) }}">
                                {{ date('d-M-Y', strtotime($first->date)) }}
                            </td>
                            <td>
                                @if($isGrouped)
                                    <code class="text-primary font-weight-bold">{{ $batchId }}</code>
                                @else
                                    <span class="text-muted font-weight-bold">#{{ $first->id }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($uniqueParties->count() > 1)
                                        <span class="font-weight-bold" title="{{ $uniqueParties->implode(', ') }}">
                                            <i class="fas fa-users mr-1 text-muted"></i> Multiple Parties ({{ $uniqueParties->count() }})
                                        </span>
                                    @else
                                        <span class="font-weight-bold">{{ $uniqueParties->first() }}</span>
                                    @endif
                                    <small class="text-muted">
                                        {{ $uniqueMasters->implode(', ') }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-semibold">{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</span>
                                    <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $first->payment_mode }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info px-2 py-1">{{ $items->count() }}</span>
                            </td>
                            <td class="text-right font-weight-bold" data-search="{{ $items->sum('amount') }} {{ number_format($items->sum('amount'), 2) }}" data-order="{{ $items->sum('amount') }}">
                                ₹{{ number_format($items->sum('amount'), 2) }}
                            </td>
                            <td class="text-center">
                                @if($first->type == 'credit')
                                    <span class="badge badge-success px-2 py-1">CR</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1">DR</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                    <a href="{{ route('admin.payment.adjustment.show', $batchId) }}" class="erp-action-btn erp-btn-view" title="View details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.payment.adjustment.edit', $batchId) }}" class="erp-action-btn erp-btn-edit" title="Edit batch">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.payment.adjustment.delete', $batchId) }}" class="erp-action-btn erp-btn-delete" title="Delete & reverse" onclick="return confirm('WARNING: This will delete the batch and REVERSE all account balances. Continue?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#adjust_table').DataTable({
            "order": [[0, "desc"]],
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]]
        });
    });
</script>
@endpush
