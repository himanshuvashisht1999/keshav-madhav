@extends('admin.layouts.app')
@section('title', 'Create Tour Expense Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Create Tour Expense Master</h1>
                <p class="erp-page-subtitle">Add a new record to Tour Expense Master</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.tour_expense.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="erp-card p-3">
            <form action="{{ route('admin.payment.master.tour_expense.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="erp-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm erp-input @error('name') is-invalid @enderror" id="name" placeholder="Enter name" required value="{{ old('name') }}">
                        @error('name')
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