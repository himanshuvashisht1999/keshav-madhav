@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sales Agent Details: {{ $data->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.master.sales-agent.index') }}">Sales Agents</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Agent Info Card -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <span class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center bg-light" style="width: 100px; height: 100px; font-size: 3rem; margin: 0 auto 15px; border: 3px solid #adb5bd; border-radius: 50%;">
                                    <i class="fas fa-user-tie text-primary"></i>
                                </span>
                            </div>
                            <h3 class="profile-username text-center">{{ $data->name }}</h3>
                            <p class="text-muted text-center">Sales Agent</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Phone</b> <a class="float-right text-dark">{{ $data->phone ?? '-' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right text-dark">{{ $data->email ?? '-' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Status</b>
                                    <span class="float-right badge {{ $data->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                        {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>See Price</b>
                                    <span class="float-right badge {{ $data->see_price == 1 ? 'badge-info' : 'badge-secondary' }}">
                                        {{ $data->see_price == 1 ? 'Allowed' : 'Not Allowed' }}
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Opening Balance</b>
                                    <span class="float-right text-bold">
                                        @if ($data->currentOpeningBalance)
                                            ₹ {{ number_format($data->currentOpeningBalance->amount, 2) }} ({{ $data->currentOpeningBalance->balance_type }})
                                        @else
                                            0.00
                                        @endif
                                    </span>
                                </li>
                            </ul>

                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('admin.master.sales-agent.edit', ['id' => $data->id]) }}" class="btn btn-primary btn-block"><b>Edit Details</b></a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('admin.master.sales-agent.index') }}" class="btn btn-secondary btn-block"><b>Back</b></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Brand Discounts Card -->
                    <div class="card card-default mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-percent mr-2"></i>Brand Discounts</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-valign-middle m-0">
                                <thead>
                                    <tr>
                                        <th>Brand</th>
                                        <th class="text-right">Discount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data->brandDiscounts as $discount)
                                        <tr>
                                            <td>{{ $discount->brand->name ?? 'N/A' }}</td>
                                            <td class="text-right text-bold text-success">{{ $discount->discount_percentage }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted p-3">No brand discounts assigned.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Associated Shops Card -->
                <div class="col-md-8">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-store mr-2"></i>Associated Shops (Customers)</h3>
                            <div class="card-tools">
                                <span class="badge badge-info">{{ count($data->shops) }} Shops</span>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0" style="max-height: 500px;">
                            <table class="table table-bordered table-striped table-head-fixed m-0">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th>Shop Name</th>
                                        <th>Phone</th>
                                        <th>Type</th>
                                        <th class="text-right">Current Balance</th>
                                        <th class="text-center" width="100">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data->shops as $key => $shop)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.master.customer.edit', ['id' => $shop->id]) }}" class="text-bold">
                                                    {{ $shop->name }}
                                                </a>
                                            </td>
                                            <td>{{ $shop->phone ?? '-' }}</td>
                                            <td><span class="badge badge-light border">{{ ucfirst($shop->type) }}</span></td>
                                            <td class="text-right text-bold" style="color: {{ $shop->balance >= 0 ? '#28a745' : '#dc3545' }}">
                                                ₹ {{ number_format(abs($shop->balance), 2) }} ({{ $shop->balance >= 0 ? 'Cr' : 'Dr' }})
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $shop->status == 1 ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $shop->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted p-4">No shops assigned to this sales agent.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
