@extends('admin.layouts.app')
@section('title', 'Adjustment Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-sliders-h text-primary"></i> Adjustment Master Configuration
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.master.adjustment_master.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add New Master
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="table erp-table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Model Class</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masters as $master)
                    <tr>
                        <td class="font-weight-600 text-dark">{{ $master->name }}</td>
                        <td><code>{{ $master->model_name }}</code></td>
                        <td class="text-center">
                            @if($master->status == 1)
                                <span class="badge-erp-status badge-erp-success">Active</span>
                            @else
                                <span class="badge-erp-status badge-erp-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex" style="gap: 4px;">
                                <a href="{{ route('admin.payment.master.adjustment_master.edit', $master->id) }}" class="erp-action-btn erp-btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.payment.master.adjustment_master.delete', $master->id) }}" class="erp-action-btn erp-btn-delete" onclick="return confirm('Delete this master?')" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
