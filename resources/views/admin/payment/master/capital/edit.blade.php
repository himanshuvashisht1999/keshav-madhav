@extends('admin.layouts.app')
@section('title', 'Edit Capital')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Edit Capital</h1>
                <p class="erp-page-subtitle">Update capital partner account details</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.capital.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.capital.update') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Capital name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm erp-input @error('name') is-invalid @enderror" placeholder="Enter capital name" value="{{ old('name', $data->name) }}" required>
                        @error('name')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="erp-label">Opening balance (₹)</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control form-control-sm erp-input @error('opening_balance') is-invalid @enderror" placeholder="0.00" value="{{ old('opening_balance', abs($data->balance)) }}">
                        @error('opening_balance')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="erp-label">Balance type</label>
                        <select name="balance_type" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                            <option value="Credit" {{ old('balance_type', $data->balance >= 0 ? 'Credit' : 'Debit') == 'Credit' ? 'selected' : '' }}>Credit (Cr)</option>
                            <option value="Debit" {{ old('balance_type', $data->balance >= 0 ? 'Credit' : 'Debit') == 'Debit' ? 'selected' : '' }}>Debit (Dr)</option>
                        </select>
                        @error('balance_type')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end pt-2 border-top">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-sync-alt mr-1"></i> Update Capital
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
