@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-clock text-primary"></i> Manage Product Stage Lot Times
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.product_stage.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Stages
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="erp-card">
        <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
            <i class="fas fa-calendar-day mr-1 text-primary"></i> Lot Times (in Days)
        </div>
        <form action="{{ route('admin.master.product_stage.lot_time.update') }}" method="POST">
            @csrf
            <div class="erp-card-body p-0 table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;" class="text-center">#</th>
                            <th>Stage Name</th>
                            <th style="width: 250px;">Lot Time (Days)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stages as $stage)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">{{ $stage->name }}</td>
                            <td>
                                <input type="number" name="lot_times[{{ $stage->id }}]" class="form-control erp-filter-input" value="{{ old('lot_times.'.$stage->id, $stage->lot_time_in_days) }}" min="1" required style="max-width: 150px;">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.product_stage.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Save Lot Times
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
