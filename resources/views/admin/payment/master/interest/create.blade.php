@extends('admin.layouts.app')
@section('title', 'Create Interest Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Create Interest Master</h1>
                <p class="erp-page-subtitle">Add a new record to Interest Master</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.interest.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.interest.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm erp-input @error('name') is-invalid @enderror" id="name" placeholder="Enter name" required value="{{ old('name') }}">
                        @error('name')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Percentage (%)</label>
                        <input type="number" step="0.01" name="percentage" class="form-control form-control-sm erp-input @error('percentage') is-invalid @enderror" id="percentage" placeholder="e.g. 5.00, 18.00" value="{{ old('percentage') }}">
                        @error('percentage')
                            <span class="text-danger small font-weight-bold mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end pt-2 border-top">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection