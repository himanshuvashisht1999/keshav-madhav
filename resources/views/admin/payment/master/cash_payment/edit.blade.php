@extends('admin.layouts.app')
@section('title', 'Edit Cash Payment')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Edit Cash Payment</h1>
                <p class="erp-page-subtitle">Update cash payment account head details</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.cash_payment.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.cash_payment.update') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Account name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm erp-input" placeholder="Enter account name" value="{{ $data->name }}" required>
                        @if ($errors->has('name'))
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-end pt-2 border-top">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-sync-alt mr-1"></i> Update Cash Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
