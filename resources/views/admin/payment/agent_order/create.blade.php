@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Agent Order Payment</h1>
                        <small class="text-muted">Record payments for agent orders</small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Agent Order Payment</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">Create Payment</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.payment.agent-order.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="agent_id" class="form-label font-weight-bold">Select Sales
                                                Agent</label>
                                            <select class="form-control select2" name="agent_id" id="agent_id" required>
                                                <option value="">Select Agent</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ (isset($selectedAgentId) && $selectedAgentId == $agent->id) ? 'selected' : '' }}>
                                                        {{ $agent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div id="order_section" style="display: none;">
                                        <h5 class="mb-3 border-bottom pb-2">Unpaid Orders</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%">Select</th>
                                                        <th>Date</th>
                                                        <th>Order ID</th>
                                                        <th>Shop</th>
                                                        <th>Grand Total</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="orders_table_body">
                                                    <!-- Populated via AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="payment_section" class="mt-4" style="display: none;">
                                        <h5 class="mb-3 border-bottom pb-2">Payment Details</h5>

                                        <div class="card bg-light mb-4 border-left-primary">
                                            <div class="card-body" id="selected_order_details">
                                                <!-- Details of selected order -->
                                            </div>
                                        </div>

                                        <div class="row">
                                            <input type="hidden" name="agent_order_id" id="agent_order_id">

                                            <div class="col-md-4 mb-3">
                                                <label for="amount" class="form-label">Payment Amount <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">₹</span>
                                                    </div>
                                                    <input type="number" step="0.01" class="form-control" name="amount"
                                                        id="amount" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="payment_date" class="form-label">Payment Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="payment_date"
                                                    id="payment_date" value="{{ date('Y-m-d') }}" required>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="payment_mode" class="form-label">Payment Mode <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control" name="payment_mode" id="payment_mode" required>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Online">Online</option>
                                                    <option value="UPI">UPI</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="reference_id" class="form-label">Reference ID / Cheque
                                                    No.</label>
                                                <input type="text" class="form-control" name="reference_id"
                                                    id="reference_id" placeholder="e.g. UPI Ref, Cheque Number">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="image" class="form-label">Payment Proof (Image)</label>
                                                <input type="file" class="form-control" name="image" id="image">
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label for="remarks" class="form-label">Remarks</label>
                                                <textarea class="form-control" name="remarks" id="remarks" rows="2"
                                                    placeholder="Optional remarks..."></textarea>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save"></i>
                                                Record Payment</button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#agent_id').change(function () {
                var agentId = $(this).val();
                if (agentId) {
                    $.ajax({
                        url: "{{ route('admin.payment.agent-order.get-orders') }}",
                        type: "GET",
                        data: { agent_id: agentId },
                        success: function (response) {
                            $('#orders_table_body').empty();
                            $('#payment_section').hide();

                            if (response.orders.length > 0) {
                                $('#order_section').show();
                                var selectedOrderId = "{{ $selectedOrderId ?? '' }}";
                                $.each(response.orders, function (key, order) {
                                    var isChecked = (selectedOrderId == order.id) ? 'checked' : '';
                                    var row = `
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="selected_order" class="order_radio" 
                                                            data-id="${order.id}"
                                                            data-balance="${order.balance_amount}"
                                                            data-total="${order.grand_total}"
                                                            data-paid="${order.paid_amount}"
                                                            data-date="${order.order_date}"
                                                            ${isChecked}
                                                        >
                                                    </td>
                                                    <td>${new Date(order.order_date).toLocaleDateString()}</td>
                                                    <td>#${order.id}</td>
                                                    <td>Shop ID: ${order.master_customer_id}</td> 
                                                    <td>${parseFloat(order.grand_total).toFixed(2)}</td>
                                                    <td>${parseFloat(order.paid_amount).toFixed(2)}</td>
                                                    <td>${parseFloat(order.balance_amount).toFixed(2)}</td>
                                                </tr>
                                            `;
                                    $('#orders_table_body').append(row);
                                });
                            } else {
                                $('#order_section').hide();
                                alert('No unpaid orders found for this agent.');
                            }
                        }
                    });
                } else {
                    $('#order_section').hide();
                    $('#payment_section').hide();
                }
            });

            $(document).on('change', '.order_radio', function () {
                var id = $(this).data('id');
                var balance = $(this).data('balance');
                var total = $(this).data('total');
                var paid = $(this).data('paid');
                var date = $(this).data('date');

                // Populate Payment Section
                $('#agent_order_id').val(id);
                $('#amount').val(balance); // Auto-fill balance
                $('#amount').attr('max', balance); // Set max validation

                // Show Details
                var detailsHtml = `
                            <div class="row">
                                <div class="col-md-3"><strong>Date:</strong> ${new Date(date).toLocaleDateString()}</div>
                                <div class="col-md-3"><strong>Order ID:</strong> #${id}</div>
                                <div class="col-md-3"><strong>Grand Total:</strong> ${parseFloat(total).toFixed(2)}</div>
                                <div class="col-md-3"><strong>Balance:</strong> <span class="text-danger">${parseFloat(balance).toFixed(2)}</span></div>
                            </div>
                        `;
                $('#selected_order_details').html(detailsHtml);

                $('#payment_section').slideDown();
            });

            // Trigger change if agent is pre-selected
            if ($('#agent_id').val()) {
                $('#agent_id').trigger('change');

                // Use an interval to check for the radio button existence to avoid race conditions
                var checkExist = setInterval(function () {
                    if ($('.order_radio:checked').length > 0) {
                        $('.order_radio:checked').trigger('change');
                        clearInterval(checkExist);
                    }
                }, 100);

                // Safety timeout to clear interval after 3 seconds
                setTimeout(function () {
                    clearInterval(checkExist);
                }, 3000);
            }
        });
    </script>
@endsection