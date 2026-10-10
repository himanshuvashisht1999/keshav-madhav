@extends('admin.layouts.app')
@section('title', 'Create Contractor')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Create Contractor</h1>
                <p class="erp-page-subtitle">Add a new job work contractor to the register</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.contractor.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.contractor.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="erp-label">Contractor name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm erp-input @error('name') is-invalid @enderror" id="name" placeholder="Enter contractor name" required value="{{ old('name') }}">
                        @error('name')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="erp-label">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm erp-input @error('phone') is-invalid @enderror" id="phone" placeholder="Phone number" value="{{ old('phone') }}">
                        @error('phone')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="erp-label">Address</label>
                        <textarea name="address" class="form-control form-control-sm erp-input @error('address') is-invalid @enderror" id="address" rows="2" placeholder="Full address">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Opening balance (₹)</label>
                        <input type="number" step="0.01" name="balance" class="form-control form-control-sm erp-input" placeholder="0.00" value="{{ old('balance', 0) }}">
                        @error('balance')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Balance type</label>
                        <select name="balance_type" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                            <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit (Cr)</option>
                            <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit (Dr)</option>
                        </select>
                        @error('balance_type')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end pt-2 border-top">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Save Contractor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
