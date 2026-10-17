@extends('admin.layouts.app')
@section('title', 'Payment Adjust List')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-balance-scale text-primary"></i> Payment Adjust List
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.adjustment.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Record New Adjustment
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

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <form action="{{ route('admin.payment.adjustment.index') }}" method="GET" class="row align-items-end">
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From date</label>
                <input type="date" name="from_date" class="form-control form-control-sm erp-input" value="{{ request('from_date') }}">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To date</label>
                <input type="date" name="to_date" class="form-control form-control-sm erp-input" value="{{ request('to_date') }}">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-exchange-alt mr-1"></i> Type</label>
                <select name="type" class="form-control form-control-sm erp-input">
                    <option value="">All types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit (+)</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit (-)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-university mr-1"></i> To account</label>
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
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Search</label>
                <input type="text" name="search" class="form-control form-control-sm erp-input" placeholder="Search party, batch, note..." value="{{ request('search') }}">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-rupee-sign mr-1"></i> Amount Range</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="amount_from" class="form-control erp-input" placeholder="Min" value="{{ request('amount_from') }}">
                    <div class="input-group-prepend input-group-append">
                        <span class="input-group-text px-1" style="background: #f1f5f9; border-color: var(--erp-border, #cbd5e1);">-</span>
                    </div>
                    <input type="number" step="0.01" name="amount_to" class="form-control erp-input" placeholder="Max" value="{{ request('amount_to') }}">
                </div>
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="submit" class="btn-erp btn-erp-primary" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.payment.adjustment.index') }}" class="btn-erp btn-erp-outline" title="Reset filters">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Filter Summary Metrics Strip -->
    <div class="row mb-2">
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: rgba(220, 38, 38, 0.1); color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total DR (Debit)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹{{ number_format($totalDebit, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Debit
                </span>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total CR (Credit)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            ₹{{ number_format($totalCredit, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Credit
                </span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="table erp-table table-bordered table-hover mb-0 w-100" id="adjust_table">
                <thead>
                    <tr>
                        <th style="display:none;">ID</th>
                        <th style="width: 110px;">Date</th>
                        <th>Batch / Ref</th>
                        <th>Party / Entity</th>
                        <th>To Account</th>
                        <th class="text-center" style="width: 70px;">Items</th>
                        <th class="text-right" style="width: 130px;">Total Amount</th>
                        <th class="text-center" style="width: 75px;">Type</th>
                        <th class="text-center" style="width: 100px;">Action</th>
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
                            <span class="text-nowrap text-secondary font-weight-500">{{ date('d M Y', strtotime($first->date)) }}</span>
                        </td>
                        <td>
                            @if($isGrouped)
                                <span class="badge-erp-status badge-erp-info font-weight-bold">{{ $batchId }}</span>
                            @else
                                <span class="text-muted font-weight-bold">#{{ $first->id }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                @if($uniqueParties->count() > 1)
                                    <span class="font-weight-600 text-dark" title="{{ $uniqueParties->implode(', ') }}">
                                        <i class="fas fa-users mr-1 text-primary"></i> Multiple Parties ({{ $uniqueParties->count() }})
                                    </span>
                                @else
                                    <span class="font-weight-600 text-dark">{{ $uniqueParties->first() }}</span>
                                @endif
                                <small class="text-muted">
                                    {{ $uniqueMasters->implode(', ') }}
                                </small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-600 text-dark">{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</span>
                                <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $first->payment_mode }}</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge-erp-status badge-erp-info font-weight-bold">{{ $items->count() }}</span>
                        </td>
                        <td class="text-right font-weight-bold" style="color: #05421c;" data-search="{{ $items->sum('amount') }} {{ number_format($items->sum('amount'), 2) }}" data-order="{{ $items->sum('amount') }}">
                            ₹ {{ number_format($items->sum('amount'), 2) }}
                        </td>
                        <td class="text-center">
                            @if($first->type == 'credit')
                                <span class="badge-erp-status badge-erp-success font-weight-bold">CR</span>
                            @else
                                <span class="badge-erp-status font-weight-bold" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">DR</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center" style="gap: 5px;">
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
@endsection

@push('scripts')
<script>
    $(function () {
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        }
        $('#adjust_table').DataTable({
            "order": [[0, "desc"]],
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'>>rt<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>"
        });
    });
</script>
@endpush
