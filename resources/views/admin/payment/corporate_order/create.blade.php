@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Corporate Order Payment</h1>
                        <small class="text-muted">Record payments for corporate orders and dispatches</small>
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
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle mr-2"></i> <strong>Please fix the
                                            following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
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
                                        <h5 class="mb-3 border-bottom pb-2">Pending Orders & Dispatches</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%">Select</th>
                                                        <th>Type</th>
                                                        <th>Date</th>
                                                        <th>SKU / ID</th>
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
                                            <input type="hidden" name="order_type" id="order_type">
                                            <input type="hidden" name="order_id" id="order_id">

                                            <div class="col-md-4 mb-3">
                                                <label for="amount" class="form-label">Payment Amount <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">₹</span>
                                                    </div>
                                                    <input type="number" step="0.01"
                                                        class="form-control @error('amount') is-invalid @enderror"
                                                        name="amount" id="amount" value="{{ old('amount') }}" required>
                                                </div>
                                                @error('amount')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="payment_date" class="form-label">Payment Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    class="form-control @error('payment_date') is-invalid @enderror"
                                                    name="payment_date" id="payment_date"
                                                    value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                                @error('payment_date')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="payment_mode" class="form-label">Payment Mode <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control @error('payment_mode') is-invalid @enderror"
                                                    name="payment_mode" id="payment_mode" required>
                                                    <option value="">Select Mode</option>
                                                    <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                                    <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                </select>
                                                @error('payment_mode')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3" id="bank_account_div" style="display:none;">
                                                <label for="bank_account_id" class="form-label">Bank Account <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_method_id" id="bank_account_id"
                                                    class="form-control select2 @error('payment_method_id') is-invalid @enderror"
                                                    style="width: 100%;">
                                                    <option value="">Select Bank Account</option>
                                                    @foreach($bank_accounts as $bank)
                                                        <option value="{{ $bank->id }}" {{ old('payment_method_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }}
                                                            ({{ $bank->account_number }}) - Bal:
                                                            ₹{{ number_format($bank->balance, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_method_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3" id="cash_account_div" style="display:none;">
                                                <label for="cash_account_id" class="form-label">Cash Account <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_method_id" id="cash_account_id"
                                                    class="form-control select2 @error('payment_method_id') is-invalid @enderror"
                                                    style="width: 100%;">
                                                    <option value="">Select Cash Account</option>
                                                    @foreach($cash_accounts as $cash)
                                                        <option value="{{ $cash->id }}" {{ old('payment_method_id') == $cash->id ? 'selected' : '' }}>{{ $cash->name }} - Bal:
                                                            ₹{{ number_format($cash->balance, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_method_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3" id="reference_id_div">
                                                <label for="reference_id" class="form-label">Reference ID / Cheque
                                                    No.</label>
                                                <input type="text" class="form-control" name="reference_id"
                                                    id="reference_id" placeholder="e.g. UPI Ref, Cheque Number">
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="image" class="form-label">Payment Proof (Image)</label>
                                                <input type="file" class="form-control" name="image" id="image">
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <div class="custom-control custom-checkbox custom-control-lg">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="complete_payment" id="complete_payment" value="1">
                                                    <label class="custom-control-label font-weight-bold text-primary"
                                                        for="complete_payment">
                                                        Mark as Fully Paid (Complete Payment)
                                                    </label>
                                                    <small class="form-text text-muted">Check this if this is the final
                                                        payment and you want to hide this order/dispatch from the pending
                                                        list.</small>
                                                </div>
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

                            if (response.dispatches.length > 0 || response.orders.length > 0) {
                                $('#dispatch_section').show();
                                var selectedDispatchId = "{{ $selectedDispatchId ?? '' }}";

                                // Show Orders first
                                $.each(response.orders, function (key, order) {
                                    var row = `
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="selected_item" class="item_radio" 
                                                            data-id="${order.id}"
                                                            data-type="order"
                                                            data-sku="${order.sku}"
                                                            data-balance="0"
                                                            data-total="0"
                                                            data-paid="${order.paid_amount}"
                                                            data-date="${order.created_at}"
                                                        >
                                                    </td>
                                                    <td><span class="badge badge-info shadow-sm">Order</span></td>
                                                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                                    <td>${order.sku}</td>
                                                    <td>#${order.id}</td>
                                                    <td>-</td>
                                                    <td>${parseFloat(order.paid_amount).toFixed(2)}</td>
                                                    <td>-</td>
                                                </tr>
                                            `;
                                    $('#dispatches_table_body').append(row);
                                });

                                // Then Dispatches
                                $.each(response.dispatches, function (key, dispatch) {
                                    var isChecked = (selectedDispatchId == dispatch.id) ? 'checked' : '';
                                    var row = `
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="selected_item" class="item_radio" 
                                                            data-id="${dispatch.id}"
                                                            data-type="dispatch"
                                                            data-sku="${dispatch.sku}"
                                                            data-balance="${dispatch.balance_amount}"
                                                            data-total="${dispatch.total_amount}"
                                                            data-paid="${dispatch.paid_amount}"
                                                            data-date="${dispatch.dispatch_date}"
                                                            ${isChecked}
                                                        >
                                                    </td>
                                                    <td><span class="badge badge-success shadow-sm">Dispatch</span></td>
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
                                alert('No unpaid corporate orders or dispatches found for this customer.');
                            }
                        }
                    });
                } else {
                    $('#dispatch_section').hide();
                    $('#payment_section').hide();
                }
            });

            $(document).on('change', '.item_radio', function () {
                var id = $(this).data('id');
                var type = $(this).data('type');
                var sku = $(this).data('sku');
                var balance = $(this).data('balance');
                var total = $(this).data('total');
                var paid = $(this).data('paid');
                var date = $(this).data('date');

                // Populate Payment Section
                $('#order_id').val(id);
                $('#order_type').val(type);

                if (type === 'dispatch') {
                    $('#amount').val(parseFloat(balance).toFixed(2));
                    $('#amount').attr('max', balance);
                } else {
                    $('#amount').val('');
                    $('#amount').removeAttr('max');
                }

                // Show Details
                var typeLabel = type === 'order' ? 'Order' : 'Dispatch';
                var detailsHtml = `
                        <div class="row">
                            <div class="col-md-3"><strong>${typeLabel} Date:</strong> ${new Date(date).toLocaleDateString()}</div>
                            <div class="col-md-4"><strong>${typeLabel} SKU:</strong> ${sku}</div>
                            <div class="col-md-3"><strong>${type === 'dispatch' ? 'Invoice Total' : 'Total Paid'}:</strong> ₹${parseFloat(type === 'dispatch' ? total : paid).toFixed(2)}</div>
                            <div class="col-md-2"><strong>Balance:</strong> <span class="text-danger font-weight-bold">${type === 'dispatch' ? '₹' + parseFloat(balance).toFixed(2) : 'N/A'}</span></div>
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
                    if ($('.item_radio:checked').length > 0) {
                        $('.item_radio:checked').trigger('change');
                        clearInterval(checkExist);
                    }
                }, 100);

                // Safety timeout to clear interval if not found
                setTimeout(function () {
                    clearInterval(checkExist);
                }, 3000);
            }
            $('#payment_mode').on('change', function () {
                var mode = $(this).val();
                if (mode == 'Bank') {
                    $('#bank_account_div').show();
                    $('#bank_account_id').attr('required', true).prop('disabled', false);
                    $('#cash_account_div').hide();
                    $('#cash_account_id').attr('required', false).prop('disabled', true);
                    $('#cash_account_id').val('').trigger('change');
                } else if (mode == 'Cash') {
                    $('#cash_account_div').show();
                    $('#cash_account_id').attr('required', true).prop('disabled', false);
                    $('#bank_account_div').hide();
                    $('#bank_account_id').attr('required', false).prop('disabled', true);
                    $('#bank_account_id').val('').trigger('change');
                } else {
                    $('#bank_account_div').hide();
                    $('#cash_account_div').hide();
                    $('#bank_account_id').attr('required', false).prop('disabled', true);
                    $('#cash_account_id').attr('required', false).prop('disabled', true);
                }
            });

            // Trigger on load for old values
            $('#payment_mode').trigger('change');
        });
    </script>
@endsection