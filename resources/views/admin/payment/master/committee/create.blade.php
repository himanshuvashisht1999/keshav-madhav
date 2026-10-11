@extends('admin.layouts.app')
@section('title', 'Create Committee')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-users-cog text-primary mr-2"></i> Create Committee
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.master.committee.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Committees
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="erp-card p-3 mb-2">
        <form action="{{ route('admin.payment.master.committee.store') }}" method="post">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control erp-input" placeholder="Enter Committee Name"
                        value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Amount (Monthly/Total)</label>
                    <input type="number" step="0.01" name="amount" class="form-control erp-input" placeholder="Enter Amount (Optional)"
                        value="{{ old('amount') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Balance</label>
                    <input type="number" step="0.01" name="balance" class="form-control erp-input" placeholder="Enter Balance"
                        value="{{ old('balance', 0) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Balance Type</label>
                    <select name="balance_type" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                        <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Period (e.g., 12 Months)</label>
                    <input type="text" name="period" class="form-control erp-input" placeholder="Enter Period (Optional)"
                        value="{{ old('period') }}">
                </div>
                <div class="col-md-12 text-right mt-2">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Submit Committee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
