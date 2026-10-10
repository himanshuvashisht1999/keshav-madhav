@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-user-friends text-primary"></i> Manage Sales Men
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.sales-man.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Sales Man
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
        <div class="p-2 border-bottom bg-light">
            <form action="{{ route('admin.master.sales-man.index') }}" method="GET" class="d-flex align-items-center" style="gap: 8px;">
                <input type="text" name="search" class="form-control erp-filter-input" style="max-width: 280px;" placeholder="Search name or phone..." value="{{ request('search') }}">
                <button type="submit" class="btn-erp btn-erp-primary">Search</button>
                <a href="{{ route('admin.master.sales-man.index') }}" class="btn-erp btn-erp-outline">Clear</a>
            </form>
        </div>
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th style="width: 90px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesMen as $man)
                        <tr>
                            <td>{{ $man->id }}</td>
                            <td>{{ $man->name }}</td>
                            <td>{{ $man->phone }}</td>
                            <td>{{ $man->email ?? '-' }}</td>
                            <td class="text-center">
                                @if($man->status)
                                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>
                                @else
                                    <span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex" style="gap: 4px;">
                                    <a href="{{ route('admin.master.sales-man.edit', $man->id) }}" class="erp-action-btn erp-btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.master.sales-man.destroy', $man->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sales man?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="erp-action-btn erp-btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No Sales Men found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salesMen->hasPages())
            <div class="erp-card-footer px-3 py-2 bg-light border-top">
                {{ $salesMen->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
