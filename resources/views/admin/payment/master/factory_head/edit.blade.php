@extends('admin.layouts.app')
@section('title', 'Edit Factory Head Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Edit Factory Head Master</h1>
                <p class="erp-page-subtitle">Update account details and balance</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.factory_head.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.factory_head.update') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm erp-input @error('name') is-invalid @enderror" id="name" value="{{ $data->name }}" required>
                        @error('name')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Live current balance</label>
                        <div class="form-control form-control-sm erp-input d-flex align-items-center justify-content-between" style="background-color: #f8fafc; font-weight: 700;">
                            <span>â‚¹{{ number_format(abs($data->balance), 2) }}</span>
                            @if($data->balance >= 0)
                                <span class="badge badge-success px-2 py-1">Cr</span>
                            @else
                                <span class="badge badge-danger px-2 py-1">Dr</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Opening balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                        <input type="number" step="0.01" name="balance" class="form-control form-control-sm erp-input" placeholder="Enter opening balance" value="{{ old('balance', $data->currentOpeningBalance->amount ?? 0) }}">
                        @error('balance')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Opening balance type</label>
                        <select name="balance_type" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                            <option value="Credit" {{ (old('balance_type', $data->currentOpeningBalance->balance_type ?? '') == 'Credit') ? 'selected' : ($data->balance >= 0 ? 'selected' : '') }}>Credit (Cr)</option>
                            <option value="Debit" {{ (old('balance_type', $data->currentOpeningBalance->balance_type ?? '') == 'Debit') ? 'selected' : ($data->balance < 0 ? 'selected' : '') }}>Debit (Dr)</option>
                        </select>
                        @error('balance_type')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end pt-2 border-top">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-sync-alt mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection