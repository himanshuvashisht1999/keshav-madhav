@extends('admin.layouts.app')
@section('title', 'Create Bank Account')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Create Bank Account</h1>
                <p class="erp-page-subtitle">Register a new company bank account with opening balance</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.bank_account.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.bank_account.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Bank name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control form-control-sm erp-input" placeholder="e.g. HDFC Bank, ICICI Bank" value="{{ old('bank_name') }}" required>
                        @if ($errors->has('bank_name'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('bank_name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Account name / title <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control form-control-sm erp-input" placeholder="e.g. Snapkid Current Account" value="{{ old('account_name') }}" required>
                        @if ($errors->has('account_name'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('account_name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Account number</label>
                        <input type="text" name="account_number" class="form-control form-control-sm erp-input" placeholder="Enter bank account number" value="{{ old('account_number') }}">
                        @if ($errors->has('account_number'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">IFSC code</label>
                        <input type="text" name="ifsc_code" class="form-control form-control-sm erp-input text-uppercase" placeholder="e.g. HDFC0001234" value="{{ old('ifsc_code') }}">
                        @if ($errors->has('ifsc_code'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('ifsc_code') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Branch name</label>
                        <input type="text" name="branch_name" class="form-control form-control-sm erp-input" placeholder="Enter branch location" value="{{ old('branch_name') }}">
                        @if ($errors->has('branch_name'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('branch_name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="erp-label">Opening balance (₹)</label>
                        <input type="number" step="0.01" name="balance" class="form-control form-control-sm erp-input" placeholder="0.00" value="{{ old('balance', 0) }}">
                        @if ($errors->has('balance'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('balance') }}</span>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="erp-label">Balance type</label>
                        <select name="balance_type" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                            <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit (Cr)</option>
                            <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit (Dr)</option>
                        </select>
                        @if ($errors->has('balance_type'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('balance_type') }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-end pt-2 border-top">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Save Bank Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection