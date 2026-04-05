@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Role: {{ $role->name }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active">Edit Role</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Role Details</h3>
                            </div>
                            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="name">Role Name</label>
                                        <input type="text" name="name" class="form-control" id="name"
                                            value="{{ $role->name }}" placeholder="Enter Role Name" required {{ $role->name === 'Admin' ? 'readonly' : '' }}>
                                    </div>

                                    <label>Permissions</label>
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="permissions[]"
                                                        id="permission_{{ $permission->id }}" value="{{ $permission->name }}" {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                    <label for="permission_{{ $permission->id }}" class="custom-control-label">
                                                        {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-info">Update Role</button>
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection