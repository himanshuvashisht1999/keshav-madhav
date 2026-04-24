@extends('admin.layouts.app')

@section('title', 'Direct Sales Orders')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Direct Sales Order History</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.direct-sales.create') }}" class="btn btn-success btn-lg shadow-sm">
                            <i class="fas fa-plus-circle mr-2"></i> Create New Sales Order
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Filter -->
                <div class="card card-outline card-info mb-4 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('admin.direct-sales.index') }}" method="GET" class="row">
                            <div class="col-md-4">
                                <label>Filter by Party</label>
                                <select name="shop_id" class="form-control select2">
                                    <option value="">All Parties</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                            {{ $shop->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="pl-4">#ID</th>
                                        <th>Date</th>
                                        <th>Customer / Shop</th>
                                        <th>Total Qty</th>
                                        <th>Total Boxes</th>
                                        <th>Grand Total</th>
                                        <th>Paid</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        <tr>
                                            <td class="pl-4">#{{ $order->id }}</td>
                                            <td>{{ date('d M, Y', strtotime($order->order_date)) }}</td>
                                            <td>
                                                <span class="font-weight-bold">{{ $order->shop_name }}</span>
                                            </td>
                                            <td>{{ number_format($order->total_qty) }} pcs</td>
                                            <td>{{ $order->total_boxes }} boxes</td>
                                            <td class="text-primary font-weight-bold">
                                                ₹{{ number_format($order->grand_total, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($order->total_paid, 2) }}</td>
                                            <td>
                                                <span class="badge badge-success px-2 py-1">Completed & Dispatched</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <a href="{{ route('admin.agent-orders.download-invoice', $order->id) }}"
                                                    class="btn btn-sm btn-info mr-1" title="Invoice">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                                <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">No direct sales orders found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush