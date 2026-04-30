@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Product Stage Lot Times</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.product_stage.index')}}">Product Stages</a></li>
                        <li class="breadcrumb-item active">Lot Times</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Lot Times (in Days)</h3>
                </div>

                <form action="{{ route('admin.master.product_stage.lot_time.update') }}" method="POST">
                    @csrf
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 60%">Stage Name</th>
                                    <th style="width: 30%">Lot Time (Days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stages as $stage)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $stage->name }}</td>
                                    <td>
                                        <input type="number" name="lot_times[{{ $stage->id }}]" class="form-control" value="{{ old('lot_times.'.$stage->id, $stage->lot_time_in_days) }}" min="1" required>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Lot Times</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
