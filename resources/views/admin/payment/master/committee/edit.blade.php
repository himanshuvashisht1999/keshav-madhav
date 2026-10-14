@extends('admin.layouts.app')
@section('title', 'Edit Committee')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-users-cog text-primary mr-2"></i> Edit Committee: {{ $data->name }}
        </div>
        <div class="erp-header-actions">
            <div class="hud-stat-box mr-2">
                <span class="hud-stat-label">Current Balance</span>
                <span class="hud-stat-value {{ $data->balance >= 0 ? 'text-success' : 'text-danger' }}">
                    ₹ {{ number_format(abs($data->balance), 2) }} {{ $data->balance >= 0 ? 'Cr' : 'Dr' }}
                </span>
            </div>
            <a href="{{ route('admin.payment.master.committee.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Committees
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="erp-card p-3 mb-2">
        <form action="{{ route('admin.payment.master.committee.update') }}" method="post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control erp-input" placeholder="Enter Committee Name"
                        value="{{ $data->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Amount (Monthly/Total)</label>
                    <input type="number" step="0.01" name="amount" class="form-control erp-input" placeholder="Enter Amount (Optional)"
                        value="{{ $data->amount }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="erp-label">Current Balance</label>
                    <div class="form-control erp-input bg-light d-flex align-items-center justify-content-between">
                        <span class="font-weight-bold">₹ {{ number_format(abs($data->balance), 2) }}</span>
                        @if($data->balance >= 0)
                            <span class="badge badge-success">Cr</span>
                        @else
                            <span class="badge badge-danger">Dr</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="erp-label">Opening Balance</label>
                    <input type="number" step="0.01" name="balance" class="form-control erp-input" placeholder="Enter Opening Balance"
                        value="{{ $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0 }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="erp-label">Opening Balance Type</label>
                    <select name="balance_type" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="Credit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Credit') ? 'selected' : ($data->balance >= 0 ? 'selected' : '') }}>Credit</option>
                        <option value="Debit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Debit') ? 'selected' : ($data->balance < 0 ? 'selected' : '') }}>Debit</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Period (e.g., 12 Months)</label>
                    <input type="text" name="period" class="form-control erp-input" placeholder="Enter Period (Optional)"
                        value="{{ $data->period }}">
                </div>
                <div class="col-md-12 text-right mt-2">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Update Committee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
