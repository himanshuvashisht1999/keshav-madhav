@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Adjustment Master Configuration</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Adjustment Master</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Masters for Adjustment</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payment.master.adjustment_master.create') }}" class="btn btn-primary btn-sm">Add New Master</a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Model Class</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($masters as $master)
                            <tr>
                                <td>{{ $master->name }}</td>
                                <td><code>{{ $master->model_name }}</code></td>
                                <td>
                                    @if($master->status == 1)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.payment.master.adjustment_master.edit', $master->id) }}" class="btn btn-info btn-sm">Edit</a>
                                    <a href="{{ route('admin.payment.master.adjustment_master.delete', $master->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this master?')">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
