@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-edit text-primary"></i> Edit Role: <span class="text-dark">{{ $role->name }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.roles.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Roles
            </a>
        </div>
    </div>

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
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            <div class="erp-card-body p-3">
                <div class="form-group mb-3">
                    <label for="name" class="font-weight-bold" style="font-size: 13px;">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control erp-filter-input" id="name"
                        value="{{ $role->name }}" placeholder="Enter Role Name" required {{ $role->name === 'Admin' ? 'readonly' : '' }}>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold d-block" style="font-size: 13px;">Role Permissions</label>
                    <div class="p-3 border rounded bg-light">
                        <div class="row">
                            @foreach($permissions as $permission)
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="permissions[]"
                                            id="permission_{{ $permission->id }}" value="{{ $permission->name }}"
                                            {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                        <label for="permission_{{ $permission->id }}" class="custom-control-label" style="font-size: 12.5px; cursor: pointer;">
                                            {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.roles.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Update Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection