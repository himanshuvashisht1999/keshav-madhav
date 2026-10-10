@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Sales agent details: {{ $data->name }}</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.sales-agent.edit', ['id' => $data->id]) }}" class="btn-erp btn-erp-primary mr-1">
                <i class="fas fa-edit mr-1"></i> Edit agent
            </a>
            <a href="{{ route('admin.master.sales-agent.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Agent Info Card -->
        <div class="col-md-4">
            <div class="erp-card p-3 mb-3">
                <div class="text-center mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-radius: 50%; background: var(--erp-green-light); color: var(--erp-green-primary); font-size: 2rem; border: 2px solid var(--erp-green-border);">
                        <i class="fas fa-user-tie"></i>
                    </span>
                    <h5 class="mt-2 mb-0 font-weight-bold" style="color: var(--erp-green-primary);">{{ $data->name }}</h5>
                    <small class="text-muted">Sales agent</small>
                </div>

                <ul class="list-group list-group-flush mb-3" style="font-size: var(--erp-font-sm);">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Phone</span>
                        <span class="font-weight-bold text-dark">{{ $data->phone ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Email</span>
                        <span class="font-weight-bold text-dark">{{ $data->email ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Status</span>
                        <span class="badge {{ $data->status == 1 ? 'badge-success' : 'badge-danger' }} px-2 py-1">
                            {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">See price</span>
                        <span class="badge {{ $data->see_price == 1 ? 'badge-info' : 'badge-secondary' }} px-2 py-1">
                            {{ $data->see_price == 1 ? 'Allowed' : 'Not Allowed' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Opening balance</span>
                        <span class="font-weight-bold text-dark">
                            @if ($data->currentOpeningBalance)
                                ₹ {{ number_format($data->currentOpeningBalance->amount, 2) }} ({{ $data->currentOpeningBalance->balance_type }})
                            @else
                                0.00
                            @endif
                        </span>
                    </li>
                    @if($data->address)
                    <li class="list-group-item px-0 py-2">
                        <span class="text-muted d-block mb-1">Address</span>
                        <span class="text-dark">{{ $data->address }}</span>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Brand Discounts Card -->
            <div class="erp-card p-3 mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-percent mr-2" style="color: var(--erp-green-primary);"></i>
                    <h6 class="font-weight-bold mb-0">Brand discounts</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Brand</th>
                                <th class="text-right">Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data->brandDiscounts as $discount)
                                <tr>
                                    <td>{{ $discount->brand->name ?? 'N/A' }}</td>
                                    <td class="text-right font-weight-bold text-success">{{ $discount->discount_percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted p-2">No brand discounts assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Associated Shops Card -->
        <div class="col-md-8">
            <div class="erp-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-store mr-2" style="color: var(--erp-green-primary);"></i>
                        <h6 class="font-weight-bold mb-0">Associated shops (Customers)</h6>
                    </div>
                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">
                        {{ count($data->shops) }} Shops
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm m-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Shop name</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th class="text-right">Current balance</th>
                                <th class="text-center" style="width: 90px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data->shops as $key => $shop)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.master.customer.edit', ['id' => $shop->id]) }}" class="font-weight-bold" style="color: var(--erp-green-primary);">
                                            {{ $shop->name }}
                                        </a>
                                    </td>
                                    <td>{{ $shop->phone ?? '-' }}</td>
                                    <td><span class="badge badge-light border">{{ ucfirst($shop->type) }}</span></td>
                                    <td class="text-right font-weight-bold" style="color: {{ $shop->balance >= 0 ? '#28a745' : '#dc3545' }};">
                                        ₹ {{ number_format(abs($shop->balance), 2) }} ({{ $shop->balance >= 0 ? 'Cr' : 'Dr' }})
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $shop->status == 1 ? 'badge-success' : 'badge-secondary' }} px-2 py-1">
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
@endsection
