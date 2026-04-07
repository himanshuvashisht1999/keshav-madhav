@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shopping-cart mr-2"></i>Sales Agent Orders</h1>
                    <p class="text-muted">Review and dispatch orders placed by sales agents.</p>
                </div>
                <div>
                    <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn btn-info shadow-sm px-4 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-truck mr-2"></i> VIEW DISPATCHES
                    </a>
                    <a href="{{ route('admin.agent-orders.create') }}" class="btn btn-primary shadow-sm px-4" style="border-radius: 8px;">
                        <i class="fas fa-plus mr-2"></i> CREATE NEW ORDER
                    </a>
                </div>
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
                                <select name="agent_id" id="agent_id" class="form-control select2">
                                    <option value="">All Agents</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                    <option value="direct" {{ request('agent_id') == 'direct' ? 'selected' : '' }}>Direct (No Agent)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Shop</label>
                                <select name="shop_id" id="shop_id" class="form-control select2">
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
                            <div class="col-md-2">
                                <label class="small text-muted font-weight-bold">Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="">Any Status</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>PAID</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>UNPAID</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-filter mr-1"></i> APPLY
                                </button>
                                <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-outline-secondary px-4 mt-2">
                                    <i class="fas fa-undo mr-1"></i> RESET
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <form id="dispatchForm" action="{{ route('admin.agent-orders.dispatch-selected') }}" method="POST">
                @csrf
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">Order Records</h5>
                        <button type="submit" id="dispatchBtn" class="btn btn-success btn-sm px-4 shadow-sm" style="border-radius: 20px; display: none;">
                            <i class="fas fa-shipping-fast mr-1"></i> DISPATCH SELECTED (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="40">
                                        <div class="custom-control custom-checkbox ml-2">
                                            <input type="checkbox" class="custom-control-input" id="checkAll">
                                            <label class="custom-control-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th>Order ID</th>
                                    <th>Agent</th>
                                    <th>Shop Name</th>
                                    <th>Exp. Dispatch</th>
                                    <th>Total Pcs</th>
                                    <th class="text-center">Scanned</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr data-shop-id="{{ $order->master_customer_id }}">
                                        <td>
                                            @if($order->status != 'dispatched')
                                            <div class="custom-control custom-checkbox ml-2">
                                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="custom-control-input order-checkbox" id="check_{{ $order->id }}">
                                                <label class="custom-control-label" for="check_{{ $order->id }}"></label>
                                            </div>
                                            @endif
                                        </td>
                                        <td>#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td><span class="badge badge-info">{{ $order->agent_name }}</span></td>
                                        <td><strong>{{ $order->shop_name }}</strong></td>
                                        <td class="text-nowrap">
                                            {{ $order->expected_dispatch_date ? \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d M Y') : 'N/A' }}
                                            @if($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'))
                                                <div class="mt-1">
                                                    <span class="badge bg-danger animate__animated animate__flash animate__infinite">DELAYED</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ number_format($order->total_qty, 0) }} Pcs</td>
                                        <td class="text-center">
                                            <span class="badge {{ $order->scanned_count == $order->total_boxes ? 'badge-success' : 'badge-info' }} px-3 py-2">
                                                {{ $order->scanned_count }} / {{ $order->total_boxes }}
                                            </span>
                                        </td>
                                        <td><span
                                                class="text-primary font-weight-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $order->status == 'pending' ? 'badge-warning' : 'badge-success' }}">
                                                {{ strtoupper($order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($order->total_paid >= $order->grand_total && $order->grand_total > 0)
                                                <a href="{{ route('admin.payment.history.index', ['paymentable_type' => 'App\Models\AgentOrder', 'paymentable_id' => $order->id]) }}">
                                                    <span class="badge badge-success">PAID</span>
                                                </a>
                                            @else
                                                <span class="badge badge-danger">UNPAID</span>
                                                <br><small class="text-muted">₹{{ number_format($order->total_paid, 2) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="text-dark">{{ date('d M Y', strtotime($order->order_date)) }}</div>
                                            <small class="text-muted">{{ date('h:i A', strtotime($order->order_date)) }}</small>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                                                class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            @if($order->status == 'pending')
                                            <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                                class="btn btn-info btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                            @endif
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
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#agent_id').on('change', function() {
        var agent_id = $(this).val();
        $.ajax({
            url: "{{ route('admin.agent-orders.get-shops') }}",
            type: "GET",
            data: { agent_id: agent_id },
            success: function(data) {
                var shopSelect = $('#shop_id');
                shopSelect.empty();
                shopSelect.append('<option value="">All Shops</option>');
                $.each(data, function(index, shop) {
                    shopSelect.append('<option value="' + shop.id + '">' + shop.name + '</option>');
                });
                shopSelect.trigger('change');
            }
        });
    });

    // Multi-dispatch logic
    const checkAll = $('#checkAll');
    const checkboxes = $('.order-checkbox');
    const dispatchBtn = $('#dispatchBtn');
    const selectedCount = $('#selectedCount');

    function updateBtn() {
        let checked = $('.order-checkbox:checked');
        let count = checked.length;
        selectedCount.text(count);
        
        if (count > 0) {
            dispatchBtn.fadeIn();
        } else {
            dispatchBtn.fadeOut();
        }

        // Shop verification
        let shopId = null;
        let diffShop = false;
        checked.each(function() {
            let sId = $(this).closest('tr').data('shop-id');
            if (shopId === null) {
                shopId = sId;
            } else if (shopId !== sId) {
                diffShop = true;
            }
        });

        if (diffShop) {
            dispatchBtn.attr('disabled', true);
            // Note: setCustomValidity is for native form validation, 
            // using toastr for UI feedback as requested in logic
            toastr.warning('Please select orders for the SAME Shop only.');
        } else {
            dispatchBtn.attr('disabled', false);
        }
    }

    checkAll.on('change', function() {
        checkboxes.prop('checked', $(this).prop('checked'));
        updateBtn();
    });

    checkboxes.on('change', function() {
        updateBtn();
    });

    $('#dispatchForm').on('submit', function(e) {
        if (!confirm('Are you sure you want to dispatch selected orders?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush