@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shopping-cart mr-2"></i>Sales Agent Orders</h1>
                <p class="text-muted">Review and dispatch orders placed by sales agents.</p>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent-orders.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Agent</label>
                                <select name="agent_id" class="form-control select2">
                                    <option value="">All Agents</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Shop</label>
                                <select name="shop_id" class="form-control select2">
                                    <option value="">All Shops</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                            {{ $shop->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Any Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>DISPATCHED</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-filter mr-1"></i> APPLY
                                </button>
                                <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-undo mr-1"></i> RESET
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Agent</th>
                                    <th>Shop Name</th>
                                    <th>Exp. Dispatch</th>
                                    <th>Total Items</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td><span class="badge badge-info">{{ $order->agent_name }}</span></td>
                                        <td><strong>{{ $order->shop_name }}</strong></td>
                                        <td>
                                            {{ $order->expected_dispatch_date ? \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d-m-Y') : 'N/A' }}
                                            @if($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'))
                                                <div class="mt-1">
                                                    <span class="badge bg-danger animate__animated animate__flash animate__infinite">DELAYED</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ number_format($order->total_qty, 0) }}</td>
                                        <td><span
                                                class="text-primary font-weight-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $order->status == 'pending' ? 'badge-warning' : 'badge-success' }}">
                                                {{ strtoupper($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ date('d-m-Y H:i', strtotime($order->order_date)) }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                                                class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="fas fa-eye mr-1"></i> View & Dispatch
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">No agent orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                        <div class="card-footer bg-white">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection