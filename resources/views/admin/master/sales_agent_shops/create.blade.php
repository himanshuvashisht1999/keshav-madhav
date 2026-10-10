@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Add shop for {{ $agent->name }}</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.sales-agent-shops.index', $agent->id) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to shops
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

        <form action="{{ route('admin.master.sales-agent-shops.store', $agent->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Shop name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" value="{{ old('name') }}" placeholder="Enter shop or company name" required autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Phone number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control erp-filter-input" value="{{ old('phone') }}" placeholder="E.g. 98XXXXXXXX" required autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Email address <small class="text-muted">(Optional)</small></label>
                        <input type="email" name="email" class="form-control erp-filter-input" value="{{ old('email') }}" placeholder="example@gmail.com" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Full address <small class="text-muted">(Optional)</small></label>
                        <textarea name="address" class="form-control erp-filter-input" rows="2" placeholder="Street, landmark, city...">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.sales-agent-shops.index', $agent->id) }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Register shop
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
