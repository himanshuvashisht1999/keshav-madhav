@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Edit bank account</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.bank_account.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="erp-card p-3">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius: 6px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.master.bank_account.update') }}" method="post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="bank_name">Bank name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="bank_name" class="form-control erp-filter-input" placeholder="Enter bank name" value="{{ old('bank_name', $data->bank_name) }}" required>
                        @if ($errors->has('bank_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('bank_name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="account_name">Account name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" id="account_name" class="form-control erp-filter-input" placeholder="Enter account name" value="{{ old('account_name', $data->account_name) }}" required>
                        @if ($errors->has('account_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('account_name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="account_number">Account number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" id="account_number" class="form-control erp-filter-input" placeholder="Enter account number" value="{{ old('account_number', $data->account_number) }}" required>
                        @if ($errors->has('account_number'))
                            <span class="invalid-feedback d-block">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="ifsc_code">IFSC code <span class="text-danger">*</span></label>
                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-control erp-filter-input" placeholder="Enter IFSC code" value="{{ old('ifsc_code', $data->ifsc_code) }}" required>
                        @if ($errors->has('ifsc_code'))
                            <span class="invalid-feedback d-block">{{ $errors->first('ifsc_code') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="branch_name">Branch name <small class="text-muted">(Optional)</small></label>
                        <input type="text" name="branch_name" id="branch_name" class="form-control erp-filter-input" placeholder="Enter branch name" value="{{ old('branch_name', $data->branch_name) }}">
                        @if ($errors->has('branch_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('branch_name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.bank_account.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Update bank account
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection