@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Fabric Stock</a></li>
                        <li class="breadcrumb-item active">View Fabric Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Receipt Info -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Fabric Stock Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><strong>SKU:</strong> {{ $data->sku}}</div>
                        <div class="col-md-4"><strong>Date:</strong> {{ getformatDate($data->date) }}</div>
                        <div class="col-md-4"><strong>Goods Entry Number:</strong> {{ $data->goods_entry_number }}</div>
                        <div class="col-md-4"><strong>Meter:</strong> {{ $data->meter}}</div>
                        <div class="col-md-4"><strong>Roll:</strong> {{ $data->roll }}</div>
                    </div>
                </div>
            </div>

            <!-- Receipt Details -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Stock Details</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fabric SKU</th>
                                <th>Unique Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->expends as $key => $detail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $detail->sku ?? '-' }}</td>
                                    <td>{{ $detail->unique_number }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No details found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-2">
                <a href="{{ route('admin.stock.index') }}" class="btn btn-primary">Back to List</a>
            </div>

        </div>
    </section>
</div>
@endsection
