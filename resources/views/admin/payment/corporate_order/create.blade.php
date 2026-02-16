@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Corporate Order Payment</h1>
                        <small class="text-muted">Record payments for corporate orders dispatches</small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Corporate Order Payment</li>
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
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title">Create Payment</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.payment.corporate-order.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="customer_id" class="form-label font-weight-bold">Select Corporate
                                                Customer</label>
                                            <select class="form-control select2" name="customer_id" id="customer_id"
                                                required>
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" {{ (isset($selectedCustomerId) && $selectedCustomerId == $customer->id) ? 'selected' : '' }}>
                                                        {{ $customer->name }} ({{ $customer->phone }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div id="dispatch_section" style="display: none;">
                                        <h5 class="mb-3 border-bottom pb-2">Pending Dispatches</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%">Select</th>
                                                        <th>Date</th>
                                                        <th>SKU / Dispatch ID</th>
                                                        <th>Order No</th>
                                                        <th>Grand Total</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dispatches_table_body">
                                                    <!-- Populated via AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="payment_section" class="mt-4" style="display: none;">
                                        <h5 class="mb-3 border-bottom pb-2">Payment Details</h5>

                                        <div class="card bg-light mb-4 border-left-success">
                                            <div class="card-body" id="selected_dispatch_details">
                                                <!-- Details of selected dispatch -->
                                            </div>
                                        </div>

                                        <div class="row">
                                            <input type="hidden" name="order_dispatch_id" id="order_dispatch_id">

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
            $('#customer_id').change(function () {
                var customerId = $(this).val();
                if (customerId) {
                    $.ajax({
                        url: "{{ route('admin.payment.corporate-order.get-dispatches') }}",
                        type: "GET",
                        data: { customer_id: customerId },
                        success: function (response) {
                            $('#dispatches_table_body').empty();
                            $('#payment_section').hide();

                            if (response.dispatches.length > 0) {
                                $('#dispatch_section').show();
                                var selectedDispatchId = "{{ $selectedDispatchId ?? '' }}";
                                $.each(response.dispatches, function (key, dispatch) {
                                    var isChecked = (selectedDispatchId == dispatch.id) ? 'checked' : '';
                                    var row = `
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="selected_dispatch" class="dispatch_radio" 
                                                            data-id="${dispatch.id}"
                                                            data-sku="${dispatch.sku}"
                                                            data-balance="${dispatch.balance_amount}"
                                                            data-total="${dispatch.total_amount}"
                                                            data-paid="${dispatch.paid_amount}"
                                                            data-date="${dispatch.dispatch_date}"
                                                            ${isChecked}
                                                        >
                                                    </td>
                                                    <td>${new Date(dispatch.dispatch_date).toLocaleDateString()}</td>
                                                    <td>${dispatch.sku}</td>
                                                    <td>#${dispatch.main_order_id}</td>
                                                    <td>${parseFloat(dispatch.total_amount).toFixed(2)}</td>
                                                    <td>${parseFloat(dispatch.paid_amount).toFixed(2)}</td>
                                                    <td>${parseFloat(dispatch.balance_amount).toFixed(2)}</td>
                                                </tr>
                                            `;
                                    $('#dispatches_table_body').append(row);
                                });
                            } else {
                                $('#dispatch_section').hide();
                                alert('No unpaid corporate dispatches found for this customer.');
                            }
                        }
                    });
                } else {
                    $('#dispatch_section').hide();
                    $('#payment_section').hide();
                }
            });

            $(document).on('change', '.dispatch_radio', function () {
                var id = $(this).data('id');
                var sku = $(this).data('sku');
                var balance = $(this).data('balance');
                var total = $(this).data('total');
                var paid = $(this).data('paid');
                var date = $(this).data('date');

                // Populate Payment Section
                $('#order_dispatch_id').val(id);
                $('#amount').val(parseFloat(balance).toFixed(2));
                $('#amount').attr('max', balance);

                // Show Details
                var detailsHtml = `
                            <div class="row">
                                <div class="col-md-3"><strong>Dispatch Date:</strong> ${new Date(date).toLocaleDateString()}</div>
                                <div class="col-md-3"><strong>Dispatch SKU:</strong> ${sku}</div>
                                <div class="col-md-3"><strong>Invoice Total:</strong> ₹${parseFloat(total).toFixed(2)}</div>
                                <div class="col-md-3"><strong>Balance:</strong> <span class="text-danger font-weight-bold">₹${parseFloat(balance).toFixed(2)}</span></div>
                            </div>
                        `;
                $('#selected_dispatch_details').html(detailsHtml);

                $('#payment_section').slideDown();
            });

            // Trigger change if customer is pre-selected
            if ($('#customer_id').val()) {
                $('#customer_id').trigger('change');

                // Use an interval to check for the radio button existence to avoid race conditions
                var checkExist = setInterval(function () {
                    if ($('.dispatch_radio:checked').length > 0) {
                        $('.dispatch_radio:checked').trigger('change');
                        clearInterval(checkExist);
                    }
                }, 100);

                // Safety timeout to clear interval if not found
                setTimeout(function () {
                    clearInterval(checkExist);
                }, 3000);
            }
        });
    </script>
@endsection