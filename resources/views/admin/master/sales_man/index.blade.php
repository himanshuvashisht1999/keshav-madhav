@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Sales Men</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.master.sales-man.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Sales Man
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.master.sales-man.index') }}" method="GET" class="form-inline">
                    <div class="form-group mb-2">
                        <label for="search" class="sr-only">Search</label>
                        <input type="text" name="search" class="form-control" id="search" placeholder="Search name or phone..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-secondary mb-2 ml-2">Search</button>
                    <a href="{{ route('admin.master.sales-man.index') }}" class="btn btn-outline-secondary mb-2 ml-2">Clear</a>
                </form>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesMen as $man)
                            <tr>
                                <td>{{ $man->id }}</td>
                                <td>{{ $man->name }}</td>
                                <td>{{ $man->phone }}</td>
                                <td>{{ $man->email ?? '-' }}</td>
                                <td>
                                    @if($man->status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.master.sales-man.edit', $man->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.master.sales-man.destroy', $man->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sales man?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
                <div class="card-footer clearfix">
                    {{ $salesMen->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
