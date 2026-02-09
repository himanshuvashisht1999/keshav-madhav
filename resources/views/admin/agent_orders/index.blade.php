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
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Agent</th>
                                    <th>Shop Name</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
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
                                        <td>{{ $order->total_qty }} pcs</td>
                                        <td><span
                                                class="text-primary font-weight-bold">₹{{ number_format($order->total_amount, 2) }}</span>
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
                </div>
            </div>
        </section>
    </div>
@endsection