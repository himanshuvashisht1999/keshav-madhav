@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-shield-alt text-primary"></i> Roles & Permissions
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.roles.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add New Role
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

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover" id="role-table">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 180px;">Name</th>
                        <th>Permissions</th>
                        <th style="width: 100px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold" style="color: var(--erp-green-primary);">
                                <i class="fas fa-user-tag mr-1 text-muted"></i> {{ $role->name }}
                            </td>
                            <td>
                                <div class="d-flex flex-wrap" style="gap: 4px;">
                                    @foreach($role->permissions as $permission)
                                        <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 2px 7px; font-weight: 500;">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex" style="gap: 4px;">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="erp-action-btn erp-btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($role->name !== 'Admin')
                                        <button type="button" class="erp-action-btn erp-btn-delete" title="Delete" onclick="deleteRole({{ $role->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
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

@section('scripts')
<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#role-table')) {
            $('#role-table').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[0, 'asc']]
            });
        }
    });

    function deleteRole(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1d5a2a',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('admin/roles/delete') }}/" + id;
            }
        });
    }
</script>
@endsection